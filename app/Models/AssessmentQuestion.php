<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'question_text', 'question_order', 'is_active'];

    public function category()
    {
        return $this->belongsTo(CompetencyCategory::class, 'category_id');
    }
}
