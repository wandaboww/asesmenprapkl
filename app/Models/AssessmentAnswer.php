<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['submission_id', 'question_id', 'answer'];

    public function submission()
    {
        return $this->belongsTo(AssessmentSubmission::class, 'submission_id');
    }

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }
}
