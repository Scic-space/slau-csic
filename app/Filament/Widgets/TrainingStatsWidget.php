<?php

namespace App\Filament\Widgets;

use App\Models\Exam;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrainingStatsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin', 'CTF Lead']) ?? false;
    }

    protected function getStats(): array
    {
        $activeCourses = Training::published()
            ->where(function ($q) {
                $q->where('available_from', '<=', now())
                    ->where('available_until', '>=', now());
            })
            ->count();

        $totalEnrollments = TrainingEnrollment::count();
        $completedEnrollments = TrainingEnrollment::where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 1) : 0;

        $totalExams = Exam::count();
        $certificatesIssued = \App\Models\CertificateEligibility::where('status', 'issued')->count();

        return [
            Stat::make('Active Courses', $activeCourses)
                ->description('Currently available trainings')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Completion Rate', $completionRate.'%')
                ->description($completedEnrollments.' completed of '.$totalEnrollments.' enrollments')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($completionRate >= 70 ? 'success' : 'warning'),

            Stat::make('Total Exams', $totalExams)
                ->description('Available examinations')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Certificates Issued', $certificatesIssued)
                ->description('Certificates awarded')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
