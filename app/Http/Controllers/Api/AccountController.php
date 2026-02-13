<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Account::class);

        $query = Account::query()->orderBy('code');

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($nested) use ($search) {
                $nested->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        $accounts = $query->paginate(20)->withQueryString();

        return response()->json([
            'data' => $accounts->through(fn (Account $account) => $this->mapAccount($account))->items(),
            'meta' => [
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
            ],
        ]);
    }

    public function meta(): JsonResponse
    {
        $this->authorize('viewAny', Account::class);

        return response()->json([
            'data' => [
                'types' => collect(AccountType::cases())->map(fn (AccountType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ])->values(),
            ],
        ]);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $this->authorize('create', Account::class);

        $account = Account::create($request->validated());

        return response()->json([
            'message' => 'Hesap olusturuldu.',
            'data' => $this->mapAccount($account),
        ], 201);
    }

    public function update(StoreAccountRequest $request, Account $account): JsonResponse
    {
        $this->authorize('update', $account);

        $account->update($request->validated());
        $account->refresh();

        return response()->json([
            'message' => 'Hesap guncellendi.',
            'data' => $this->mapAccount($account),
        ]);
    }

    public function destroy(Account $account): JsonResponse
    {
        $this->authorize('delete', $account);

        if ($account->charges()->exists() || $account->expenses()->exists()) {
            return response()->json([
                'message' => 'Bu hesaba bagli islemler var, silinemez.',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'message' => 'Hesap silindi.',
        ]);
    }

    private function mapAccount(Account $account): array
    {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type?->value ?? (string) $account->type,
            'type_label' => $account->type?->label() ?? (string) $account->type,
            'is_active' => (bool) $account->is_active,
            'full_name' => $account->full_name,
        ];
    }
}
