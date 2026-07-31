<?php

namespace App\Models;

enum NewsCategory: string
{
    case ThreatIntel = 'threat_intel';
    case Vulnerabilities = 'vulnerabilities';
    case PolicyCompliance = 'policy_compliance';
    case Industry = 'industry';
    case ToolsResearch = 'tools_research';

    public function label(): string
    {
        return match ($this) {
            self::ThreatIntel => 'Threat Intelligence',
            self::Vulnerabilities => 'Vulnerabilities',
            self::PolicyCompliance => 'Policy & Compliance',
            self::Industry => 'Industry',
            self::ToolsResearch => 'Tools & Research',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ThreatIntel => 'danger',
            self::Vulnerabilities => 'warning',
            self::PolicyCompliance => 'info',
            self::Industry => 'success',
            self::ToolsResearch => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ThreatIntel => 'heroicon-o-shield-exclamation',
            self::Vulnerabilities => 'heroicon-o-bug-ant',
            self::PolicyCompliance => 'heroicon-o-document-text',
            self::Industry => 'heroicon-o-building-office-2',
            self::ToolsResearch => 'heroicon-o-wrench-screwdriver',
        };
    }
}
