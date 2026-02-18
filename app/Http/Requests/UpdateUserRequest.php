<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($userId)],
            'tc_kimlik' => ['nullable', 'string', 'size:11', 'regex:/^\d{11}$/', Rule::unique('users')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,owner,tenant,vendor'],
            'address' => ['nullable', 'string', 'max:500'],
            'birth_date' => ['nullable', 'date'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'education' => ['nullable', 'string', 'in:ilkokul,ortaokul,lise,onlisans,lisans,yuksek_lisans,doktora'],
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
