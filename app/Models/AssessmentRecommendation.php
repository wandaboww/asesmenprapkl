<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentRecommendation extends Model
{
    use HasFactory;

    protected $fillable = ['submission_id', 'industry_id', 'score'];

    public function submission()
    {
        return $this->belongsTo(AssessmentSubmission::class, 'submission_id');
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }
}
