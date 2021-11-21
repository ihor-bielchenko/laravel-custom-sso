<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class Register extends FormRequest
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
			'email' => 'required|unique:users|string|max:255',
			'name' => 'required|string|max:255',
			'password' => 'required|string|max:255',
			'confirm_password' => 'required|string|max:255',
		];
	}
}
