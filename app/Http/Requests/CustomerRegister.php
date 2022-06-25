<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRegister extends FormRequest
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
        return [
            
            'first_name' => 'required|string',
			'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password'
        ];
    }

    public function messages()
    {
        return [

            'required' => ' :attribute can not be leave empty',
            'string' => ' :attribute should be string',
            'email' => ' :attribute should be email',
            'unique' => ' :attribute already exists',
            'same' => 'Password does not match'
        ];
    }

    public function attributes()
    {
        return [

            'first_name' => 'Full Name',
			'last_name' => 'Last Name',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Repeat Password'

        ];
    }
}