<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddApartmentToUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siteId = $this->user()->site_id;

        return [
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'relation_type' => ['required', 'in:owner,tenant'],
            'start_date' => ['nullable', 'date'],
        ];
    }
}
