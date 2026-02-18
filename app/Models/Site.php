<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'city',
        'district',
        'zip_code',
        'tax_no',
        'tax_office',
        'phone',
        'contact_person',
        'contact_email',
        'contact_phone',
        'country',
        'language',
        'timezone',
        'currency',
        'permission_settings',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permission_settings' => 'array',
    ];

    public static function defaultPermissions(): array
    {
        return [
            'auditor_can_create' => false,
            'auditor_can_view_reports' => true,
            'auditor_can_edit_settings' => false,
            'auditor_can_view_support' => false,
            'auditor_can_view_personal_info' => false,
            'resident_can_view_balance' => true,
            'resident_can_view_receipts' => true,
            'resident_can_view_payments' => true,
            'resident_can_view_cash_accounts' => false,
            'resident_can_view_all_accounts' => false,
            'resident_cannot_edit_profile' => false,
            'resident_can_view_plates' => false,
            'support_team_access' => false,
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function cashAccounts(): HasMany
    {
        return $this->hasMany(CashAccount::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function apartmentGroups(): HasMany
    {
        return $this->hasMany(ApartmentGroup::class);
    }
}
