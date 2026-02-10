<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollectPaymentRequest;
use App\Http\Requests\StoreBulkChargeRequest;
use App\Http\Requests\StoreChargeRequest;
use App\Models\Account;
use App\Models\Apartment;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Services\ChargeService;
use App\Services\ReceiptService;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function __construct(
        private ChargeService $chargeService,
        private ReceiptService $receiptService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Charge::class);

        $query = Charge::with(['apartment', 'account']);

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }
        if ($request->filled('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }
        if ($request->filled('status')) {
            match ($request->status) {
                'overdue' => $query->overdue(),
                'paid' => $query->paid(),
                'open' => $query->open(),
                default => null,
            };
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Owner/Tenant: restrict to own apartments
        $user = auth()->user();
        if ($user->hasAnyRole(['owner', 'tenant'])) {
            $query->whereIn('apartment_id', $user->apartment_ids);
        }

        $charges = $query->orderByDesc('due_date')->get();
        $apartments = Apartment::where('is_active', true)->orderBy('block')->orderBy('number')->get();

        return view('charges.index', compact('charges', 'apartments'));
    }

    public function create()
    {
        $this->authorize('create', Charge::class);

        $apartments = Apartment::where('is_active', true)->orderBy('block')->orderBy('number')->get();
        $accounts = Account::where('type', 'income')->where('is_active', true)->get();

        return view('charges.create', compact('apartments', 'accounts'));
    }

    public function store(StoreChargeRequest $request)
    {
        $this->authorize('create', Charge::class);

        $charge = $this->chargeService->createCharge($request->validated());

        return redirect()->route('charges.show', $charge)
            ->with('success', 'Tahakkuk başarıyla oluşturuldu.');
    }

    public function createBulk()
    {
        $this->authorize('create', Charge::class);

        $apartments = Apartment::where('is_active', true)->orderBy('block')->orderBy('number')->get();
        $accounts = Account::where('type', 'income')->where('is_active', true)->get();

        return view('charges.create-bulk', compact('apartments', 'accounts'));
    }

    public function storeBulk(StoreBulkChargeRequest $request)
    {
        $this->authorize('create', Charge::class);

        $count = $this->chargeService->createBulkCharges($request->validated());

        return redirect()->route('charges.index')
            ->with('success', "{$count} adet tahakkuk başarıyla oluşturuldu.");
    }

    public function show(Charge $charge)
    {
        $this->authorize('view', $charge);

        $charge->load(['apartment', 'account', 'receiptItems.receipt.cashAccount', 'creator']);
        $cashAccounts = CashAccount::where('is_active', true)->get();

        return view('charges.show', compact('charge', 'cashAccounts'));
    }

    public function collect(CollectPaymentRequest $request, Charge $charge)
    {
        $this->authorize('collect', $charge);

        $amount = min($request->validated('amount'), $charge->remaining);
        if ($amount <= 0) {
            return back()->with('error', 'Tahsil edilecek kalan tutar bulunmuyor.');
        }

        $this->receiptService->collectSinglePayment(
            array_merge($request->validated(), ['apartment_id' => $charge->apartment_id]),
            $charge,
            $amount
        );

        return redirect()->route('charges.show', $charge)
            ->with('success', 'Tahsilat başarıyla alındı.');
    }

    public function destroy(Charge $charge)
    {
        $this->authorize('delete', $charge);

        if (!$this->chargeService->deleteCharge($charge)) {
            return back()->with('error', 'Ödemesi olan tahakkuk silinemez.');
        }

        return redirect()->route('charges.index')
            ->with('success', 'Tahakkuk silindi.');
    }
}
