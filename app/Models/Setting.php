<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->getTypedValue();
    }

    public static function setValue(string $key, mixed $value, ?string $type = null, ?string $group = null, ?string $description = null): self
    {
        $type ??= gettype($value);
        $type = match ($type) {
            'boolean', 'bool' => 'boolean',
            'integer', 'int' => 'integer',
            'double', 'float' => 'float',
            'array' => 'json',
            default => 'string',
        };

        $stringValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $stringValue, 'type' => $type, 'group' => $group, 'description' => $description],
        );
    }

    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public static function isFeatureEnabled(string $feature): bool
    {
        return (bool) static::where('key', 'enable_'.$feature)->value('value');
    }
}
