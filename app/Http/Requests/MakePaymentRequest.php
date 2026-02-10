<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MakePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $siteId = auth()->user()->site_id;

        return [
            'paid_at' => ['required', 'date'],
            'method' => ['required', 'in:cash,bank'],
            'cash_account_id' => [
                'required',
                Rule::exists('cash_accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
