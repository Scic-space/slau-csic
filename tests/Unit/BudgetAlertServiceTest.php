<?php

use App\Services\BudgetAlertService;
use Tests\TestCase;

class BudgetAlertServiceTest extends TestCase
{
    /** @test */
    public function it_detects_over_budget_alert(): void
    {
        $alert = [
            'category' => 'Test Category',
            'type' => 'expense',
            'spent' => 1200.00,
            'allocated' => 1000.00,
            'percentage' => 120.0,
            'status' => 'over_budget',
            'remaining' => -200.00,
        ];

        $message = BudgetAlertService::formatAlertMessage(
            (object) $alert,
            'over_budget',
            1200.00,
            1000.00,
            120.0
        );

        $this->assertStringContainsString('Budget Alert: expense category', $message);
        $this->assertStringContainsString('has exceeded budget by $200.00', $message);
    }

    /** @test */
    public function it_detects_critical_alert(): void
    {
        $alert = [
            'category' => 'Test Category',
            'type' => 'expense',
            'spent' => 950.00,
            'allocated' => 1000.00,
            'percentage' => 95.0,
            'status' => 'critical',
            'remaining' => 50.00,
        ];

        $message = BudgetAlertService::formatAlertMessage(
            (object) $alert,
            'critical',
            950.00,
            1000.00,
            95.0
        );

        $this->assertStringContainsString('Critical Budget Alert: expense category', $message);
        $this->assertStringContainsString('has used 95% of budget ($950.00 / $1000.00)', $message);
        $this->assertStringContainsString('Only 10% remaining', $message);
    }

    /** @test */
    public function it_detects_warning_alert(): void
    {
        $alert = [
            'category' => 'Test Category',
            'type' => 'expense',
            'spent' => 850.00,
            'allocated' => 1000.00,
            'percentage' => 85.0,
            'status' => 'warning',
            'remaining' => 150.00,
        ];

        $message = BudgetAlertService::formatAlertMessage(
            (object) $alert,
            'warning',
            850.00,
            1000.00,
            85.0
        );

        $this->assertStringContainsString('Budget Warning: expense category', $message);
        $this->assertStringContainsString('has used 85% of budget ($850.00 / $1000.00)', $message);
    }

    /** @test */
    public function it_returns_budget_status(): void
    {
        // Create mock categories with different spending levels
        $this->mock(BudgetCategory::class, function ($mock) {
            $mock->shouldReceive('where')
                ->with('is_active', true)
                ->andReturnSelf()
                ->shouldReceive('get')
                ->andReturn([
                    (object) [
                        'name' => 'Over Budget Category',
                        'type' => 'expense',
                        'allocated_amount' => 1000.00,
                    ],
                    (object) [
                        'name' => 'Warning Category',
                        'type' => 'expense',
                        'allocated_amount' => 1000.00,
                    ],
                    (object) [
                        'name' => 'Normal Category',
                        'type' => 'expense',
                        'allocated_amount' => 1000.00,
                    ],
                ]);
        });

        $this->mock(Transaction::class, function ($mock) {
            $mock->shouldReceive('where')
                ->with('category', 'Over Budget Category')
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('status', 'approved')
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('type', 'expense')
                ->andReturnSelf()
                ->shouldReceive('whereYear')
                ->with('date', now()->year)
                ->andReturnSelf()
                ->shouldReceive('sum')
                ->with('amount')
                ->andReturn(1200.00);

            $mock->shouldReceive('where')
                ->with('category', 'Warning Category')
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('status', 'approved')
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('type', 'expense')
                ->andReturnSelf()
                ->shouldReceive('whereYear')
                ->with('date', now()->year)
                ->andReturnSelf()
                ->shouldReceive('sum')
                ->with('amount')
                ->andReturn(850.00);

            $mock->shouldReceive('where')
                ->with('category', 'Normal Category')
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('status', 'approved')
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('type', 'expense')
                ->andReturnSelf()
                ->shouldReceive('whereYear')
                ->with('date', now()->year)
                ->andReturnSelf()
                ->shouldReceive('sum')
                ->with('amount')
                ->andReturn(500.00);
        });

        $alerts = BudgetAlertService::getBudgetStatus();

        $this->assertCount(2, $alerts);

        // Check over budget alert
        $overBudgetAlert = $alerts[0];
        $this->assertEquals('Over Budget Category', $overBudgetAlert['category']);
        $this->assertEquals('expense', $overBudgetAlert['type']);
        $this->assertEquals(1200.00, $overBudgetAlert['spent']);
        $this->assertEquals(1000.00, $overBudgetAlert['allocated']);
        $this->assertEquals(120.0, $overBudgetAlert['percentage']);
        $this->assertEquals('over_budget', $overBudgetAlert['status']);
        $this->assertEquals(-200.00, $overBudgetAlert['remaining']);

        // Check critical alert
        $criticalAlert = $alerts[1];
        $this->assertEquals('Warning Category', $criticalAlert['category']);
        $this->assertEquals('expense', $criticalAlert['type']);
        $this->assertEquals(850.00, $criticalAlert['spent']);
        $this->assertEquals(1000.00, $criticalAlert['allocated']);
        $this->assertEquals(95.0, $criticalAlert['percentage']);
        $this->assertEquals('critical', $criticalAlert['status']);
        $this->assertEquals(50.00, $criticalAlert['remaining']);
    }
}
