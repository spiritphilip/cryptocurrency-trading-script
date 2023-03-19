<?php
namespace App\Http\Services;


use App\Http\Repositories\FiatWithdrawalRepository;
use App\Http\Requests\Api\User\FiatWithdrawalRequest;
use App\Jobs\MailSend;
use App\Model\FiatWithdrawal;
use App\Model\FiatWithdrawalCurrency;
use App\Model\UserBank;
use App\Model\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FiatWithdrawalService extends BaseService
{
    public $model = FiatWithdrawal::class;
    public $repository = FiatWithdrawalRepository::class;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function getFiatWithdrawalData($userId)
    {
        $response = responseData(false);
        try {
            $data['my_wallet'] = Wallet::join('coins', 'coins.id' ,'=', 'wallets.coin_id')
                ->where(['coins.status' => STATUS_ACTIVE, 'wallets.user_id' => $userId])
                ->select('wallets.coin_type', 'wallets.id','wallets.balance')
                ->get();
            if(isset($data['my_wallet'][0])) {
                foreach ($data['my_wallet'] as $wallet) {
                    $wallet->encryptId = encrypt($wallet->id);
                    unset($wallet->id);
                }
            }
            $data['currency'] = FiatWithdrawalCurrency::join('currency_lists','currency_lists.id', '=','fiat_withdrawal_currencies.currency_id')
                ->where(['fiat_withdrawal_currencies.status' => STATUS_ACTIVE])
                ->select('currency_lists.code','currency_lists.symbol','currency_lists.name')
                ->get();
            $data['my_bank'] = UserBank::where(['user_id' => $userId, 'status' => STATUS_ACTIVE])->get();
            $response = responseData(true,__('Success'),$data);
        } catch (\Exception $e) {
            storeException('getFiatWithdrawalData',$e->getMessage());
        }
        return $response;
    }


    public function makeWithdrawalData($data,$userId)
    {
        $input = [
            'user_id' => $userId,
            'bank_id' => $data['bank_id'],
            'wallet_id' => $data['wallet']->id,
            'coin_amount' => $data['amount'],
            'currency_amount' => $data['convert_amount'],
            'rate' => $data['rate'],
            'currency' => $data['currency'],
            'fees' => $data['fees'],
        ];

        return $input;
    }

    public function getFiatWithdrawalRateData($request,$userId)
    {
        $response = responseData(false);
        try {
            $wallet = Wallet::where(['id' => decrypt($request->wallet_id), 'user_id' => $userId])->first();
            if ($wallet) {
                $rate = convert_currency(1,'USDT',$wallet->coin_type,$request->currency);
                $data['amount'] = $request->amount;
                $data['rate'] = $rate;
                $data['convert_amount'] = bcmul($request->amount,$rate,8);
                $data['fees'] = getCalculatedFees($data['convert_amount']);
                $data['net_amount'] = bcsub($data['convert_amount'],$data['fees'],8);
                $data['currency'] = $request->currency;
                if ($request->type == 'fiat') {
                    $data['wallet'] = $wallet;
                }
                if($request->type == 'fiat') {
                    if ($request->amount > $wallet->balance) {
                        return responseData(false,__('Wallet has no enough balance to withdrawal'));
                    }
                    $bank = UserBank::where(['user_id' => $userId,'id' => $request->bank_id])->first();
                    if (isset($bank)) {
                        $data['bank_id'] = $bank->id;
                    } else {
                        return responseData(false,__('Bank not found'));
                    }
                }
                $response = responseData(true,__('Success'),$data);
            } else {
                $response = responseData(false,__('Wallet not found'));
            }
        } catch (\Exception $e) {
            storeException("getFiatWithdrawalRateData",$e->getMessage());
        }
        return $response;
    }

    public function fiatWithdrawalProcess($request,$userId)
    {
        $response = responseData(false);
        DB::beginTransaction();
        try {
            $rate = $this->getFiatWithdrawalRateData($request,$userId);
            if ($rate['success'] == true) {
                $rateData = $rate['data'];
                if ($request->amount > $rateData['wallet']->balance) {
                    return responseData(false,__('Wallet has no enough balance to withdrawal'));
                }
                $rateData['wallet']->decrement('balance',$rateData['amount']);
                $item = $this->object->create($this->makeWithdrawalData($rateData,$userId));
                $response = responseData(true,__('Withdrawal request submitted successfully. please wait for admin approval'),$item);
            } else {
                $response = responseData(false,$rate['message']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('fiatWithdrawalProcess', $e->getMessage());
        }
        DB::commit();
        return $response;
    }

    public function getWithdrawalHistory($id,$paginate = null){
        try{
            $lists = FiatWithdrawal::where('user_id', $id)->orderBy('id', 'DESC')->paginate($paginate ?? 200);

            return responseData(true,__('Fiat withdraw history get successfully'),$lists) ;

        }catch (\Exception $e){
            storeException('getWithdrawalHistory',$e->getMessage());
            return responseData(false,__('Something went wrong')) ;
        }
        return responseData(false,__('Fiat withdraw history get failed'),$lists) ;
    }

    public function fiatWithdrawalAdminAccept($request)
    {
        try{
            $id = decrypt($request->id);
            $fiatWithdraw = FiatWithdrawal::find($id);
            if(file_exists($request->file)){
                $file = uploadimage($request->file,IMG_SLEEP_VIEW_PATH);
                $fiatWithdraw->bank_slip = $file;
                $fiatWithdraw->status = STATUS_ACCEPTED;
                $fiatWithdraw->save();
                $this->sendAcceptMailToUser($fiatWithdraw);
            }else{
                return responseData(false,__('Slip not found'));
            }
        }catch (\Exception $e){
            storeException('fiatWithdrawalAdminAccept',$e->getMessage());
            return responseData(false,__('Something went wrong'));
        }
        return responseData(true,__('Fiat withdraw accepted'));
    }

    public function fiatWithdrawalAdminReject($request)
    {
        DB::beginTransaction();
        try{
            if (!empty($request->reject_note)) {
                $id = decrypt($request->id);
                $fiatWithdraw = FiatWithdrawal::find($id);
                if($fiatWithdraw) {
                    $wallet = Wallet::find($fiatWithdraw->wallet_id);
                    if($wallet){
                        $wallet->increment('balance',$fiatWithdraw->coin_amount);
                        $fiatWithdraw->status = STATUS_REJECTED;
                        $fiatWithdraw->save();
                        $this->sendRejectMailToUser($fiatWithdraw,$request->reject_note);
                    }else{
                        return responseData(false,__('Wallet not found'));
                    }
                }else{
                    return responseData(false,__('Withdrawal record not found'));
                }
            } else {
                return responseData(false,__('Reject note is required'));
            }
        }catch (\Exception $e){
            DB::rollBack();
            storeException('fiatWithdrawalAdminReject', $e->getMessage());
            return responseData(false,__('Something went wrong'));
        }
        DB::commit();
        return responseData(true,__('Fiat withdraw Rejected'));
    }

    // send withdrawal accept mail to user
    public function sendAcceptMailToUser($item)
    {
        try {
            $data['item'] = $item;
            $data['slip'] = !empty($item->bank_slip) ? asset(IMG_SLEEP_VIEW_PATH.$item->bank_slip) : '';
            $data['subject'] = __('Fiat currency withdrawal request accepted');
            $data['mailTemplate'] = 'email.fiat_withdrawal_accept';
            $data['to'] = $item->user->email;
            $data['name'] = $item->user->first_name.' '.$item->user->last_name;
            dispatch(new MailSend($data));

        } catch (\Exception $e) {
            storeException('sendAcceptMailToUser',$e->getMessage());
        }
    }

    // send withdrawal accept mail to user
    public function sendRejectMailToUser($item,$note)
    {
        try {
            $data['item'] = $item;
            $data['reason'] = $note;
            $data['subject'] = __('Fiat currency withdrawal request rejected');
            $data['mailTemplate'] = 'email.fiat_withdrawal_reject';
            $data['to'] = $item->user->email;
            $data['name'] = $item->user->first_name.' '.$item->user->last_name;
            dispatch(new MailSend($data));

        } catch (\Exception $e) {
            storeException('sendAcceptMailToUser',$e->getMessage());
        }
    }

}
