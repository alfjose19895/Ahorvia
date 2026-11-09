<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Enums\CategoryType;
use App\Livewire\Debts\DebtsIndex;
use App\Livewire\Settings\SettingsManager;
use App\Livewire\Transactions\TransactionsIndex;
use App\Models\Category;
use App\Models\Debt;
use App\Models\DebtTypeCatalog;
use App\Models\FinancialInstitution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterCatalogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_edit_and_delete_categories_in_settings(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->set('activeTab', 'categories')
            ->set('categoryName', 'Mascotas y Veterinario')
            ->set('categoryType', 'expense')
            ->set('categoryColor', '#06B6D4')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $category = Category::query()
            ->ownedBy($user)
            ->where('name', 'Mascotas y Veterinario')
            ->first();

        $this->assertNotNull($category);
        $this->assertSame(CategoryType::Expense, $category->type);
        $this->assertSame('#06B6D4', $category->color);

        // Editar categoría
        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->call('openEditCategoryModal', $category->id)
            ->set('categoryName', 'Mascotas y Cuidado Animal')
            ->set('categoryColor', '#10B981')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertSame('Mascotas y Cuidado Animal', $category->fresh()?->name);
        $this->assertSame('#10B981', $category->fresh()?->color);

        // Desactivar / Activar
        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->call('toggleCategoryStatus', $category->id);

        $this->assertFalse($category->fresh()?->is_active);

        // Eliminar
        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->call('deleteCategory', $category->id);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_user_can_create_edit_and_delete_financial_institutions(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->set('activeTab', 'institutions')
            ->set('institutionName', 'Banco Internacional Ecuador')
            ->call('saveInstitution')
            ->assertHasNoErrors();

        $institution = FinancialInstitution::query()
            ->ownedBy($user)
            ->where('name', 'Banco Internacional Ecuador')
            ->first();

        $this->assertNotNull($institution);
        $this->assertTrue($institution->is_active);

        // Editar
        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->call('openEditInstitutionModal', $institution->id)
            ->set('institutionName', 'Banco Internacional del Austro')
            ->call('saveInstitution')
            ->assertHasNoErrors();

        $this->assertSame('Banco Internacional del Austro', $institution->fresh()?->name);

        // Desactivar
        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->call('toggleInstitutionStatus', $institution->id);

        $this->assertFalse($institution->fresh()?->is_active);

        // Eliminar
        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->call('deleteInstitution', $institution->id);

        $this->assertDatabaseMissing('financial_institutions', ['id' => $institution->id]);
    }

    public function test_user_can_quick_add_category_from_transactions_modal(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TransactionsIndex::class)
            ->call('openQuickCategoryModal', 'expense')
            ->set('quickCategoryName', 'Suscripciones y Software')
            ->set('quickCategoryType', 'expense')
            ->set('quickCategoryColor', '#8B5CF6')
            ->call('saveQuickCategory')
            ->assertHasNoErrors();

        $created = Category::query()
            ->ownedBy($user)
            ->where('name', 'Suscripciones y Software')
            ->first();

        $this->assertNotNull($created);
        $this->assertSame(CategoryType::Expense, $created->type);
        $this->assertSame('#8B5CF6', $created->color);
    }

    public function test_user_can_create_edit_and_delete_debt_types_in_settings(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->set('activeTab', 'debt_types')
            ->set('debtTypeName', 'Microcrédito Productivo')
            ->call('saveDebtType')
            ->assertHasNoErrors();

        $debtType = DebtTypeCatalog::query()
            ->ownedBy($user)
            ->where('name', 'Microcrédito Productivo')
            ->first();

        $this->assertNotNull($debtType);
        $this->assertSame('microcredito_productivo', $debtType->slug);
        $this->assertFalse($debtType->is_system);
        $this->assertTrue($debtType->is_active);

        // Editar
        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->call('openEditDebtTypeModal', $debtType->id)
            ->set('debtTypeName', 'Microcrédito Negocio Familiar')
            ->call('saveDebtType')
            ->assertHasNoErrors();

        $this->assertSame('Microcrédito Negocio Familiar', $debtType->fresh()?->name);

        // Desactivar
        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->call('toggleDebtTypeStatus', $debtType->id);

        $this->assertFalse($debtType->fresh()?->is_active);

        // Eliminar
        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->call('deleteDebtType', $debtType->id);

        $this->assertDatabaseMissing('debt_types', ['id' => $debtType->id]);
    }

    public function test_user_can_quick_add_debt_type_from_debts_modal_and_create_debt(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(DebtsIndex::class)
            ->call('openCreateModal')
            ->call('openQuickDebtTypeModal')
            ->set('quickDebtTypeName', 'Crédito Vehicular Auto')
            ->call('saveQuickDebtType')
            ->assertHasNoErrors();

        $createdType = DebtTypeCatalog::query()
            ->ownedBy($user)
            ->where('name', 'Crédito Vehicular Auto')
            ->first();

        $this->assertNotNull($createdType);
        $this->assertSame('credito_vehicular_auto', $createdType->slug);

        // El componente seleccionó automáticamente el nuevo tipo
        $component->assertSet('debtType', 'credito_vehicular_auto');

        // Guardar la deuda con este nuevo tipo dinámico
        $component->set('name', 'Financiamiento Mazda CX30')
            ->set('originalPrincipal', '15000.00')
            ->set('creditorName', 'Banco Pichincha')
            ->call('saveDebt')
            ->assertHasNoErrors();

        $debt = Debt::query()
            ->ownedBy($user)
            ->where('name', 'Financiamiento Mazda CX30')
            ->first();

        $this->assertNotNull($debt);
        $this->assertSame('credito_vehicular_auto', (string) $debt->type);
        $this->assertSame('Crédito Vehicular Auto', $debt->type->label());
    }
}
