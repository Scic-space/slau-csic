<?php

namespace App\Notifications;

class NotificationTypeConfig
{
    /**
     * Get the category, icon, and colors for a notification type.
     *
     * @return array{category: string, icon: string, bgColor: string, textColor: string, iconBg: string}
     */
    public static function for(string $notificationClass): array
    {
        $shortName = class_basename($notificationClass);

        return match (true) {
            str_contains($shortName, 'EventReminder') || str_contains($shortName, 'EventCancelled') || str_contains($shortName, 'MeetingReminder') || str_contains($shortName, 'MeetingCancelled') || str_contains($shortName, 'MeetingRescheduled') => [
                'category' => 'events',
                'icon' => 'calendar',
                'bgColor' => 'bg-blue-100 dark:bg-blue-900/40',
                'textColor' => 'text-blue-600 dark:text-blue-400',
                'iconBg' => 'bg-blue-100 dark:bg-blue-900/40',
            ],
            str_contains($shortName, 'Election') || str_contains($shortName, 'Nomination') || str_contains($shortName, 'Vote') => [
                'category' => 'elections',
                'icon' => 'ballot',
                'bgColor' => 'bg-indigo-100 dark:bg-indigo-900/40',
                'textColor' => 'text-indigo-600 dark:text-indigo-400',
                'iconBg' => 'bg-indigo-100 dark:bg-indigo-900/40',
            ],
            str_contains($shortName, 'Fine') || str_contains($shortName, 'Payment') || str_contains($shortName, 'Transaction') || str_contains($shortName, 'Budget') => [
                'category' => 'fines',
                'icon' => 'dollar',
                'bgColor' => 'bg-red-100 dark:bg-red-900/40',
                'textColor' => 'text-red-600 dark:text-red-400',
                'iconBg' => 'bg-red-100 dark:bg-red-900/40',
            ],
            str_contains($shortName, 'Member') || str_contains($shortName, 'Registration') || str_contains($shortName, 'Welcome') || str_contains($shortName, 'Membership') => [
                'category' => 'membership',
                'icon' => 'user',
                'bgColor' => 'bg-green-100 dark:bg-green-900/40',
                'textColor' => 'text-green-600 dark:text-green-400',
                'iconBg' => 'bg-green-100 dark:bg-green-900/40',
            ],
            str_contains($shortName, 'Exam') || str_contains($shortName, 'Assignment') || str_contains($shortName, 'Grade') => [
                'category' => 'exams',
                'icon' => 'academic',
                'bgColor' => 'bg-purple-100 dark:bg-purple-900/40',
                'textColor' => 'text-purple-600 dark:text-purple-400',
                'iconBg' => 'bg-purple-100 dark:bg-purple-900/40',
            ],
            str_contains($shortName, 'Challenge') || str_contains($shortName, 'Competition') || str_contains($shortName, 'Ctf') || str_contains($shortName, 'Writeup') => [
                'category' => 'ctf',
                'icon' => 'trophy',
                'bgColor' => 'bg-amber-100 dark:bg-amber-900/40',
                'textColor' => 'text-amber-600 dark:text-amber-400',
                'iconBg' => 'bg-amber-100 dark:bg-amber-900/40',
            ],
            default => [
                'category' => 'system',
                'icon' => 'bell',
                'bgColor' => 'bg-gray-100 dark:bg-gray-700',
                'textColor' => 'text-gray-600 dark:text-gray-400',
                'iconBg' => 'bg-gray-100 dark:bg-gray-700',
            ],
        };
    }

    /**
     * Get categories for filter tabs.
     *
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'all' => 'All',
            'unread' => 'Unread',
            'events' => 'Events',
            'elections' => 'Elections',
            'fines' => 'Fines',
            'membership' => 'Membership',
            'exams' => 'Exams',
            'ctf' => 'CTF',
            'system' => 'System',
        ];
    }

    /**
     * Get all notification classes belonging to a category.
     *
     * @return array<string>
     */
    public static function classesInCategory(string $category): array
    {
        $all = [
            'events' => ['EventReminder', 'EventCancelled', 'MeetingReminder', 'MeetingCancelled', 'MeetingRescheduled', 'CompetitionReminder'],
            'elections' => ['ElectionOpened', 'ElectionClosed', 'ElectionReminder', 'ElectionResults', 'NominationStatus', 'VoteConfirmation', 'VoteReceipt'],
            'fines' => ['FineIssued', 'FineOverdue', 'FineAppeal', 'PaymentReceived', 'TransactionApproval', 'TransactionApproved', 'BudgetAlert'],
            'membership' => ['MemberApproval', 'MemberRejection', 'MemberSuspended', 'MemberRequires', 'MembershipExpiring', 'RegistrationApproved', 'RegistrationRejected', 'WelcomeMember', 'PromotedFromWaitlist'],
            'exams' => ['ExamGraded', 'ExamPublished', 'AssignmentApproved', 'AssignmentGenerated'],
            'ctf' => ['ChallengeSolved', 'CompetitionResults', 'WriteupReviewed', 'TeamMember'],
            'system' => ['BroadcastMessage'],
        ];

        return $all[$category] ?? [];
    }
}
