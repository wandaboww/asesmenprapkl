<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTwoCtStudentResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'attempt_no',
        'total_web',
        'total_marketing',
        'total_admin',
        'persen_web',
        'persen_marketing',
        'persen_admin',
        'rekomendasi',
        'jawaban_json',
        'submitted_at',
    ];

    protected $casts = [
        'total_web' => 'integer',
        'total_marketing' => 'integer',
        'total_admin' => 'integer',
        'persen_web' => 'float',
        'persen_marketing' => 'float',
        'persen_admin' => 'float',
        'jawaban_json' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'siswa_id');
    }
}
