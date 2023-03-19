<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendCoinBalanceRequest;
use App\Http\Services\WalletService;
use App\Model\AdminSendCoinHistory;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\Model\WalletNetwork;
use App\Model\WalletSwapHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    private $service;
    function __construct()
    {
        $this->service =  new WalletService();
    }
    // my wallet list
    public function myWalletList(Request $request)
    {
        $data['title'] = __('My Wallet List');
        create_coin_wallet(Auth::id());
        if($request->ajax()){
            $data['wallets'] = Wallet::join('coins','coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.user_id'=> Auth::id(), 'wallets.type'=> PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
                ->orderBy('wallets.id', 'ASC')
                ->select('wallets.*', 'coins.coin_icon');

            return datatables()->of($data['wallets'])
                ->addColumn('created_at', function ($item) {return $item->created_at;})
                ->make(true);
        }

        return view('admin.profile.wallet_list',$data);
    }

    // all personal wallet list
    public function adminWalletList(Request $request)
    {
        $data['title'] = __('Wallet List');
        $data['sub_menu'] = 'wallet_list';
        if($request->ajax()){
            $data['wallets'] = Wallet::join('users','users.id','=','wallets.user_id')
                ->join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.type'=>PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
                ->orderBy('wallets.id', 'desc')
                ->select(
                    'wallets.name'
                    ,'wallets.coin_type'
                    ,'wallets.balance'
                    ,'wallets.created_at'
                    ,'users.first_name'
                    ,'users.last_name'
                    ,'users.email'
                );

            return datatables()->of($data['wallets'])
                ->addColumn('coin_type', function ($item) {return $item->coin_type;})
                ->addColumn('user_name',function ($item){return $item->first_name.' '.$item->last_name;})
                ->addColumn('created_at', function ($item) {return $item->created_at;})
                ->make(true);
        }

        return view('admin.wallet.index',$data);
    }

    // send coin wallet list
    public function adminWalletSendList(Request $request)
    {
        $data['title'] = __('Send Coin List');
        $data['sub_menu'] = __('send_coin_list');
        if($request->ajax()){
            $data['wallets'] = AdminSendCoinHistory::join('wallets','wallets.id','=','admin_send_coin_histories.wallet_id')
                ->join('users', 'users.id', '=', 'admin_send_coin_histories.user_id')
                ->orderBy('admin_send_coin_histories.id', 'desc')
                ->select(
                    'admin_send_coin_histories.id',
                    'wallets.name'
                    ,'wallets.coin_type'
                    ,'wallets.balance'
                    ,'admin_send_coin_histories.created_at'
                    ,'users.first_name'
                    ,'users.last_name'
                    ,'users.email',
                    'admin_send_coin_histories.amount'
                );

            return datatables()->of($data['wallets'])
                ->addColumn('user_name',function ($item){return $item->first_name.' '.$item->last_name;})
                ->addColumn('created_at', function ($item) {return $item->created_at;})
                ->addColumn('actions', function ($item) {
                    $action = '<ul>';
                    $action .= delete_html('adminSendBalanceDelete',encrypt($item->id));
                    $action .= '<ul>';

                    return $action;
                })
//                ->addColumn('updated_by', function ($item) {return $item->author->first_name;})
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.wallet.send_coin_history',$data);
    }

    // Swap coin wallet list
    public function adminSwapCoinHistory(Request $request)
    {
        $data['title'] = __('Swap Coin History');
        $data['sub_menu'] = __('swap_coin_history');
        if($request->ajax()){
            $data['history'] = WalletSwapHistory::where("status",STATUS_ACTIVE)->with("user")->get();
            $response = datatables()->of($data['history'])
                ->addColumn('email',function ($item){return $item->user->eamil;})
                ->addColumn('request', function ($item) {return $item->requested_amount." ".$item->from_coin_type;})
                ->addColumn('convert', function ($item) {return $item->converted_amount." ".$item->to_coin_type;})
                ->addColumn('rate', function ($item) {return $item->rate;})
                ->make(true);
            return $response;
        }
        return view('admin.wallet.swap_coin_history',$data);
    }

    // send user wallet balance
    public function adminSendWallet()
    {
        $data['title'] = __('Send Wallet Balance');
        $data['sub_menu'] = __('send_wallet');
        $data['wallets'] = Wallet::join('users','users.id', '=', 'wallets.user_id')
//            ->where(['users.role' => USER_ROLE_USER])
            ->select('users.first_name','users.last_name','users.email','wallets.*')
            ->get();

        return view('admin.wallet.send_wallet_coin', $data);
    }

    // admin send coin process
    public function adminSendBalanceProcess(SendCoinBalanceRequest $request)
    {
        $response = $this->service->sendCoinBalanceToUser($request);
        if($response['success'] == true) {
            return redirect()->back()->with('success', $response['message']);
        } else {
            return redirect()->back()->with('dismiss', $response['message']);
        }
    }

    public function adminSendBalanceDelete($id)
    {
        $response = $this->service->adminSendBalanceDelete(decrypt($id));
        if($response['success']== true)
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }

    }

     // all wallet address list
    public function walletAddressList(Request $request)
    {
        $data['title'] = __('Wallet Address List');
        $data['sub_menu'] = 'wallet_address_list';
        $data['address_list'] = WalletAddressHistory::orderBy('id','desc')->get();
        $data['address_network_list'] = WalletNetwork::orderBy('id','desc')->where('address','<>','')->get();

        return view('admin.wallet.address_list',$data);
    }

}
