<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTwoCtQuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'soal_id',
        'label_opsi',
        'teks_opsi',
        'bobot_web',
        'bobot_marketing',
        'bobot_admin',
        'is_active',
    ];

    protected $casts = [
        'bobot_web' => 'integer',
        'bobot_marketing' => 'integer',
        'bobot_admin' => 'integer',
        'is_active' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(BatchTwoCtQuestion::class, 'soal_id');
    }
}
