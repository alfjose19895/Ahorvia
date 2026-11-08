<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Actions\Transactions\RecordExpenseAction;
use App\Actions\Transactions\RecordIncomeAction;
use App\Actions\Transactions\RecordTransferAction;
use App\Actions\Transactions\ReverseJournalEntryAction;
use App\Enums\CategoryType;
use App\Enums\MovementType;
use App\Models\Account;
use App\Models\Category;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Finance\LedgerBootstrapService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Movimientos — Ahorvia')]
class TransactionsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'tipo', history: true)]
    public string $movementFilter = 'all';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'cuenta', history: true)]
    public ?int $accountFilter = null;

    #[Url(as: 'categoria', history: true)]
    public ?int $categoryFilter = null;

    #[Url(as: 'desde', history: true)]
    public ?string $dateFrom = null;

    #[Url(as: 'hasta', history: true)]
    public ?string $dateTo = null;

    // Control de modales
    public bool $showCreateModal = false;

    public bool $showReversalModal = false;

    // Formulario de creación
    public string $transactionType = 'expense'; // 'income', 'expense', 'transfer'

    public ?int $accountId = null;

    public ?int $destinationAccountId = null;

    public ?int $categoryId = null;

    public string $amount = '';

    public string $entryDate = '';

    public string $description = '';

    public ?string $reference = null;

    // Formulario de reverso
    public ?int $reversingEntryId = null;

    public ?string $reversalReason = null;

    public ?JournalEntry $reversingEntry = null;

    // Modal creación rápida de categoría "al vuelo"
    public bool $showQuickCategoryModal = false;

    public string $quickCategoryName = '';

    public string $quickCategoryType = 'expense';

    public string $quickCategoryColor = '#10B981';

    public function mount(): void
    {
        $this->entryDate = now()->toDateString();

        $tipo = request()->query('tipo', $this->movementFilter);
        if (in_array($tipo, ['income', 'expense', 'transfer'], true)) {
            $this->movementFilter = (string) $tipo;
            $this->openCreateModal((string) $tipo);
        }
    }

    public function updatedMovementFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAccountFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->movementFilter = 'all';
        $this->search = '';
        $this->accountFilter = null;
        $this->categoryFilter = null;
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    public function openCreateModal(string $type = 'expense'): void
    {
        $this->transactionType = $type;
        $this->amount = '';
        $this->description = '';
        $this->reference = null;
        $this->entryDate = now()->toDateString();
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function saveTransaction(
        RecordIncomeAction $incomeAction,
        RecordExpenseAction $expenseAction,
        RecordTransferAction $transferAction,
    ): void {
        /** @var User $user */
        $user = Auth::user();

        if ($this->transactionType === 'income') {
            $this->validate([
                'accountId' => ['required', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)],
                'categoryId' => ['required', 'integer', Rule::exists('categories', 'id')->where('user_id', $user->id)->where('is_active', true)],
                'amount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
                'entryDate' => ['required', 'date'],
                'description' => ['required', 'string', 'max:255'],
                'reference' => ['nullable', 'string', 'max:100'],
            ]);

            $incomeAction->execute($user, [
                'account_id' => (int) $this->accountId,
                'category_id' => (int) $this->categoryId,
                'amount' => $this->amount,
                'entry_date' => CarbonImmutable::parse($this->entryDate),
                'description' => $this->description,
                'reference' => $this->reference,
            ]);

            session()->flash('status', '¡Ingreso registrado y confirmado en libro contable!');
        } elseif ($this->transactionType === 'expense') {
            $this->validate([
                'accountId' => ['required', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)],
                'categoryId' => ['required', 'integer', Rule::exists('categories', 'id')->where('user_id', $user->id)->where('is_active', true)],
                'amount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
                'entryDate' => ['required', 'date'],
                'description' => ['required', 'string', 'max:255'],
                'reference' => ['nullable', 'string', 'max:100'],
            ]);

            $expenseAction->execute($user, [
                'account_id' => (int) $this->accountId,
                'category_id' => (int) $this->categoryId,
                'amount' => $this->amount,
                'entry_date' => CarbonImmutable::parse($this->entryDate),
                'description' => $this->description,
                'reference' => $this->reference,
            ]);

            session()->flash('status', '¡Gasto registrado con éxito!');
        } elseif ($this->transactionType === 'transfer') {
            $this->validate([
                'accountId' => ['required', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)],
                'destinationAccountId' => [
                    'required',
                    'integer',
                    'different:accountId',
                    Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true),
                ],
                'amount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
                'entryDate' => ['required', 'date'],
                'description' => ['required', 'string', 'max:255'],
                'reference' => ['nullable', 'string', 'max:100'],
            ]);

            $transferAction->execute($user, [
                'source_account_id' => (int) $this->accountId,
                'destination_account_id' => (int) $this->destinationAccountId,
                'amount' => $this->amount,
                'entry_date' => CarbonImmutable::parse($this->entryDate),
                'description' => $this->description,
                'reference' => $this->reference,
            ]);

            session()->flash('status', '¡Transferencia registrada y balanceada correctamente!');
        }

        $this->closeCreateModal();
    }

    public function openReversalModal(int $entryId): void
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var JournalEntry $entry */
        $entry = JournalEntry::query()->ownedBy($user)->with(['lines.account', 'lines.category'])->findOrFail($entryId);

        $this->reversingEntryId = $entryId;
        $this->reversingEntry = $entry;
        $this->reversalReason = '';
        $this->showReversalModal = true;
    }

    public function closeReversalModal(): void
    {
        $this->showReversalModal = false;
        $this->reversingEntryId = null;
        $this->reversingEntry = null;
    }

    public function executeReversal(ReverseJournalEntryAction $reverseAction): void
    {
        if ($this->reversingEntryId === null) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'reversalReason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $reverseAction->execute($user, $this->reversingEntryId, (string) $this->reversalReason);

        $this->closeReversalModal();
        session()->flash('status', '¡Asiento contable reversado correctamente! Se generó el contra-asiento de auditoría.');
    }

    public function openQuickCategoryModal(?string $type = null): void
    {
        $resolvedType = $type && in_array($type, ['expense', 'income'], true)
            ? $type
            : ($this->transactionType === 'income' ? 'income' : 'expense');

        $this->quickCategoryType = $resolvedType;
        $this->quickCategoryColor = $resolvedType === 'income' ? '#10B981' : '#EF4444';
        $this->quickCategoryName = '';
        $this->resetValidation();
        $this->showQuickCategoryModal = true;
    }

    public function closeQuickCategoryModal(): void
    {
        $this->showQuickCategoryModal = false;
        $this->resetValidation();
    }

    public function saveQuickCategory(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'quickCategoryName' => ['required', 'string', 'max:120'],
            'quickCategoryType' => ['required', Rule::in(['expense', 'income'])],
            'quickCategoryColor' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        $name = trim($this->quickCategoryName);
        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = 'cat-'.time();
        }

        $baseSlug = $slug;
        $counter = 1;
        while (Category::query()->ownedBy($user)->where('type', CategoryType::from($this->quickCategoryType))->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => $slug,
            'type' => CategoryType::from($this->quickCategoryType),
            'color' => $this->quickCategoryColor,
            'is_system' => false,
            'is_active' => true,
        ]);

        $this->categoryId = $category->id;
        $this->showQuickCategoryModal = false;
        $this->resetValidation();
        session()->flash('status', "Categoría '{$category->name}' creada y seleccionada.");
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = JournalEntry::query()
            ->ownedBy($user)
            ->with(['lines.account', 'lines.category'])
            ->latest('entry_date')
            ->latest('id');

        if ($this->movementFilter !== 'all') {
            if ($this->movementFilter === 'income') {
                $query->where('movement_type', MovementType::Income);
            } elseif ($this->movementFilter === 'expense') {
                $query->whereIn('movement_type', [MovementType::Expense, MovementType::CardPurchase]);
            } elseif ($this->movementFilter === 'transfer') {
                $query->where('movement_type', MovementType::Transfer);
            } elseif ($this->movementFilter === 'reversal') {
                $query->where('movement_type', MovementType::Reversal);
            }
        }

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(static function (Builder $nested) use ($term): void {
                $nested->where('description', 'like', $term)
                    ->orWhere('reference', 'like', $term)
                    ->orWhere('entry_number', 'like', $term);
            });
        }

        if ($this->accountFilter !== null) {
            $accId = $this->accountFilter;
            $query->whereHas('lines', static function (Builder $lineQ) use ($accId): void {
                $lineQ->where('account_id', $accId);
            });
        }

        if ($this->categoryFilter !== null) {
            $catId = $this->categoryFilter;
            $query->whereHas('lines', static function (Builder $lineQ) use ($catId): void {
                $lineQ->where('category_id', $catId);
            });
        }

        if ($this->dateFrom !== null && $this->dateFrom !== '') {
            $query->where('entry_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== null && $this->dateTo !== '') {
            $query->where('entry_date', '<=', $this->dateTo);
        }

        $entries = $query->paginate(15);

        $accounts = Account::query()->ownedBy($user)->userFacing()->active()->orderBy('name')->get();

        if (Category::query()->ownedBy($user)->count() === 0) {
            app(LedgerBootstrapService::class)->ensureDefaultCategories($user);
        }

        $categories = Category::query()->ownedBy($user)->active()->orderBy('name')->get();

        return view('livewire.transactions.transactions-index', [
            'entries' => $entries,
            'accounts' => $accounts,
            'incomeCategories' => $categories->where('type', CategoryType::Income)->values(),
            'expenseCategories' => $categories->where('type', CategoryType::Expense)->values(),
            'currency' => strtoupper($user->currency_code ?: 'USD'),
        ]);
    }
}
