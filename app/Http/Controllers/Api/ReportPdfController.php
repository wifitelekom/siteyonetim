<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportPdfController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function cashStatement(Request $request)
    {
        $this->authorizeView($request);

        $siteId = $request->user()->site_id;
        $validated = $request->validate([
            'cash_account_id' => [
                'required',
                Rule::exists('cash_accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);

        $data = $this->reportService->cashStatement(
            (int) $validated['cash_account_id'],
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        return Pdf::loadView('pdf.cash-statement', $data)
            ->download('kasa-ekstresi-' . now()->format('Y-m-d') . '.pdf');
    }

    public function accountStatement(Request $request)
    {
        $this->authorizeView($request);

        $siteId = $request->user()->site_id;
        $validated = $request->validate([
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)),
            ],
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);

        $data = $this->reportService->accountStatement(
            (int) $validated['account_id'],
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        return Pdf::loadView('pdf.account-statement', $data)
            ->download('hesap-ekstresi-' . now()->format('Y-m-d') . '.pdf');
    }

    public function collections(Request $request)
    {
        $this->authorizeView($request);

        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);

        $data = $this->reportService->collectionsReport(
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        return Pdf::loadView('pdf.collections', $data)
            ->download('tahsilatlar-' . now()->format('Y-m-d') . '.pdf');
    }

    public function payments(Request $request)
    {
        $this->authorizeView($request);

        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);

        $data = $this->reportService->paymentsReport(
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        return Pdf::loadView('pdf.payments-report', $data)
            ->download('odemeler-' . now()->format('Y-m-d') . '.pdf');
    }

    public function debtStatus(Request $request)
    {
        $this->authorizeView($request);

        $data = $this->reportService->debtStatus();

        return Pdf::loadView('pdf.debt-status', $data)
            ->download('borc-durumu-' . now()->format('Y-m-d') . '.pdf');
    }

    public function receivableStatus(Request $request)
    {
        $this->authorizeView($request);

        $data = $this->reportService->receivableStatus();

        return Pdf::loadView('pdf.receivable-status', $data)
            ->download('alacak-durumu-' . now()->format('Y-m-d') . '.pdf');
    }

    public function chargeList(Request $request)
    {
        $this->authorizeView($request);

        $validated = $request->validate([
            'period' => ['required', 'string', 'max:7'],
        ]);

        $data = $this->reportService->chargeList($validated['period']);

        return Pdf::loadView('pdf.charge-list', $data)
            ->download('tahakkuk-listesi-' . $validated['period'] . '.pdf');
    }

    private function authorizeView(Request $request): void
    {
        abort_unless($request->user()->can('reports.view'), 403);
    }
}
