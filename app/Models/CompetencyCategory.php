<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyCategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_name', 'description', 'icon'];

    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class, 'category_id');
    }
}
