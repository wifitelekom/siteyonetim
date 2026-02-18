<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $siteId = $this->user()->site_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'tc_kimlik' => ['nullable', 'string', 'size:11', 'regex:/^\d{11}$/', 'unique:users,tc_kimlik'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,owner,tenant,vendor'],
            'address' => ['nullable', 'string', 'max:500'],
            'birth_date' => ['nullable', 'date'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'education' => ['nullable', 'string', 'in:ilkokul,ortaokul,lise,onlisans,lisans,yuksek_lisans,doktora'],
            'apartment_id' => [
                'nullable',
                Rule::exists('apartments', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'relation_type' => ['required_with:apartment_id', 'in:owner,tenant'],
            'start_date' => ['nullable', 'date'],
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
