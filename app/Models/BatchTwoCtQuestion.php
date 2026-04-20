<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTwoCtQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis_ct',
        'narasi_soal',
        'level_kesulitan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(BatchTwoCtQuestionOption::class, 'soal_id');
    }
}
