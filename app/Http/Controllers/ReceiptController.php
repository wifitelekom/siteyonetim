<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Receipt::class);

        $query = Receipt::with(['apartment', 'cashAccount']);

        if ($request->filled('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }
        if ($request->filled('from')) {
            $query->where('paid_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('paid_at', '<=', $request->to);
        }

        $user = auth()->user();
        if ($user->hasAnyRole(['owner', 'tenant'])) {
            $query->whereIn('apartment_id', $user->apartment_ids);
        }

        $receipts = $query->orderByDesc('paid_at')->paginate(15);
        $apartments = Apartment::where('is_active', true)->orderBy('block')->orderBy('number')->get();

        return view('receipts.index', compact('receipts', 'apartments'));
    }

    public function show(Receipt $receipt)
    {
        $this->authorize('view', $receipt);

        $receipt->load(['apartment', 'cashAccount', 'items.charge.apartment', 'creator']);

        return view('receipts.show', compact('receipt'));
    }

    public function pdf(Receipt $receipt)
    {
        $this->authorize('print', $receipt);

        $receipt->load(['apartment', 'cashAccount', 'items.charge', 'creator']);
        $site = auth()->user()->site;

        $pdf = Pdf::loadView('pdf.receipt', compact('receipt', 'site'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('makbuz-' . $receipt->receipt_no . '.pdf');
    }
}
