<?php

namespace App\Http\Controllers\Api;

use App\Enums\TemplatePeriod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateExpenseRequest;
use App\Models\Account;
use App\Models\TemplateExpense;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $query = TemplateExpense::query()
            ->with(['vendor', 'account'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('period')) {
            $query->where('period', $request->string('period'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $templates = $query->paginate(20)->withQueryString();

        return response()->json([
            'data' => $templates->through(fn (TemplateExpense $template) => $this->mapTemplate($template))->items(),
            'meta' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
            ],
        ]);
    }

    public function meta(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $accounts = Account::query()
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'label' => $account->full_name,
            ])->values();

        $vendors = Vendor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Vendor $vendor) => [
                'id' => $vendor->id,
                'label' => $vendor->name,
            ])->values();

        return response()->json([
            'data' => [
                'accounts' => $accounts,
                'vendors' => $vendors,
                'periods' => collect(TemplatePeriod::cases())->map(fn (TemplatePeriod $period) => [
                    'value' => $period->value,
                    'label' => $this->periodLabel($period->value),
                ])->values(),
            ],
        ]);
    }

    public function show(Request $request, TemplateExpense $expense): JsonResponse
    {
        $this->authorizeManage($request);

        $expense->loadMissing(['vendor', 'account']);

        return response()->json([
            'data' => $this->mapTemplate($expense),
        ]);
    }

    public function store(StoreTemplateExpenseRequest $request): JsonResponse
    {
        $this->authorizeManage($request);

        $payload = $request->validated();
        $payload['is_active'] = (bool) ($payload['is_active'] ?? true);

        $template = TemplateExpense::create($payload);
        $template->loadMissing(['vendor', 'account']);

        return response()->json([
            'message' => 'Gider sablonu olusturuldu.',
            'data' => $this->mapTemplate($template),
        ], 201);
    }

    public function update(StoreTemplateExpenseRequest $request, TemplateExpense $expense): JsonResponse
    {
        $this->authorizeManage($request);

        $payload = $request->validated();
        if (array_key_exists('is_active', $payload)) {
            $payload['is_active'] = (bool) $payload['is_active'];
        }

        $expense->update($payload);
        $expense->refresh()->loadMissing(['vendor', 'account']);

        return response()->json([
            'message' => 'Gider sablonu guncellendi.',
            'data' => $this->mapTemplate($expense),
        ]);
    }

    public function destroy(Request $request, TemplateExpense $expense): JsonResponse
    {
        $this->authorizeManage($request);

        $expense->delete();

        return response()->json([
            'message' => 'Gider sablonu silindi.',
        ]);
    }

    private function mapTemplate(TemplateExpense $template): array
    {
        $periodValue = $template->period?->value ?? (string) $template->period;

        return [
            'id' => $template->id,
            'name' => $template->name,
            'amount' => (float) $template->amount,
            'due_day' => (int) $template->due_day,
            'period' => $periodValue,
            'period_label' => $this->periodLabel($periodValue),
            'is_active' => (bool) $template->is_active,
            'last_generated_at' => optional($template->last_generated_at)->toDateString(),
            'vendor' => $template->vendor ? [
                'id' => $template->vendor->id,
                'label' => $template->vendor->name,
            ] : null,
            'account' => $template->account ? [
                'id' => $template->account->id,
                'label' => $template->account->full_name,
            ] : null,
        ];
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'monthly' => 'Aylik',
            'quarterly' => '3 Aylik',
            'yearly' => 'Yillik',
            default => $period,
        };
    }

    private function authorizeManage(Request $request): void
    {
        abort_unless($request->user()->can('templates.manage'), 403);
    }
}
