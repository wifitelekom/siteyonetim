<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siteId = auth()->user()->site_id;
        $expense = $this->route('expense');

        $amountRules = ['required', 'numeric', 'min:0.01'];

        if ((float) $expense->paid_amount > 0) {
            $amountRules[] = 'in:' . $expense->amount;
        }

        return [
            'vendor_id' => [
                'nullable',
                Rule::exists('vendors', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)),
            ],
            'expense_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'amount' => $amountRules,
            'description' => ['nullable', 'string', 'max:500'],
            'invoice_no' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.in' => 'Odemesi yapilmis giderin tutari degistirilemez.',
        ];
    }
}
