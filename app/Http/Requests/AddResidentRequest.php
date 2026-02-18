<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siteId = $this->user()->site_id;

        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('site_id', $siteId)),
            ],
            'relation_type' => ['required', 'in:owner,tenant'],
            'family_role' => ['nullable', 'string', 'in:spouse,child,parent,sibling,other'],
            'start_date' => ['nullable', 'date'],
        ];
    }
}
