<?php

use App\Models\BudgetCategory;
use App\Models\Fine;
use App\Models\FineAppeal;
use App\Models\FinePayment;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\BudgetAlertNotification;
use App\Notifications\FineAppealReviewedNotification;
use App\Notifications\FineAppealSubmittedNotification;
use App\Notifications\FineIssuedNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\TransactionApprovalRequestedNotification;
use App\Notifications\TransactionApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FinanceNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_budget_alert_notification_is_sent_to_treasurer(): void
    {
        Notification::fake();

        $treasurer = User::factory()->create()->assignRole('Treasurer');
        $category = BudgetCategory::factory()->expense()->create([
            'name' => 'Events',
            'allocated_amount' => 1000.00,
        ]);

        $treasurer->notify(new BudgetAlertNotification($category, 'warning', 850.00, 1000.00, 85.0));

        Notification::assertSentTo(
            $treasurer,
            BudgetAlertNotification::class,
            function ($notification) {
                return $notification->alertLevel === 'warning'
                    && $notification->percentage === 85.0;
            }
        );
    }

    public function test_budget_alert_has_database_channel(): void
    {
        $treasurer = User::factory()->create()->assignRole('Treasurer');
        $category = BudgetCategory::factory()->expense()->create([
            'name' => 'Equipment',
            'allocated_amount' => 1000.00,
        ]);

        $treasurer->notify(new BudgetAlertNotification($category, 'critical', 950.00, 1000.00, 95.0));

        $this->assertDatabaseHas('notifications', [
            'type' => BudgetAlertNotification::class,
            'notifiable_id' => $treasurer->id,
        ]);
    }

    public function test_fine_issued_notification_is_sent_to_member(): void
    {
        Notification::fake();

        $member = User::factory()->create();
        $fine = Fine::factory()->create(['user_id' => $member->id]);

        $member->notify(new FineIssuedNotification($fine));

        Notification::assertSentTo($member, FineIssuedNotification::class);
    }

    public function test_payment_received_notification_is_sent_to_member(): void
    {
        Notification::fake();

        $member = User::factory()->create();
        $fine = Fine::factory()->create(['user_id' => $member->id]);
        $payment = FinePayment::factory()->create(['fine_id' => $fine->id]);

        $member->notify(new PaymentReceivedNotification($payment));

        Notification::assertSentTo($member, PaymentReceivedNotification::class);
    }

    public function test_transaction_approval_requested_notification_sent_to_approvers(): void
    {
        Notification::fake();

        $approver = User::factory()->create()->assignRole('Treasurer');
        $transaction = Transaction::factory()->create(['status' => 'pending']);

        $approver->notify(new TransactionApprovalRequestedNotification($transaction));

        Notification::assertSentTo($approver, TransactionApprovalRequestedNotification::class);
    }

    public function test_transaction_approved_notification_sent_to_creator(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'created_by' => $creator->id,
            'status' => 'approved',
        ]);

        $creator->notify(new TransactionApprovedNotification($transaction));

        Notification::assertSentTo($creator, TransactionApprovedNotification::class);
    }

    public function test_fine_appeal_submitted_notification_sent_to_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->create()->assignRole('admin');
        $fine = Fine::factory()->create();
        $appeal = FineAppeal::factory()->pending()->create(['fine_id' => $fine->id]);

        $admin->notify(new FineAppealSubmittedNotification($appeal));

        Notification::assertSentTo($admin, FineAppealSubmittedNotification::class);
    }

    public function test_fine_appeal_reviewed_notification_sent_to_member(): void
    {
        Notification::fake();

        $member = User::factory()->create();
        $fine = Fine::factory()->create(['user_id' => $member->id]);
        $appeal = FineAppeal::factory()->approved()->create(['fine_id' => $fine->id]);

        $member->notify(new FineAppealReviewedNotification($appeal));

        Notification::assertSentTo($member, FineAppealReviewedNotification::class);
    }

    public function test_fine_appeal_notification_indicates_outcome(): void
    {
        $member = User::factory()->create();
        $fine = Fine::factory()->create(['user_id' => $member->id]);
        $appeal = FineAppeal::factory()->approved()->create([
            'fine_id' => $fine->id,
            'decision_notes' => 'Valid reason provided.',
        ]);

        $notification = new FineAppealReviewedNotification($appeal);
        $data = $notification->toArray($member);

        $this->assertEquals('approved', $data['status']);
        $this->assertEquals('Valid reason provided.', $data['decision_notes']);
    }
}
