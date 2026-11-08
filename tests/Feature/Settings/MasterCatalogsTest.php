<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Enums\CategoryType;
use App\Livewire\Settings\SettingsManager;
use App\Livewire\Transactions\TransactionsIndex;
use App\Models\Category;
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
}
