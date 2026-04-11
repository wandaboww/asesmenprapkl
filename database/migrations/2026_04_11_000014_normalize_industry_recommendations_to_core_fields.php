<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $industries = DB::table('industries')->select('id', 'primary_competency', 'industry_name')->get();

        foreach ($industries as $industry) {
            $payload = $this->resolveCoreIndustryPayload((string) $industry->primary_competency);
            if (!$payload) {
                continue;
            }

            DB::table('industries')->where('id', $industry->id)->update(array_merge($payload, [
                'updated_at' => $now,
            ]));
        }

        // Force-normalize legacy startup naming to the Pemrograman field.
        DB::table('industries')
            ->where(function ($query) {
                $query->whereRaw('LOWER(industry_name) LIKE ?', ['%startup%'])
                    ->orWhereRaw('LOWER(industry_name) LIKE ?', ['%saas%']);
            })
            ->update([
                'industry_name' => 'Pemrograman',
                'description' => 'Rekomendasi bidang pemrograman: pengembangan aplikasi, website, database, dan sistem informasi.',
                'primary_competency' => 'programming',
                'secondary_competency' => 'marketing',
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Irreversible normalization; keep as no-op.
    }

    private function resolveCoreIndustryPayload(string $primaryCompetency): ?array
    {
        $key = $this->normalizeCompetencyKey($primaryCompetency);

        return match ($key) {
            'administrasi' => [
                'industry_name' => 'Administrasi',
                'description' => 'Rekomendasi bidang administrasi: pengarsipan, pengolahan data, dokumen, dan operasional perkantoran.',
                'primary_competency' => 'administration',
                'secondary_competency' => 'marketing',
            ],
            'digital_marketing' => [
                'industry_name' => 'Digital Marketing',
                'description' => 'Rekomendasi bidang digital marketing: konten kreatif, media sosial, branding, dan promosi digital.',
                'primary_competency' => 'marketing',
                'secondary_competency' => 'administration',
            ],
            'pemrograman' => [
                'industry_name' => 'Pemrograman',
                'description' => 'Rekomendasi bidang pemrograman: pengembangan aplikasi, website, database, dan sistem informasi.',
                'primary_competency' => 'programming',
                'secondary_competency' => 'marketing',
            ],
            default => null,
        };
    }

    private function normalizeCompetencyKey(string $value): ?string
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
};
