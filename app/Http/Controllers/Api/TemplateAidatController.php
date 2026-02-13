<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateAidatRequest;
use App\Models\Account;
use App\Models\Apartment;
use App\Models\TemplateAidat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateAidatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $query = TemplateAidat::query()
            ->with(['account', 'apartments:id'])
            ->withCount('apartments')
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('scope')) {
            $query->where('scope', $request->string('scope'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $templates = $query->paginate(20)->withQueryString();

        return response()->json([
            'data' => $templates->through(fn (TemplateAidat $template) => $this->mapTemplate($template))->items(),
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
            ->where('type', 'income')
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'label' => $account->full_name,
            ])->values();

        $apartments = Apartment::query()
            ->where('is_active', true)
            ->orderBy('block')
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->map(fn (Apartment $apartment) => [
                'id' => $apartment->id,
                'label' => $apartment->full_label,
            ])->values();

        return response()->json([
            'data' => [
                'accounts' => $accounts,
                'apartments' => $apartments,
                'scopes' => [
                    ['value' => 'all', 'label' => 'Tum Daireler'],
                    ['value' => 'selected', 'label' => 'Secili Daireler'],
                ],
            ],
        ]);
    }

    public function show(Request $request, TemplateAidat $aidat): JsonResponse
    {
        $this->authorizeManage($request);

        $aidat->loadMissing(['account', 'apartments:id']);

        return response()->json([
            'data' => [
                ...$this->mapTemplate($aidat),
                'apartment_ids' => $aidat->apartments->pluck('id')->values(),
            ],
        ]);
    }

    public function store(StoreTemplateAidatRequest $request): JsonResponse
    {
        $this->authorizeManage($request);

        $payload = $request->safe()->except('apartment_ids');
        $payload['is_active'] = (bool) ($payload['is_active'] ?? true);

        $template = TemplateAidat::create($payload);

        if (($payload['scope'] ?? 'all') === 'selected' && $request->filled('apartment_ids')) {
            $template->apartments()->sync($request->input('apartment_ids', []));
        }

        $template->loadMissing(['account', 'apartments:id']);
        $template->loadCount('apartments');

        return response()->json([
            'message' => 'Aidat sablonu olusturuldu.',
            'data' => $this->mapTemplate($template),
        ], 201);
    }

    public function update(StoreTemplateAidatRequest $request, TemplateAidat $aidat): JsonResponse
    {
        $this->authorizeManage($request);

        $payload = $request->safe()->except('apartment_ids');
        if (array_key_exists('is_active', $payload)) {
            $payload['is_active'] = (bool) $payload['is_active'];
        }

        $aidat->update($payload);

        if (($payload['scope'] ?? $aidat->scope) === 'selected' && $request->filled('apartment_ids')) {
            $aidat->apartments()->sync($request->input('apartment_ids', []));
        } else {
            $aidat->apartments()->detach();
        }

        $aidat->refresh()->loadMissing(['account', 'apartments:id']);
        $aidat->loadCount('apartments');

        return response()->json([
            'message' => 'Aidat sablonu guncellendi.',
            'data' => $this->mapTemplate($aidat),
        ]);
    }

    public function destroy(Request $request, TemplateAidat $aidat): JsonResponse
    {
        $this->authorizeManage($request);

        $aidat->apartments()->detach();
        $aidat->delete();

        return response()->json([
            'message' => 'Aidat sablonu silindi.',
        ]);
    }

    private function mapTemplate(TemplateAidat $template): array
    {
        return [
            'id' => $template->id,
            'name' => $template->name,
            'amount' => (float) $template->amount,
            'due_day' => (int) $template->due_day,
            'scope' => $template->scope,
            'scope_label' => $template->scope === 'all' ? 'Tum Daireler' : 'Secili Daireler',
            'is_active' => (bool) $template->is_active,
            'apartments_count' => (int) ($template->apartments_count ?? $template->apartments->count()),
            'account' => $template->account ? [
                'id' => $template->account->id,
                'label' => $template->account->full_name,
            ] : null,
        ];
    }

    private function authorizeManage(Request $request): void
    {
        abort_unless($request->user()->can('templates.manage'), 403);
    }
}
