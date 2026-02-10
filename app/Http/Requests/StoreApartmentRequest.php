<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreApartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'block' => ['nullable', 'string', 'max:10'],
            'floor' => ['required', 'integer', 'min:-5', 'max:50'],
            'number' => ['required', 'string', 'max:10'],
            'm2' => ['nullable', 'numeric', 'min:1'],
            'arsa_payi' => ['nullable', 'numeric', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }
}
