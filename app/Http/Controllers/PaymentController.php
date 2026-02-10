<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Vendor;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);

        $query = Payment::with(['vendor', 'cashAccount']);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('from')) {
            $query->where('paid_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('paid_at', '<=', $request->to);
        }

        $user = auth()->user();
        if ($user->hasRole('vendor') && $user->vendor) {
            $query->where('vendor_id', $user->vendor->id);
        }

        $payments = $query->orderByDesc('paid_at')->paginate(25)->withQueryString();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('payments.index', compact('payments', 'vendors'));
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load(['vendor', 'cashAccount', 'items.expense', 'creator']);

        return view('payments.show', compact('payment'));
    }
}
