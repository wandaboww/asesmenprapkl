<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    use HasFactory;

    protected $fillable = ['industry_name', 'description', 'primary_competency', 'secondary_competency'];

    public function getDisplayIndustryNameAttribute(): string
    {
        return match (self::normalizeCompetencyKey((string) $this->primary_competency)) {
            'administrasi' => 'Administrasi',
            'digital_marketing' => 'Digital Marketing',
            'pemrograman' => 'Pemrograman',
            default => $this->industry_name,
        };
    }

    public function getDisplayIndustryDescriptionAttribute(): string
    {
        return match (self::normalizeCompetencyKey((string) $this->primary_competency)) {
            'administrasi' => 'Rekomendasi bidang administrasi: pengarsipan, pengolahan data, dokumen, dan operasional perkantoran.',
            'digital_marketing' => 'Rekomendasi bidang digital marketing: konten kreatif, media sosial, branding, dan promosi digital.',
            'pemrograman' => 'Rekomendasi bidang pemrograman: pengembangan aplikasi, website, database, dan sistem informasi.',
            default => (string) $this->description,
        };
    }

    public static function normalizeCompetencyKey(string $value): ?string
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return null;
        }

        if (str_contains($normalized, 'administrasi') || str_contains($normalized, 'administration')) {
            return 'administrasi';
        }

        if (str_contains($normalized, 'digital marketing') || str_contains($normalized, 'marketing')) {
            return 'digital_marketing';
        }

        if (str_contains($normalized, 'pemrograman') || str_contains($normalized, 'programming')) {
            return 'pemrograman';
        }

        return null;
    }
}
