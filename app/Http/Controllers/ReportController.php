<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashAccount;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function index()
    {
        return view('reports.index');
    }

    // --- Cash Statement ---
    public function cashStatement(Request $request)
    {
        $cashAccounts = CashAccount::where('is_active', true)->get();

        if (!$request->filled('cash_account_id')) {
            return view('reports.cash-statement', ['cashAccounts' => $cashAccounts, 'data' => null]);
        }

        $siteId = auth()->user()->site_id;
        $request->validate([
            'cash_account_id' => [
                'required',
                Rule::exists('cash_accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $data = $this->reportService->cashStatement(
            $request->cash_account_id,
            Carbon::parse($request->from),
            Carbon::parse($request->to)
        );

        return view('reports.cash-statement', array_merge(['cashAccounts' => $cashAccounts], ['data' => $data]));
    }

    public function cashStatementPdf(Request $request)
    {
        $siteId = auth()->user()->site_id;
        $request->validate([
            'cash_account_id' => [
                'required',
                Rule::exists('cash_accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $data = $this->reportService->cashStatement(
            $request->cash_account_id,
            Carbon::parse($request->from),
            Carbon::parse($request->to)
        );

        $pdf = Pdf::loadView('pdf.cash-statement', $data);
        return $pdf->download('kasa-ekstresi-' . now()->format('Y-m-d') . '.pdf');
    }

    // --- Account Statement ---
    public function accountStatement(Request $request)
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        if (!$request->filled('account_id')) {
            return view('reports.account-statement', ['accounts' => $accounts, 'data' => null]);
        }

        $siteId = auth()->user()->site_id;
        $request->validate([
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)),
            ],
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $data = $this->reportService->accountStatement(
            $request->account_id,
            Carbon::parse($request->from),
            Carbon::parse($request->to)
        );

        return view('reports.account-statement', array_merge(['accounts' => $accounts], ['data' => $data]));
    }

    public function accountStatementPdf(Request $request)
    {
        $siteId = auth()->user()->site_id;
        $request->validate([
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)),
            ],
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $data = $this->reportService->accountStatement(
            $request->account_id,
            Carbon::parse($request->from),
            Carbon::parse($request->to)
        );

        $pdf = Pdf::loadView('pdf.account-statement', $data);
        return $pdf->download('hesap-ekstresi-' . now()->format('Y-m-d') . '.pdf');
    }

    // --- Collections ---
    public function collections(Request $request)
    {
        if (!$request->filled('from')) {
            return view('reports.collections', ['data' => null]);
        }

        $request->validate(['from' => 'required|date', 'to' => 'required|date']);

        $data = $this->reportService->collectionsReport(
            Carbon::parse($request->from),
            Carbon::parse($request->to)
        );

        return view('reports.collections', ['data' => $data]);
    }

    public function collectionsPdf(Request $request)
    {
        $request->validate(['from' => 'required|date', 'to' => 'required|date']);

        $data = $this->reportService->collectionsReport(
            Carbon::parse($request->from),
            Carbon::parse($request->to)
        );

        $pdf = Pdf::loadView('pdf.collections', $data);
        return $pdf->download('tahsilatlar-' . now()->format('Y-m-d') . '.pdf');
    }

    // --- Payments ---
    public function payments(Request $request)
    {
        if (!$request->filled('from')) {
            return view('reports.payments-report', ['data' => null]);
        }

        $request->validate(['from' => 'required|date', 'to' => 'required|date']);

        $data = $this->reportService->paymentsReport(
            Carbon::parse($request->from),
            Carbon::parse($request->to)
        );

        return view('reports.payments-report', ['data' => $data]);
    }

    public function paymentsPdf(Request $request)
    {
        $request->validate(['from' => 'required|date', 'to' => 'required|date']);

        $data = $this->reportService->paymentsReport(
            Carbon::parse($request->from),
            Carbon::parse($request->to)
        );

        $pdf = Pdf::loadView('pdf.payments-report', $data);
        return $pdf->download('odemeler-' . now()->format('Y-m-d') . '.pdf');
    }

    // --- Debt Status ---
    public function debtStatus()
    {
        $data = $this->reportService->debtStatus();
        return view('reports.debt-status', $data);
    }

    public function debtStatusPdf()
    {
        $data = $this->reportService->debtStatus();
        $pdf = Pdf::loadView('pdf.debt-status', $data);
        return $pdf->download('borc-durumu-' . now()->format('Y-m-d') . '.pdf');
    }

    // --- Receivable Status ---
    public function receivableStatus()
    {
        $data = $this->reportService->receivableStatus();
        return view('reports.receivable-status', $data);
    }

    public function receivableStatusPdf()
    {
        $data = $this->reportService->receivableStatus();
        $pdf = Pdf::loadView('pdf.receivable-status', $data);
        return $pdf->download('alacak-durumu-' . now()->format('Y-m-d') . '.pdf');
    }

    // --- Charge List ---
    public function chargeList(Request $request)
    {
        if (!$request->filled('period')) {
            return view('reports.charge-list', ['data' => null]);
        }

        $data = $this->reportService->chargeList($request->period);

        return view('reports.charge-list', ['data' => $data]);
    }

    public function chargeListPdf(Request $request)
    {
        $request->validate(['period' => 'required|string']);

        $data = $this->reportService->chargeList($request->period);

        $pdf = Pdf::loadView('pdf.charge-list', $data);
        return $pdf->download('tahakkuk-listesi-' . $request->period . '.pdf');
    }
}
