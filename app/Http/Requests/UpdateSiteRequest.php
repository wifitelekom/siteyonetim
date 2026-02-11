<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
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
            'admin_user_id' => ['nullable', 'integer'],
            'admin_name' => ['nullable', 'string', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'admin_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}
