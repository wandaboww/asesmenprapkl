<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyCategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_name', 'description', 'icon'];

    public function getDisplayNameAttribute(): string
    {
        return self::normalizeDisplayName($this->category_name);
    }

    public static function normalizeDisplayName(?string $name): string
    {
        $normalized = strtolower(trim((string) $name));

        if ($normalized === '') {
            return 'Tidak Diketahui';
        }

        if (str_contains($normalized, 'administrasi') || str_contains($normalized, 'administration')) {
            return 'Administrasi';
        }

        if (str_contains($normalized, 'digital marketing') || str_contains($normalized, 'marketing')) {
            return 'Digital Marketing';
        }

        if (str_contains($normalized, 'pemrograman') || str_contains($normalized, 'programming')) {
            return 'Pemrograman';
        }

        return ucwords(str_replace('_', ' ', $normalized));
    }

    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class, 'category_id');
    }
}
