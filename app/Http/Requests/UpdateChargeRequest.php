<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siteId = auth()->user()->site_id;
        $charge = $this->route('charge');

        $amountRules = ['required', 'numeric', 'min:0.01'];

        if ((float) $charge->paid_amount > 0) {
            $amountRules[] = 'in:' . $charge->amount;
        }

        return [
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)),
            ],
            'charge_type' => ['required', 'in:aidat,other'],
            'period' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'due_date' => ['required', 'date'],
            'amount' => $amountRules,
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.in' => 'Tahsilat alinmis tahakkugun tutari degistirilemez.',
        ];
    }
}

