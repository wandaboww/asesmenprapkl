<?php

namespace App\Http\Controllers;

use App\Models\AssessmentBatch;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentRecommendation;
use App\Models\AssessmentSubmission;
use App\Models\CompetencyCategory;
use App\Models\Industry;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student_id = session('student_id');
        $student = Student::with('studentClass')->findOrFail($student_id);

        $batches = AssessmentBatch::orderBy('id')->get();
        $activeBatch = $batches->first(function (AssessmentBatch $batch) {
            return $batch->is_active;
        });

        $submissions = AssessmentSubmission::with('recommendation.industry')
            ->where('student_id', $student_id)
            ->get()
            ->keyBy('batch_id');

        [$batchOneId, $batchTwoId] = $this->resolveBatchOneAndTwoIds();
        $batchOneSubmission = $batchOneId ? $submissions->get($batchOneId) : null;

        $questionCountsByBatch = AssessmentQuestion::query()
            ->where('is_active', true)
            ->select('batch_id', DB::raw('COUNT(*) as total_questions'))
            ->groupBy('batch_id')
            ->pluck('total_questions', 'batch_id');

        $batchProgresses = $batches->map(function (AssessmentBatch $batch) use ($submissions, $questionCountsByBatch, $batchOneSubmission, $batchTwoId) {
            $submission = $submissions->get($batch->id);
            $activeQuestionCount = (int) ($questionCountsByBatch[$batch->id] ?? 0);
            $hasQuestions = $activeQuestionCount > 0;
            $categoryHint = null;
            $blockedReason = null;

            if ($batchTwoId && $batch->id === $batchTwoId) {
                if (!$batchOneSubmission) {
                    $blockedReason = 'Batch 2 terbuka setelah Anda menyelesaikan Batch 1.';
                } else {
                    $targetCategory = $this->resolveCategoryForBatchTwoFromSubmission($batchOneSubmission);
                    if ($targetCategory) {
                        $categoryHint = $targetCategory->display_name;

                        $activeQuestionCount = AssessmentQuestion::query()
                            ->where('batch_id', $batch->id)
                            ->where('category_id', $targetCategory->id)
                            ->where('is_active', true)
                            ->count();

                        $hasQuestions = $activeQuestionCount > 0;

                        if (!$hasQuestions) {
                            $blockedReason = 'Soal Batch 2 untuk bidang ' . $categoryHint . ' belum tersedia.';
                        }
                    } else {
                        $blockedReason = 'Rekomendasi Batch 1 belum tersedia. Hubungi admin untuk sinkronisasi data.';
                    }
                }
            }

            $canStart = !$submission && $batch->is_active && $hasQuestions && !$blockedReason;

            if ($submission) {
                $statusLabel = 'Selesai';
                $statusClass = 'success';
            } elseif (!$batch->is_active) {
                $statusLabel = 'Belum Aktif';
                $statusClass = 'secondary';
            } elseif ($blockedReason) {
                $statusLabel = 'Menunggu Syarat';
                $statusClass = 'warning';
            } elseif (!$hasQuestions) {
                $statusLabel = 'Soal Belum Tersedia';
                $statusClass = 'warning';
            } else {
                $statusLabel = 'Belum Dikerjakan';
                $statusClass = 'info';
            }

            return [
                'batch' => $batch,
                'submission' => $submission,
                'has_questions' => $hasQuestions,
                'can_start' => $canStart,
                'blocked_reason' => $blockedReason,
                'category_hint' => $categoryHint,
                'status_label' => $statusLabel,
                'status_class' => $statusClass,
            ];
        });

        return view('student.dashboard', compact('student', 'activeBatch', 'batchProgresses'));
    }

    public function assessment(Request $request)
    {
        $student_id = session('student_id');
        $activeBatch = AssessmentBatch::where('is_active', true)->orderByDesc('id')->first();
        $requestedBatchId = $request->integer('batch_id');

        $selectedBatch = $requestedBatchId
            ? AssessmentBatch::find($requestedBatchId)
            : $activeBatch;

        if (!$selectedBatch) {
            return redirect()->route('student.dashboard')->with('error', 'Belum ada batch asesmen yang aktif saat ini.');
        }

        if (!$selectedBatch->is_active) {
            return redirect()->route('student.dashboard')->with('error', 'Batch ' . $selectedBatch->batch_name . ' belum aktif untuk dikerjakan.');
        }

        if (AssessmentSubmission::where('student_id', $student_id)->where('batch_id', $selectedBatch->id)->exists()) {
            return redirect()->route('student.dashboard');
        }

        [$batchOneId, $batchTwoId] = $this->resolveBatchOneAndTwoIds();
        $forcedCategory = null;

        if ($batchTwoId && $selectedBatch->id === $batchTwoId) {
            if (!$batchOneId) {
                return redirect()->route('student.dashboard')->with('error', 'Batch 1 belum tersedia. Batch 2 belum dapat dikerjakan.');
            }

            $batchOneSubmission = AssessmentSubmission::with('recommendation.industry')
                ->where('student_id', $student_id)
                ->where('batch_id', $batchOneId)
                ->first();

            if (!$batchOneSubmission) {
                return redirect()->route('student.dashboard')->with('error', 'Selesaikan Batch 1 terlebih dahulu sebelum mengerjakan Batch 2.');
            }

            $forcedCategory = $this->resolveCategoryForBatchTwoFromSubmission($batchOneSubmission);
            if (!$forcedCategory) {
                return redirect()->route('student.dashboard')->with('error', 'Rekomendasi bidang dari Batch 1 belum terbaca. Hubungi admin.');
            }
        }

        if ($forcedCategory) {
            $categories = CompetencyCategory::whereKey($forcedCategory->id)
                ->with(['questions' => function ($query) use ($selectedBatch) {
                    $query->where('batch_id', $selectedBatch->id)
                        ->where('is_active', true)
                        ->orderBy('question_order')
                        ->with(['options' => function ($optionQuery) {
                            $optionQuery->where('is_active', true)->orderBy('option_order')->orderBy('id');
                        }]);
                }])
                ->get();
        } else {
            $categories = CompetencyCategory::with(['questions' => function ($query) use ($selectedBatch) {
                $query->where('batch_id', $selectedBatch->id)
                    ->where('is_active', true)
                    ->orderBy('question_order')
                    ->with(['options' => function ($optionQuery) {
                        $optionQuery->where('is_active', true)->orderBy('option_order')->orderBy('id');
                    }]);
            }])->get();
        }

        $totalQuestions = $categories->sum(function ($category) {
            return $category->questions->count();
        });

        if ($totalQuestions === 0) {
            return redirect()->route('student.dashboard')->with('error', 'Batch yang dipilih belum memiliki soal yang bisa dikerjakan.');
        }

        return view('student.assessment', compact('categories', 'selectedBatch', 'forcedCategory'));
    }

    public function submitAssessment(Request $request)
    {
        $student_id = session('student_id');
        $activeBatch = AssessmentBatch::where('is_active', true)->orderByDesc('id')->first();
        $requestedBatchId = $request->integer('batch_id');

        $selectedBatch = $requestedBatchId
            ? AssessmentBatch::find($requestedBatchId)
            : $activeBatch;

        if (!$selectedBatch) {
            return redirect()->route('student.dashboard')->with('error', 'Belum ada batch asesmen yang aktif saat ini.');
        }

        if (!$selectedBatch->is_active) {
            return redirect()->route('student.dashboard')->with('error', 'Batch ' . $selectedBatch->batch_name . ' belum aktif untuk dikerjakan.');
        }

        if (AssessmentSubmission::where('student_id', $student_id)->where('batch_id', $selectedBatch->id)->exists()) {
            return redirect()->route('student.dashboard');
        }

        [$batchOneId, $batchTwoId] = $this->resolveBatchOneAndTwoIds();
        $forcedCategory = null;

        if ($batchTwoId && $selectedBatch->id === $batchTwoId) {
            if (!$batchOneId) {
                return redirect()->route('student.dashboard')->with('error', 'Batch 1 belum tersedia. Batch 2 belum dapat dikerjakan.');
            }

            $batchOneSubmission = AssessmentSubmission::with('recommendation.industry')
                ->where('student_id', $student_id)
                ->where('batch_id', $batchOneId)
                ->first();

            if (!$batchOneSubmission) {
                return redirect()->route('student.dashboard')->with('error', 'Selesaikan Batch 1 terlebih dahulu sebelum mengerjakan Batch 2.');
            }

            $forcedCategory = $this->resolveCategoryForBatchTwoFromSubmission($batchOneSubmission);
            if (!$forcedCategory) {
                return redirect()->route('student.dashboard')->with('error', 'Rekomendasi bidang dari Batch 1 belum terbaca. Hubungi admin.');
            }
        }

        $questions = AssessmentQuestion::with([
            'category',
            'options' => function ($query) {
                $query->where('is_active', true)->orderBy('option_order')->orderBy('id');
            },
        ])
            ->where('batch_id', $selectedBatch->id)
            ->when($forcedCategory, function ($query) use ($forcedCategory) {
                $query->where('category_id', $forcedCategory->id);
            })
            ->where('is_active', true)
            ->orderBy('question_order')
            ->get();

        if ($questions->isEmpty()) {
            return redirect()->route('student.dashboard')->with('error', 'Batch yang dipilih belum memiliki soal yang bisa dikerjakan.');
        }

        $answersByQuestion = [];
        foreach ($questions as $question) {
            $selectedOptionId = $request->input('q_' . $question->id);
            if (!$selectedOptionId) {
                return back()->with('error', 'Harap jawab semua pertanyaan sebelum submit.')->withInput();
            }

            $selectedOption = $question->options->firstWhere('id', (int) $selectedOptionId);
            if (!$selectedOption) {
                return back()->with('error', 'Terdapat jawaban yang tidak valid. Silakan isi ulang asesmen.')->withInput();
            }

            $answersByQuestion[$question->id] = $selectedOption;
        }

        try {
            DB::beginTransaction();

            $submission = AssessmentSubmission::create([
                'student_id' => $student_id,
                'batch_id' => $selectedBatch->id,
            ]);

            $competencyScores = [
                'administrasi' => ['obtained' => 0, 'max' => 0],
                'digital_marketing' => ['obtained' => 0, 'max' => 0],
                'pemrograman' => ['obtained' => 0, 'max' => 0],
            ];

            foreach ($questions as $question) {
                $selectedOption = $answersByQuestion[$question->id];
                $maxQuestionScore = (float) $question->options->max('option_score');
                if ($maxQuestionScore <= 0) {
                    $maxQuestionScore = 1;
                }

                AssessmentAnswer::create([
                    'submission_id' => $submission->id,
                    'question_id' => $question->id,
                    'question_option_id' => $selectedOption->id,
                    'answer' => $selectedOption->option_text,
                    'score_value' => (float) $selectedOption->option_score,
                    'max_score_value' => $maxQuestionScore,
                ]);

                $competencyKey = $this->normalizeCompetencyKey($question->category->category_name ?? '');
                if ($competencyKey) {
                    $competencyScores[$competencyKey]['obtained'] += (float) $selectedOption->option_score;
                    $competencyScores[$competencyKey]['max'] += $maxQuestionScore;
                }
            }

            $competencyPercentages = [];
            foreach ($competencyScores as $key => $scorePack) {
                $competencyPercentages[$key] = $scorePack['max'] > 0
                    ? ($scorePack['obtained'] / $scorePack['max']) * 100
                    : 0;
            }

            arsort($competencyPercentages);
            $sortedKeys = array_keys($competencyPercentages);
            $primary = $sortedKeys[0] ?? 'pemrograman';
            $secondary = $sortedKeys[1] ?? $primary;

            if (($competencyPercentages[$secondary] ?? 0) <= 0) {
                $secondary = $primary;
            }

            $highestScore = $competencyPercentages[$primary] ?? 0;

            $industry = $this->findIndustryByPrimaryCompetency($primary)
                ?? $this->findIndustryByPrimaryCompetency($secondary)
                ?? $this->findIndustryByPrimaryCompetency('pemrograman')
                ?? $this->createCoreIndustryForCompetency($primary);

            AssessmentRecommendation::create([
                'submission_id' => $submission->id,
                'industry_id' => $industry->id,
                'score' => $highestScore,
            ]);

            DB::commit();

            return redirect()->route('student.result', ['batch_id' => $selectedBatch->id])->with('success', 'Assessment berhasil disubmit!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function result(Request $request)
    {
        $student_id = session('student_id');
        $activeBatch = AssessmentBatch::where('is_active', true)->orderByDesc('id')->first();
        $requestedBatchId = $request->integer('batch_id');

        $submissionQuery = AssessmentSubmission::with(['batch', 'recommendation.industry', 'answers.question.category'])
            ->where('student_id', $student_id)
            ->latest('submitted_at');

        if ($requestedBatchId) {
            $submissionQuery->where('batch_id', $requestedBatchId);
        } elseif ($activeBatch) {
            $submissionQuery->where('batch_id', $activeBatch->id);
        }

        $submission = $submissionQuery->first();

        if (!$submission) {
            return redirect()->route('student.dashboard')->with('error', 'Hasil asesmen untuk batch yang dipilih belum tersedia.');
        }

        $scores = [];
        foreach ($submission->answers as $ans) {
            if (!$ans->question || !$ans->question->category) {
                continue;
            }

            $catName = CompetencyCategory::normalizeDisplayName($ans->question->category->category_name);
            if (!isset($scores[$catName])) {
                $scores[$catName] = [
                    'name' => $catName,
                    'obtained' => 0,
                    'max' => 0,
                    'percentage' => 0,
                ];
            }

            $scores[$catName]['obtained'] += (float) $ans->score_value;
            $maxScore = (float) $ans->max_score_value;
            $scores[$catName]['max'] += $maxScore > 0 ? $maxScore : 1;
        }

        foreach ($scores as &$score) {
            $score['percentage'] = $score['max'] > 0
                ? ($score['obtained'] / $score['max']) * 100
                : 0;
        }

        return view('student.result', compact('submission', 'scores'));
    }

    private function resolveBatchOneAndTwoIds(): array
    {
        $batches = AssessmentBatch::orderBy('id')->get();

        $batchOne = $batches->first(function (AssessmentBatch $batch) {
            return strtolower(trim((string) $batch->batch_name)) === 'batch 1';
        }) ?? $batches->first();

        $batchTwo = $batches->first(function (AssessmentBatch $batch) {
            return strtolower(trim((string) $batch->batch_name)) === 'batch 2';
        });

        if (!$batchTwo) {
            $batchTwo = $batches->first(function (AssessmentBatch $batch) use ($batchOne) {
                return $batchOne && $batch->id !== $batchOne->id;
            });
        }

        return [$batchOne?->id, $batchTwo?->id];
    }

    private function resolveCategoryForBatchTwoFromSubmission(?AssessmentSubmission $batchOneSubmission): ?CompetencyCategory
    {
        if (!$batchOneSubmission || !$batchOneSubmission->recommendation || !$batchOneSubmission->recommendation->industry) {
            return null;
        }

        $industry = $batchOneSubmission->recommendation->industry;
        $competencyKey = $this->normalizeCompetencyKey((string) $industry->primary_competency)
            ?? $this->normalizeCompetencyKey((string) $industry->display_industry_name);

        if (!$competencyKey) {
            return null;
        }

        $targetCategoryLabel = match ($competencyKey) {
            'administrasi' => 'Administrasi',
            'digital_marketing' => 'Digital Marketing',
            default => 'Pemrograman',
        };

        return CompetencyCategory::all()->first(function (CompetencyCategory $category) use ($targetCategoryLabel) {
            return $category->display_name === $targetCategoryLabel;
        });
    }

    private function normalizeCompetencyKey(string $value): ?string
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return null;
        }

        if (str_contains($normalized, 'administrasi') || str_contains($normalized, 'administration')) {
            return 'administrasi';
        }

        if (str_contains($normalized, 'digital marketing') || str_contains($normalized, 'marketing')) {
            return 'digital_marketing';
        }

        if (str_contains($normalized, 'pemrograman') || str_contains($normalized, 'programming')) {
            return 'pemrograman';
        }

        return null;
    }

    private function findIndustryByPrimaryCompetency(string $competencyKey): ?Industry
    {
        $primaryAliases = $this->getCompetencyAliases($competencyKey);

        return Industry::whereIn('primary_competency', $primaryAliases)
            ->whereRaw('LOWER(industry_name) NOT LIKE ?', ['%startup%'])
            ->whereRaw('LOWER(industry_name) NOT LIKE ?', ['%saas%'])
            ->orderBy('id')
            ->first();
    }

    private function createCoreIndustryForCompetency(string $competencyKey): Industry
    {
        $definition = $this->getCoreIndustryDefinition($competencyKey);

        $industry = Industry::firstOrCreate(
            ['industry_name' => $definition['industry_name']],
            [
                'description' => $definition['description'],
                'primary_competency' => $definition['primary_competency'],
                'secondary_competency' => $definition['secondary_competency'],
            ]
        );

        if (!$industry->wasRecentlyCreated) {
            $industry->update([
                'description' => $definition['description'],
                'primary_competency' => $definition['primary_competency'],
                'secondary_competency' => $definition['secondary_competency'],
            ]);
        }

        return $industry;
    }

    private function getCoreIndustryDefinition(string $competencyKey): array
    {
        return match ($competencyKey) {
            'administrasi' => [
                'industry_name' => 'Administrasi',
                'description' => 'Rekomendasi bidang administrasi: pengarsipan, pengolahan data, dokumen, dan operasional perkantoran.',
                'primary_competency' => 'administration',
                'secondary_competency' => 'marketing',
            ],
            'digital_marketing' => [
                'industry_name' => 'Digital Marketing',
                'description' => 'Rekomendasi bidang digital marketing: konten kreatif, media sosial, branding, dan promosi digital.',
                'primary_competency' => 'marketing',
                'secondary_competency' => 'administration',
            ],
            default => [
                'industry_name' => 'Pemrograman',
                'description' => 'Rekomendasi bidang pemrograman: pengembangan aplikasi, website, database, dan sistem informasi.',
                'primary_competency' => 'programming',
                'secondary_competency' => 'marketing',
            ],
        };
    }

    private function getCompetencyAliases(string $competencyKey): array
    {
        return match ($competencyKey) {
            'administrasi' => ['administrasi', 'administration'],
            'digital_marketing' => ['digital_marketing', 'digital marketing', 'marketing'],
            'pemrograman' => ['pemrograman', 'programming'],
            default => [$competencyKey],
        };
    }
}
