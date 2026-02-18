<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserApartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relation_type' => ['required', 'in:owner,tenant'],
            'start_date' => ['nullable', 'date'],
        ];
    }
}
