<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AssessmentQuestion;
use App\Models\CompetencyCategory;
use App\Models\AssessmentSubmission;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentRecommendation;
use App\Models\Industry;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student_id = session('student_id');
        $student = Student::with('studentClass')->findOrFail($student_id);
        $submission = AssessmentSubmission::where('student_id', $student_id)->first();
        
        $already_submitted = $submission ? true : false;
        return view('student.dashboard', compact('student', 'already_submitted', 'submission'));
    }

    public function assessment()
    {
        $student_id = session('student_id');
        if (AssessmentSubmission::where('student_id', $student_id)->exists()) {
            return redirect()->route('student.dashboard');
        }

        $categories = CompetencyCategory::with(['questions' => function($query) {
            $query->where('is_active', true)->orderBy('question_order');
        }])->get();

        return view('student.assessment', compact('categories'));
    }

    public function submitAssessment(Request $request)
    {
        $student_id = session('student_id');
        if (AssessmentSubmission::where('student_id', $student_id)->exists()) {
            return redirect()->route('student.dashboard');
        }

        try {
            DB::beginTransaction();

            $submission = AssessmentSubmission::create([
                'student_id' => $student_id,
            ]);

            $scores = [];
            foreach ($request->except('_token') as $key => $value) {
                if (strpos($key, 'q_') === 0) {
                    $question_id = str_replace('q_', '', $key);
                    $question = AssessmentQuestion::with('category')->find($question_id);
                    
                    if ($question) {
                        AssessmentAnswer::create([
                            'submission_id' => $submission->id,
                            'question_id' => $question_id,
                            'answer' => $value,
                        ]);

                        $catName = $question->category->category_name;
                        if (!isset($scores[$catName])) {
                            $scores[$catName] = ['total' => 0, 'yes' => 0];
                        }
                        $scores[$catName]['total']++;
                        if (strtolower($value) === 'ya' || strtolower($value) === 'yes') {
                            $scores[$catName]['yes']++;
                        }
                    }
                }
            }

            // Calculate percentages
            $programming_score = $this->calculateCatScore($scores, 'Pemrograman Website');
            $administration_score = $this->calculateCatScore($scores, 'Administrasi Perkantoran');
            $marketing_score = $this->calculateCatScore($scores, 'Digital Marketing');

            $competency_scores = [
                'programming' => $programming_score,
                'administration' => $administration_score,
                'marketing' => $marketing_score
            ];

            arsort($competency_scores);
            $top_2 = array_slice($competency_scores, 0, 2);
            $keys = array_keys($top_2);
            $primary = $keys[0];
            $secondary = $top_2[$keys[1]] > 0 ? $keys[1] : $primary;
            $highest_score = $top_2[$primary];

            // Finding industry
            $industry = Industry::where('primary_competency', $primary)
                ->where('secondary_competency', $secondary)
                ->first();

            if (!$industry) {
                $industry = Industry::where('primary_competency', $secondary)
                    ->where('secondary_competency', $primary)
                    ->first();
            }

            if (!$industry) {
                $industry = Industry::first();
            }

            AssessmentRecommendation::create([
                'submission_id' => $submission->id,
                'industry_id' => $industry->id,
                'score' => $highest_score,
            ]);

            DB::commit();

            return redirect()->route('student.result')->with('success', 'Assessment berhasil disubmit!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function result()
    {
        $student_id = session('student_id');
        $submission = AssessmentSubmission::with(['recommendation.industry', 'answers.question.category'])
            ->where('student_id', $student_id)
            ->first();

        if (!$submission) {
            return redirect()->route('student.dashboard');
        }

        $scores = [];
        foreach ($submission->answers as $ans) {
            $catName = $ans->question->category->category_name;
            if (!isset($scores[$catName])) {
                $scores[$catName] = ['total' => 0, 'yes' => 0, 'name' => $catName];
            }
            $scores[$catName]['total']++;
            if (strtolower($ans->answer) === 'ya') {
                $scores[$catName]['yes']++;
            }
        }

        return view('student.result', compact('submission', 'scores'));
    }

    private function calculateCatScore($scores, $key)
    {
        if (isset($scores[$key]) && $scores[$key]['total'] > 0) {
            return ($scores[$key]['yes'] / $scores[$key]['total']) * 100;
        }
        return 0;
    }

}
