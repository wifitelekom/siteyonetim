<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $siteId = auth()->user()->site_id;
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('accounts')->where('site_id', $siteId)->ignore($this->route('account'))],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense,asset,liability'],
        ];
    }
}
