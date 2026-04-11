<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'question_id',
        'question_option_id',
        'answer',
        'score_value',
        'max_score_value',
    ];

    protected $casts = [
        'score_value' => 'float',
        'max_score_value' => 'float',
    ];

    public function submission()
    {
        return $this->belongsTo(AssessmentSubmission::class, 'submission_id');
    }

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }

    public function option()
    {
        return $this->belongsTo(AssessmentQuestionOption::class, 'question_option_id');
    }
}
