<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'category_id',
        'question_text',
        'question_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function batch()
    {
        return $this->belongsTo(AssessmentBatch::class, 'batch_id');
    }

    public function category()
    {
        return $this->belongsTo(CompetencyCategory::class, 'category_id');
    }

    public function options()
    {
        return $this->hasMany(AssessmentQuestionOption::class, 'question_id');
    }
}
