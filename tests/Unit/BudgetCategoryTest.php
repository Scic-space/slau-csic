<?php

use App\Models\BudgetCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_budget_category(): void
    {
        $categoryData = [
            'name' => 'Test Category',
            'type' => 'expense',
            'allocated_amount' => 1000.00,
            'semester' => 'Fall',
            'academic_year' => '2025-2026',
            'description' => 'Test description',
            'is_active' => true,
        ];

        $category = BudgetCategory::create($categoryData);

        $this->assertDatabaseHas('budget_categories', $categoryData);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('expense', $category->type);
        $this->assertEquals(1000.00, $category->allocated_amount);
    }

    /** @test */
    public function it_can_create_income_category(): void
    {
        $categoryData = [
            'name' => 'Donations',
            'type' => 'income',
            'allocated_amount' => 5000.00,
            'semester' => 'Spring',
            'academic_year' => '2025-2026',
            'is_active' => true,
        ];

        $category = BudgetCategory::create($categoryData);

        $this->assertEquals('income', $category->type);
        $this->assertEquals(5000.00, $category->allocated_amount);
    }

    /** @test */
    public function it_can_deactivate_category(): void
    {
        $category = BudgetCategory::factory()->create(['is_active' => true]);

        $category->update(['is_active' => false]);

        $this->assertFalse($category->is_active);
    }

    /** @test */
    public function it_formats_amount_correctly(): void
    {
        $category = BudgetCategory::factory()->create([
            'allocated_amount' => 1234.56,
        ]);

        $this->assertEquals('$1,234.56', $category->formatted_amount);
    }

    /** @test */
    public function it_scopes_to_active_categories(): void
    {
        BudgetCategory::factory()->count(3)->create(['is_active' => false]);
        BudgetCategory::factory()->count(2)->create(['is_active' => true]);

        $activeCategories = BudgetCategory::active()->get();

        $this->assertCount(2, $activeCategories);
        $activeCategories->each(function ($category) {
            $this->assertTrue($category->is_active);
        });
    }

    /** @test */
    public function it_scopes_to_income_categories(): void
    {
        BudgetCategory::factory()->expense()->count(3)->create();
        BudgetCategory::factory()->income()->count(2)->create();

        $incomeCategories = BudgetCategory::income()->get();

        $this->assertCount(2, $incomeCategories);
        $incomeCategories->each(function ($category) {
            $this->assertEquals('income', $category->type);
        });
    }

    /** @test */
    public function it_scopes_to_expense_categories(): void
    {
        BudgetCategory::factory()->income()->count(2)->create();
        BudgetCategory::factory()->expense()->count(3)->create();

        $expenseCategories = BudgetCategory::expense()->get();

        $this->assertCount(3, $expenseCategories);
        $expenseCategories->each(function ($category) {
            $this->assertEquals('expense', $category->type);
        });
    }

    /** @test */
    public function it_returns_income_categories_list(): void
    {
        $incomeCategories = BudgetCategory::getIncomeCategories();

        $this->assertIsArray($incomeCategories);
        $this->assertContains('Membership Dues', $incomeCategories);
        $this->assertContains('Donations', $incomeCategories);
    }

    /** @test */
    public function it_returns_expense_categories_list(): void
    {
        $expenseCategories = BudgetCategory::getExpenseCategories();

        $this->assertIsArray($expenseCategories);
        $this->assertContains('Events', $expenseCategories);
        $this->assertContains('Equipment', $expenseCategories);
    }
}
