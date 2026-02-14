<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function meta(Request $request): JsonResponse
    {
        $this->authorizeView($request);

        $cashAccounts = CashAccount::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (CashAccount $account) => [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $this->enumToString($account->type),
            ])->values();

        $accounts = Account::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'full_name' => $account->full_name,
            ])->values();

        return response()->json([
            'data' => [
                'cash_accounts' => $cashAccounts,
                'accounts' => $accounts,
            ],
        ]);
    }

    public function cashStatement(Request $request): JsonResponse
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
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $data = $this->reportService->cashStatement(
            (int) $validated['cash_account_id'],
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        return response()->json([
            'data' => [
                'account' => [
                    'id' => $data['account']->id,
                    'name' => $data['account']->name,
                    'type' => $this->enumToString($data['account']->type),
                ],
                'from' => $data['from']->toDateString(),
                'to' => $data['to']->toDateString(),
                'opening_balance' => (float) $data['opening_balance'],
                'closing_balance' => (float) $data['closing_balance'],
                'transactions' => collect($data['transactions'])->map(fn (array $tx) => [
                    'date' => Carbon::parse($tx['date'])->toDateString(),
                    'description' => $tx['description'] ?? '',
                    'type' => $tx['type'] ?? '',
                    'receipt_no' => $tx['receipt_no'] ?? null,
                    'amount' => (float) ($tx['amount'] ?? 0),
                    'direction' => $tx['direction'] ?? '',
                    'balance' => (float) ($tx['balance'] ?? 0),
                ])->values(),
            ],
        ]);
    }

    public function accountStatement(Request $request): JsonResponse
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
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $data = $this->reportService->accountStatement(
            (int) $validated['account_id'],
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        $rows = collect($data['charges'])->map(fn (Charge $charge) => [
            'date' => optional($charge->due_date)->toDateString(),
            'type' => 'charge',
            'description' => $charge->description ?: ('Tahakkuk - ' . optional($charge->apartment)->full_label),
            'amount' => (float) $charge->amount,
        ])->concat(
            collect($data['expenses'])->map(fn ($expense) => [
                'date' => optional($expense->expense_date)->toDateString(),
                'type' => 'expense',
                'description' => $expense->description ?: ('Gider - ' . optional($expense->vendor)->name),
                'amount' => (float) $expense->amount,
            ])
        )->sortBy('date')->values();

        return response()->json([
            'data' => [
                'account' => [
                    'id' => $data['account']->id,
                    'code' => $data['account']->code,
                    'name' => $data['account']->name,
                    'full_name' => $data['account']->full_name,
                ],
                'from' => $data['from']->toDateString(),
                'to' => $data['to']->toDateString(),
                'rows' => $rows,
                'totals' => [
                    'charges' => (float) $data['totalCharges'],
                    'expenses' => (float) $data['totalExpenses'],
                ],
            ],
        ]);
    }

    public function collections(Request $request): JsonResponse
    {
        $this->authorizeView($request);

        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $data = $this->reportService->collectionsReport(
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        return response()->json([
            'data' => [
                'from' => $data['from']->toDateString(),
                'to' => $data['to']->toDateString(),
                'total' => (float) $data['total'],
                'receipts' => collect($data['receipts'])->map(fn ($receipt) => [
                    'id' => $receipt->id,
                    'receipt_no' => $receipt->receipt_no,
                    'paid_at' => optional($receipt->paid_at)->toDateString(),
                    'apartment' => optional($receipt->apartment)->full_label,
                    'method' => $receipt->method?->value ?? (string) $receipt->method,
                    'cash_account' => optional($receipt->cashAccount)->name,
                    'total_amount' => (float) $receipt->total_amount,
                    'description' => $receipt->description,
                ])->values(),
            ],
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $this->authorizeView($request);

        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $data = $this->reportService->paymentsReport(
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        return response()->json([
            'data' => [
                'from' => $data['from']->toDateString(),
                'to' => $data['to']->toDateString(),
                'total' => (float) $data['total'],
                'payments' => collect($data['payments'])->map(fn ($payment) => [
                    'id' => $payment->id,
                    'paid_at' => optional($payment->paid_at)->toDateString(),
                    'vendor' => optional($payment->vendor)->name,
                    'method' => $payment->method?->value ?? (string) $payment->method,
                    'cash_account' => optional($payment->cashAccount)->name,
                    'total_amount' => (float) $payment->total_amount,
                    'description' => $payment->description,
                ])->values(),
            ],
        ]);
    }

    public function debtStatus(Request $request): JsonResponse
    {
        $this->authorizeView($request);

        $data = $this->reportService->debtStatus();

        return response()->json([
            'data' => [
                'grand_total' => (float) $data['grandTotal'],
                'debts' => collect($data['debts'])->map(fn (array $debt) => [
                    'apartment' => $debt['apartment'] ?? '-',
                    'resident' => $debt['resident'] ?? '-',
                    'total' => (float) ($debt['total'] ?? 0),
                    'paid' => (float) ($debt['paid'] ?? 0),
                    'remaining' => (float) ($debt['remaining'] ?? 0),
                    'overdue_count' => (int) ($debt['overdue_count'] ?? 0),
                    'open_count' => (int) ($debt['open_count'] ?? 0),
                ])->values(),
            ],
        ]);
    }

    public function receivableStatus(Request $request): JsonResponse
    {
        $this->authorizeView($request);

        $data = $this->reportService->receivableStatus();

        return response()->json([
            'data' => [
                'grand_total' => (float) $data['grandTotal'],
                'receivables' => collect($data['receivables'])->map(fn (array $row) => [
                    'vendor' => $row['vendor'] ?? '-',
                    'total' => (float) ($row['total'] ?? 0),
                    'paid' => (float) ($row['paid'] ?? 0),
                    'remaining' => (float) ($row['remaining'] ?? 0),
                ])->values(),
            ],
        ]);
    }

    public function chargeList(Request $request): JsonResponse
    {
        $this->authorizeView($request);

        $validated = $request->validate([
            'period' => ['required', 'string', 'max:7'],
        ]);

        $data = $this->reportService->chargeList($validated['period']);

        return response()->json([
            'data' => [
                'period' => $data['period'],
                'totals' => [
                    'amount' => (float) $data['totalAmount'],
                    'paid' => (float) $data['totalPaid'],
                    'remaining' => (float) $data['totalRemaining'],
                ],
                'charges' => collect($data['charges'])->map(function ($charge) {
                    return [
                        'id' => $charge->id,
                        'apartment' => optional($charge->apartment)->full_label,
                        'account' => optional($charge->account)->full_name,
                        'period' => $charge->period,
                        'due_date' => optional($charge->due_date)->toDateString(),
                        'amount' => (float) $charge->amount,
                        'paid_amount' => (float) $charge->paid_amount,
                        'remaining' => (float) $charge->remaining,
                        'status' => $charge->status?->value ?? null,
                    ];
                })->values(),
            ],
        ]);
    }

    private function authorizeView(Request $request): void
    {
        abort_unless($request->user()->can('reports.view'), 403);
    }

    private function enumToString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}
