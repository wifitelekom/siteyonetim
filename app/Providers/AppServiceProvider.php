<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Vendor;
use App\Policies\AccountPolicy;
use App\Policies\ApartmentPolicy;
use App\Policies\CashAccountPolicy;
use App\Policies\ChargePolicy;
use App\Policies\ExpensePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ReceiptPolicy;
use App\Policies\UserPolicy;
use App\Policies\VendorPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Charge::class, ChargePolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Apartment::class, ApartmentPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(CashAccount::class, CashAccountPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
