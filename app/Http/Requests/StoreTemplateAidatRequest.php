<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTemplateAidatRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $siteId = auth()->user()->site_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)),
            ],
            'scope' => ['required', 'in:all,selected'],
            'apartment_ids' => ['required_if:scope,selected', 'array'],
            'apartment_ids.*' => [
                Rule::exists('apartments', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'is_active' => ['boolean'],
        ];
    }
}
