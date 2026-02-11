<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_no' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'admin_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->whereNull('site_id'),
            ],
            'admin_name' => ['required_without:admin_user_id', 'string', 'max:255'],
            'admin_email' => ['required_without:admin_user_id', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required_without:admin_user_id', 'string', 'min:8', 'confirmed'],
        ];
    }
}
