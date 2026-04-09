<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentSubmission extends Model
{
    use HasFactory;

    const CREATED_AT = 'submitted_at';
    const UPDATED_AT = null;

    protected $fillable = ['student_id', 'submitted_at'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function answers()
    {
        return $this->hasMany(AssessmentAnswer::class, 'submission_id');
    }

    public function recommendation()
    {
        return $this->hasOne(AssessmentRecommendation::class, 'submission_id');
    }
}
