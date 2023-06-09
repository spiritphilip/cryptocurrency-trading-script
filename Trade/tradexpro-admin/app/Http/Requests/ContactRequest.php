<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
        $rules = [
            'name'=>'required',
            'email'=>'required|email',
            'phone'=>'required',
            'address'=>'required',
            'description' => 'required'
        ];
        if (isset(allsetting()['select_captcha_type']) && (allsetting()['select_captcha_type'] == CAPTCHA_TYPE_RECAPTCHA)) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        return $rules;
    }
}
