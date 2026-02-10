<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'tc_kimlik' => ['nullable', 'string', 'size:11', 'regex:/^\d{11}$/', 'unique:users,tc_kimlik'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,owner,tenant,vendor'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'Bu telefon numarası zaten kayıtlı.',
            'tc_kimlik.unique' => 'Bu TC Kimlik numarası zaten kayıtlı.',
            'tc_kimlik.size' => 'TC Kimlik numarası 11 haneli olmalıdır.',
            'tc_kimlik.regex' => 'TC Kimlik numarası sadece rakamlardan oluşmalıdır.',
        ];
    }
}
