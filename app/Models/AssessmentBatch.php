<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_name',
        'description',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class, 'batch_id');
    }

    public function submissions()
    {
        return $this->hasMany(AssessmentSubmission::class, 'batch_id');
    }
}
