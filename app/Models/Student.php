<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['class_id', 'full_name', 'student_number'];

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
    }

    public function submissions()
    {
        return $this->hasMany(AssessmentSubmission::class);
    }

    public function submission()
    {
        return $this->hasOne(AssessmentSubmission::class)->latestOfMany('submitted_at');
    }
}
