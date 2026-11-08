<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\FinancialInstitution;
use App\Models\User;
use App\Services\Finance\LedgerBootstrapService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración y Seguridad — Ahorvia')]
class SettingsManager extends Component
{
    public string $activeTab = 'profile'; // 'profile', 'categories', 'institutions'

    public string $name = '';

    public string $email = '';

    public string $currency_code = 'USD';

    public string $timezone = 'UTC';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $twoFactorPassword = '';

    public string $twoFactorCode = '';

    public bool $showingQrCode = false;

    public bool $showingConfirmation = false;

    public bool $showingRecoveryCodes = false;

    public ?string $profileStatus = null;

    public ?string $passwordStatus = null;

    public ?string $twoFactorStatus = null;

    // Catálogo Maestro: Categorías
    public string $categoryFilter = 'all'; // 'all', 'expense', 'income'

    public bool $showCategoryModal = false;

    public ?int $editingCategoryId = null;

    public string $categoryName = '';

    public string $categoryType = 'expense';

    public string $categoryColor = '#10B981';

    public ?string $categoryStatus = null;

    // Catálogo Maestro: Instituciones / Bancos
    public bool $showInstitutionModal = false;

    public ?int $editingInstitutionId = null;

    public string $institutionName = '';

    public ?string $institutionStatus = null;

    public function mount(): void
    {
        $user = $this->currentUser();
        $this->authorize('view', $user);

        if (request()->query('tab') && in_array(request()->query('tab'), ['profile', 'categories', 'institutions'], true)) {
            $this->activeTab = (string) request()->query('tab');
        }

        $this->name = $user->name;
        $this->email = $user->email;
        $this->currency_code = $user->currency_code ?: 'USD';
        $this->timezone = $user->timezone ?: 'UTC';

        if ($user->two_factor_secret !== null && $user->two_factor_confirmed_at === null) {
            $this->showingQrCode = true;
            $this->showingConfirmation = true;
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['profile', 'categories', 'institutions'], true)) {
            $this->activeTab = $tab;
            $this->resetValidation();
        }
    }

    public function updateProfile(UpdateUserProfileInformation $updater): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->profileStatus = null;

        try {
            $updater->update($user, [
                'name' => $this->name,
                'email' => $this->email,
                'currency_code' => $this->currency_code,
                'timezone' => $this->timezone,
            ]);
        } catch (ValidationException $exception) {
            $this->setValidationErrors($exception);

            return;
        }

        $this->profileStatus = 'Perfil y preferencias financieras actualizados correctamente.';
    }

    public function updatePassword(UpdateUserPassword $updater): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->passwordStatus = null;

        try {
            $updater->update($user, [
                'current_password' => $this->current_password,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);
        } catch (ValidationException $exception) {
            $this->setValidationErrors($exception);

            return;
        }

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->passwordStatus = 'Contraseña actualizada de forma segura.';
    }

    public function enableTwoFactor(EnableTwoFactorAuthentication $enable): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->resetErrorBag();
        $this->twoFactorStatus = null;

        if (! $this->verifyTwoFactorPassword($user)) {
            return;
        }

        $enable($user);
        $user->refresh();

        $this->twoFactorPassword = '';
        $this->showingQrCode = true;
        $this->showingConfirmation = true;
        $this->showingRecoveryCodes = false;
        $this->twoFactorStatus = 'Escanea el código QR con tu aplicación autenticadora e ingresa el código de 6 dígitos para confirmar.';
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirm): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->resetErrorBag();
        $this->twoFactorStatus = null;

        try {
            $confirm($user, trim($this->twoFactorCode));
        } catch (ValidationException $exception) {
            $this->addError('twoFactorCode', 'El código de verificación 2FA ingresado no es válido.');

            return;
        }

        $user->refresh();

        $this->twoFactorCode = '';
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = true;
        $this->twoFactorStatus = 'Autenticación de dos factores (2FA) activada y confirmada. Guarda tus códigos de recuperación en un lugar seguro.';
    }

    public function showRecoveryCodes(): void
    {
        $user = $this->currentUser();
        $this->authorize('view', $user);
        $this->resetErrorBag();

        if (! $this->verifyTwoFactorPassword($user)) {
            return;
        }

        $this->twoFactorPassword = '';
        $this->showingRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->resetErrorBag();

        if (! $this->verifyTwoFactorPassword($user)) {
            return;
        }

        $generate($user);
        $user->refresh();

        $this->twoFactorPassword = '';
        $this->showingRecoveryCodes = true;
        $this->twoFactorStatus = 'Se han generado nuevos códigos de recuperación. Los anteriores quedaron invalidados.';
    }

    public function disableTwoFactor(DisableTwoFactorAuthentication $disable): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->resetErrorBag();

        if (! $this->verifyTwoFactorPassword($user)) {
            return;
        }

        $disable($user);
        $user->refresh();

        $this->twoFactorPassword = '';
        $this->twoFactorCode = '';
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
        $this->twoFactorStatus = 'La autenticación de dos factores (2FA) ha sido desactivada.';
    }

    // =========================================================================
    // Métodos Catálogo Maestro: Categorías
    // =========================================================================

    public function openCreateCategoryModal(?string $type = null): void
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryType = $type ?: 'expense';
        $this->categoryColor = $this->categoryType === 'income' ? '#10B981' : '#EF4444';
        $this->categoryStatus = null;
        $this->resetValidation();
        $this->showCategoryModal = true;
    }

    public function openEditCategoryModal(int $id): void
    {
        $user = $this->currentUser();
        /** @var Category $category */
        $category = Category::query()->ownedBy($user)->findOrFail($id);

        $this->editingCategoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categoryType = $category->type->value;
        $this->categoryColor = $category->color ?: '#10B981';
        $this->categoryStatus = null;
        $this->resetValidation();
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->editingCategoryId = null;
        $this->resetValidation();
    }

    public function saveCategory(): void
    {
        $user = $this->currentUser();
        $this->validate([
            'categoryName' => ['required', 'string', 'max:120'],
            'categoryType' => ['required', Rule::in(['expense', 'income'])],
            'categoryColor' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        $slug = Str::slug($this->categoryName);
        if ($slug === '') {
            $slug = 'cat-'.time();
        }

        if ($this->editingCategoryId !== null) {
            /** @var Category $category */
            $category = Category::query()->ownedBy($user)->findOrFail($this->editingCategoryId);

            $exists = Category::query()
                ->ownedBy($user)
                ->where('type', CategoryType::from($this->categoryType))
                ->where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists();

            if ($exists) {
                $slug .= '-'.$category->id;
            }

            $category->update([
                'name' => trim($this->categoryName),
                'slug' => $slug,
                'type' => CategoryType::from($this->categoryType),
                'color' => $this->categoryColor,
            ]);

            $this->categoryStatus = 'Categoría actualizada exitosamente.';
        } else {
            $baseSlug = $slug;
            $counter = 1;
            while (Category::query()->ownedBy($user)->where('type', CategoryType::from($this->categoryType))->where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            Category::query()->create([
                'user_id' => $user->id,
                'name' => trim($this->categoryName),
                'slug' => $slug,
                'type' => CategoryType::from($this->categoryType),
                'color' => $this->categoryColor,
                'is_system' => false,
                'is_active' => true,
            ]);

            $this->categoryStatus = 'Nueva categoría registrada exitosamente.';
        }

        $this->showCategoryModal = false;
        $this->editingCategoryId = null;
    }

    public function toggleCategoryStatus(int $id): void
    {
        $user = $this->currentUser();
        /** @var Category $category */
        $category = Category::query()->ownedBy($user)->findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();

        $this->categoryStatus = $category->is_active
            ? "Categoría '{$category->name}' activada."
            : "Categoría '{$category->name}' archivada/desactivada.";
    }

    public function deleteCategory(int $id): void
    {
        $user = $this->currentUser();
        /** @var Category $category */
        $category = Category::query()->ownedBy($user)->findOrFail($id);

        $hasUsage = $category->journalLines()->exists() || $category->budgetLines()->exists();

        if ($hasUsage) {
            $category->is_active = false;
            $category->save();
            $this->categoryStatus = "La categoría '{$category->name}' tiene movimientos o presupuestos asociados, por lo que fue desactivada para preservar la integridad contable.";
        } else {
            $name = $category->name;
            $category->delete();
            $this->categoryStatus = "Categoría '{$name}' eliminada correctamente.";
        }
    }

    // =========================================================================
    // Métodos Catálogo Maestro: Instituciones Financieras / Bancos
    // =========================================================================

    public function openCreateInstitutionModal(): void
    {
        $this->editingInstitutionId = null;
        $this->institutionName = '';
        $this->institutionStatus = null;
        $this->resetValidation();
        $this->showInstitutionModal = true;
    }

    public function openEditInstitutionModal(int $id): void
    {
        $user = $this->currentUser();
        /** @var FinancialInstitution $inst */
        $inst = FinancialInstitution::query()->ownedBy($user)->findOrFail($id);

        $this->editingInstitutionId = $inst->id;
        $this->institutionName = $inst->name;
        $this->institutionStatus = null;
        $this->resetValidation();
        $this->showInstitutionModal = true;
    }

    public function closeInstitutionModal(): void
    {
        $this->showInstitutionModal = false;
        $this->editingInstitutionId = null;
        $this->resetValidation();
    }

    public function saveInstitution(): void
    {
        $user = $this->currentUser();
        $this->validate([
            'institutionName' => ['required', 'string', 'max:120'],
        ]);

        $name = trim($this->institutionName);

        if ($this->editingInstitutionId !== null) {
            /** @var FinancialInstitution $inst */
            $inst = FinancialInstitution::query()->ownedBy($user)->findOrFail($this->editingInstitutionId);

            $exists = FinancialInstitution::query()
                ->ownedBy($user)
                ->where('name', $name)
                ->where('id', '!=', $inst->id)
                ->exists();

            if ($exists) {
                $this->addError('institutionName', 'Ya existe un banco o institución con este nombre en tu catálogo.');

                return;
            }

            $inst->update(['name' => $name]);
            $this->institutionStatus = 'Institución bancaria actualizada exitosamente.';
        } else {
            FinancialInstitution::query()->firstOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                ['is_active' => true]
            );
            $this->institutionStatus = 'Nueva institución financiera registrada en el catálogo.';
        }

        $this->showInstitutionModal = false;
        $this->editingInstitutionId = null;
    }

    public function toggleInstitutionStatus(int $id): void
    {
        $user = $this->currentUser();
        /** @var FinancialInstitution $inst */
        $inst = FinancialInstitution::query()->ownedBy($user)->findOrFail($id);
        $inst->is_active = ! $inst->is_active;
        $inst->save();

        $this->institutionStatus = $inst->is_active
            ? "Institución '{$inst->name}' activada."
            : "Institución '{$inst->name}' desactivada.";
    }

    public function deleteInstitution(int $id): void
    {
        $user = $this->currentUser();
        /** @var FinancialInstitution $inst */
        $inst = FinancialInstitution::query()->ownedBy($user)->findOrFail($id);
        $name = $inst->name;
        $inst->delete();

        $this->institutionStatus = "Institución '{$name}' eliminada del catálogo maestro.";
    }

    public function render(): View
    {
        $user = $this->currentUser()->fresh() ?? $this->currentUser();

        $bootstrap = app(LedgerBootstrapService::class);
        if (Category::query()->ownedBy($user)->count() === 0) {
            $bootstrap->ensureDefaultCategories($user);
        }
        if (FinancialInstitution::query()->ownedBy($user)->count() === 0) {
            $bootstrap->ensureDefaultInstitutions($user);
        }

        /** @var array<string, array{name: string, symbol: string}> $currencies */
        $currencies = config('ahorvia.finance.supported_currencies', []);

        $categoriesQuery = Category::query()
            ->ownedBy($user)
            ->withCount(['journalLines', 'budgetLines'])
            ->orderBy('type')
            ->orderBy('name');

        if ($this->categoryFilter !== 'all') {
            $categoriesQuery->where('type', $this->categoryFilter);
        }

        $categories = $categoriesQuery->get();
        $institutions = FinancialInstitution::query()->ownedBy($user)->orderBy('name')->get();

        return view('livewire.settings.settings-manager', [
            'user' => $user,
            'currencies' => $currencies,
            'categories' => $categories,
            'institutions' => $institutions,
            'commonTimezones' => [
                'UTC' => 'UTC (Tiempo Universal Coordinado)',
                'America/Bogota' => 'América / Bogotá (UTC-5)',
                'America/Lima' => 'América / Lima (UTC-5)',
                'America/Mexico_City' => 'América / Ciudad de México (UTC-6)',
                'America/Santiago' => 'América / Santiago',
                'America/Argentina/Buenos_Aires' => 'América / Buenos Aires (UTC-3)',
                'Europe/Madrid' => 'Europa / Madrid',
                'America/New_York' => 'América / Nueva York',
            ],
        ]);
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    private function verifyTwoFactorPassword(User $user): bool
    {
        if ($this->twoFactorPassword === '' || ! Hash::check($this->twoFactorPassword, $user->password)) {
            $this->addError('twoFactorPassword', 'Confirma tu contraseña actual para modificar la configuración 2FA.');

            return false;
        }

        return true;
    }

    private function setValidationErrors(ValidationException $exception): void
    {
        $this->resetErrorBag();

        foreach ($exception->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $this->addError($field, $message);
            }
        }
    }
}
