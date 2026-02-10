<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreCashAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:cash,bank'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ];
    }
}
