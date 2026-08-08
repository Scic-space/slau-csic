<?php

namespace App\Models;

enum AcademicLevel: string
{
    case Certificate = 'certificate';
    case Diploma = 'diploma';
    case Bachelor = 'bachelor';
    case PostgraduateDiploma = 'postgraduate_diploma';
    case Master = 'master';

    public function durationYears(): int
    {
        return match ($this) {
            self::Certificate, self::PostgraduateDiploma => 1,
            self::Diploma, self::Master => 2,
            self::Bachelor => 3,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Certificate => 'Certificate',
            self::Diploma => 'Diploma',
            self::Bachelor => "Bachelor's Degree",
            self::PostgraduateDiploma => 'Postgraduate Diploma',
            self::Master => "Master's Degree",
        };
    }

    public static function fromProgram(string $program): self
    {
        return match (true) {
            str_contains($program, 'Master of') => self::Master,
            str_contains($program, 'Postgraduate Diploma') => self::PostgraduateDiploma,
            str_contains($program, 'Bachelor') => self::Bachelor,
            str_contains($program, 'Certificate') => self::Certificate,
            str_contains($program, 'Diploma') => self::Diploma,
            default => self::Bachelor,
        };
    }
}
