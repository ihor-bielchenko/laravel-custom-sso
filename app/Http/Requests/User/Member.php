<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class Member extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return false;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'project_id' => 'required|numeric|min:1',
			'user_id' => 'required|numeric|min:0',
			'name' => 'required|string|max:255',
		];
	}
}
