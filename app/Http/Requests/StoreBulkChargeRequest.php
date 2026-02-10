<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkChargeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $siteId = auth()->user()->site_id;

        return [
            'apartment_ids' => ['required', 'array', 'min:1'],
            'apartment_ids.*' => [
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
