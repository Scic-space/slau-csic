<?php

namespace App\Models;

enum BadgeCriteriaType: string
{
    case EventsAttended = 'events_attended';
    case CtfCompleted = 'ctf_completed';
    case TotalPoints = 'total_points';
    case TeachingSessions = 'teaching_sessions';
    case StreakDays = 'streak_days';
    case CtfScore = 'ctf_score';
    case ClubTracks = 'club_tracks';
    case ExamPassed = 'exam_passed';
    case Competition = 'competition';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::EventsAttended => 'Events Attended',
            self::CtfCompleted => 'CTF Challenges Completed',
            self::TotalPoints => 'Total Points',
            self::TeachingSessions => 'Teaching Sessions',
            self::StreakDays => 'Streak Days',
            self::CtfScore => 'CTF Score',
            self::ClubTracks => 'Learning Tracks Completed',
            self::ExamPassed => 'Exams Passed',
            self::Competition => 'Competitions Entered',
            self::Custom => 'Custom (Manual Award)',
        };
    }
}
