<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules =[
            'coin_type'=>['required','max:80',Rule::unique('coins')->ignore(decryptId($this->coin_id),'id')],
            'network'=>'required',
            'name'=>'required|max:30',
            'coin_price' => 'required|numeric|gt:0',
            'max_send_limit' => 'numeric|gte:0',
        ];

        if(!empty($this->minimum_withdrawal)){
            $rules['minimum_withdrawal']='numeric|min:0.00000010';
        }
        if(!empty($this->maximum_withdrawal)){
            $rules['maximum_withdrawal']='numeric|min:0.00000010';
        }
        if(!empty($this->withdrawal_fees)){
            $rules['withdrawal_fees']='numeric';
        }
        if(!empty($this->minimum_buy_amount)){
            $rules['minimum_buy_amount']='numeric|min:0.00000010';
        }
        if(!empty($this->minimum_sell_amount)){
            $rules['minimum_sell_amount']='numeric|min:0.00000010';
        }
        if(!empty($this->coin_icon)){
            $rules['coin_icon'] = 'image|mimes:jpg,jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }

    public function messages()
    {
        $messages=[
            'ctype.required'=>'coin.short.name.cant.be.empty',
            'ctype.unique'=>'coin.short.name.already.exists',
            'ctype.max'=>'coin.short.name.cant.be.more.than.10.character',
            'name.required'=>'coin.full.name.cant.be.empty',
            'name.max'=>'coin.full.name.cant.be.more.than.30.character',
            'coin_icon.required'=>'coin.icon.can.not.be.empty',
            'coin_icon.image'=>'coin.icon.must.be.image',
            'coin_icon.max'=>'coin.icon.should.not.be.more.than.2MB',
            'network.required'=>'Coin API is required'
        ];
        if(!empty($this->minimum_buy_amount)){
            $messages['minimum_buy_amount.min']= 'minimum.buy.amount.cant.be.less.than.0.00000010';
        }
        if(!empty($this->minimum_sell_amount)){
            $messages['minimum_sell_amount.min']= 'minimum.sell.amount.cant.be.less.than.0.00000010';
        }
        return $messages;
    }
}
