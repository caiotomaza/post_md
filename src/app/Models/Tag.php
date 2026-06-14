<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'display_mode', 'color_hex', 'emoji', 'position'];

    protected $casts = [
        'position' => 'integer',
    ];

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_tag')
            ->withPivot('position');
    }

    public static function normalizeHex(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return '#' . strtoupper($hex);
    }

    public static function isValidHex(string $hex): bool
    {
        return (bool) preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $hex);
    }
}
