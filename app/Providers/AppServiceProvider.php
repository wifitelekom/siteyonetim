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
use App\Models\Document;
use App\Models\Note;
use App\Models\Reminder;
use App\Models\ExpenseNote;
use App\Models\Vendor;
use App\Policies\AccountPolicy;
use App\Policies\ApartmentPolicy;
use App\Policies\CashAccountPolicy;
use App\Policies\ChargePolicy;
use App\Policies\DocumentPolicy;
use App\Policies\ExpenseNotePolicy;
use App\Policies\ExpensePolicy;
use App\Policies\NotePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ReceiptPolicy;
use App\Policies\ReminderPolicy;
use App\Policies\UserPolicy;
use App\Policies\VendorPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-write', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        Gate::policy(Charge::class, ChargePolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Apartment::class, ApartmentPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(CashAccount::class, CashAccountPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Note::class, NotePolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Reminder::class, ReminderPolicy::class);
        Gate::policy(ExpenseNote::class, ExpenseNotePolicy::class);
    }
}
