<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentBatch;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentQuestionOption;
use App\Models\AssessmentSubmission;
use App\Models\CompetencyCategory;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminController extends Controller
{
    public function loginForm()
    {
        if (session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function attemptLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            session([
                'admin_id' => $admin->id,
                'admin_name' => $admin->full_name,
            ]);
            return redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Username atau password salah!');
    }

    public function logout()
    {
        session()->forget(['admin_id', 'admin_name']);
        return redirect()->route('admin.login');
    }

    public function dashboard(Request $request)
    {
        $allBatches = AssessmentBatch::orderBy('id')->get();
        $comparisonBatches = $this->resolveDashboardComparisonBatches($allBatches);
        $comparisonBatchIds = $comparisonBatches->pluck('id')->all();

        $classes = StudentClass::whereIn('class_name', ['11 PPLG 1', '11 PPLG 2', '11 PPLG 3'])->orderBy('class_name')->get();

        $students = Student::with([
            'studentClass',
            'submissions' => function ($query) use ($comparisonBatchIds) {
                $query->whereIn('batch_id', $comparisonBatchIds)
                    ->with(['recommendation.industry', 'batch', 'answers.question.category'])
                    ->latest('submitted_at');
            },
        ])->orderBy('class_id')->orderBy('full_name')->get();

        foreach ($students as $student) {
            $student->submissions_by_batch = $student->submissions->keyBy('batch_id');
        }

        $totalStudents = $students->count();
        $competencyLabels = ['Administrasi', 'Digital Marketing', 'Pemrograman'];
        $batchStats = [];

        foreach ($comparisonBatches as $batch) {
            $submissions = $students
                ->map(function ($student) use ($batch) {
                    return $student->submissions_by_batch->get($batch->id);
                })
                ->filter();

            $submittedCount = $submissions->count();
            $pendingCount = max(0, $totalStudents - $submittedCount);
            $completionRate = $totalStudents > 0 ? round(($submittedCount / $totalStudents) * 100, 1) : 0;

            $avgRecommendationScore = round((float) $submissions
                ->filter(function ($submission) {
                    return $submission->recommendation !== null;
                })
                ->avg(function ($submission) {
                    return (float) $submission->recommendation->score;
                }), 1);

            $industryCounts = [];
            $competencyAccumulator = [];
            foreach ($competencyLabels as $label) {
                $competencyAccumulator[$label] = ['sum' => 0, 'count' => 0];
            }

            foreach ($submissions as $submission) {
                if ($submission->recommendation && $submission->recommendation->industry) {
                    $industryName = $submission->recommendation->industry->display_industry_name;
                    if (!isset($industryCounts[$industryName])) {
                        $industryCounts[$industryName] = 0;
                    }
                    $industryCounts[$industryName]++;
                }

                $categoryScores = $this->calculateCategoryScores($submission);
                foreach ($competencyLabels as $label) {
                    if (isset($categoryScores[$label])) {
                        $competencyAccumulator[$label]['sum'] += (float) $categoryScores[$label]['percentage'];
                        $competencyAccumulator[$label]['count']++;
                    }
                }
            }

            $competencyAverages = [];
            foreach ($competencyLabels as $label) {
                $sum = $competencyAccumulator[$label]['sum'];
                $count = $competencyAccumulator[$label]['count'];
                $competencyAverages[$label] = $count > 0 ? round($sum / $count, 1) : 0;
            }

            arsort($industryCounts);
            $topIndustry = array_key_first($industryCounts);

            $batchStats[$batch->id] = [
                'batch' => $batch,
                'submitted_count' => $submittedCount,
                'pending_count' => $pendingCount,
                'completion_rate' => $completionRate,
                'avg_recommendation_score' => $avgRecommendationScore,
                'industry_counts' => $industryCounts,
                'competency_averages' => $competencyAverages,
                'top_industry' => $topIndustry,
            ];
        }

        $totalBatchSlots = $totalStudents * max(1, $comparisonBatches->count());
        $totalSubmissions = collect($batchStats)->sum('submitted_count');
        $overallCompletionRate = $totalBatchSlots > 0 ? round(($totalSubmissions / $totalBatchSlots) * 100, 1) : 0;
        $overallAverageScore = round((float) collect($batchStats)
            ->avg('avg_recommendation_score'), 1);

        $completionChart = [
            'labels' => $comparisonBatches->pluck('batch_name')->values()->all(),
            'submitted' => $comparisonBatches->map(function ($batch) use ($batchStats) {
                return $batchStats[$batch->id]['submitted_count'] ?? 0;
            })->values()->all(),
            'pending' => $comparisonBatches->map(function ($batch) use ($batchStats) {
                return $batchStats[$batch->id]['pending_count'] ?? 0;
            })->values()->all(),
        ];

        $avgScoreChart = [
            'labels' => $comparisonBatches->pluck('batch_name')->values()->all(),
            'scores' => $comparisonBatches->map(function ($batch) use ($batchStats) {
                return $batchStats[$batch->id]['avg_recommendation_score'] ?? 0;
            })->values()->all(),
        ];

        $industryLabels = collect($batchStats)
            ->flatMap(function ($stat) {
                return array_keys($stat['industry_counts']);
            })
            ->unique()
            ->values()
            ->all();

        if (empty($industryLabels)) {
            $industryLabels = ['Administrasi', 'Digital Marketing', 'Pemrograman'];
        }

        $industryCompareChart = [
            'labels' => $industryLabels,
            'datasets' => $comparisonBatches->map(function ($batch) use ($batchStats, $industryLabels) {
                $counts = $batchStats[$batch->id]['industry_counts'] ?? [];

                return [
                    'label' => $batch->batch_name,
                    'data' => collect($industryLabels)->map(function ($label) use ($counts) {
                        return $counts[$label] ?? 0;
                    })->values()->all(),
                ];
            })->values()->all(),
        ];

        $competencyCompareChart = [
            'labels' => $competencyLabels,
            'datasets' => $comparisonBatches->map(function ($batch) use ($batchStats, $competencyLabels) {
                $averages = $batchStats[$batch->id]['competency_averages'] ?? [];

                return [
                    'label' => $batch->batch_name,
                    'data' => collect($competencyLabels)->map(function ($label) use ($averages) {
                        return $averages[$label] ?? 0;
                    })->values()->all(),
                ];
            })->values()->all(),
        ];

        $classStats = $classes->map(function ($class) use ($students, $comparisonBatches) {
            $classStudents = $students->where('class_id', $class->id);
            $classTotal = $classStudents->count();
            $perBatch = [];

            foreach ($comparisonBatches as $batch) {
                $submittedCount = $classStudents->filter(function ($student) use ($batch) {
                    return $student->submissions_by_batch->has($batch->id);
                })->count();

                $perBatch[$batch->id] = [
                    'submitted_count' => $submittedCount,
                    'pending_count' => max(0, $classTotal - $submittedCount),
                    'completion_rate' => $classTotal > 0 ? round(($submittedCount / $classTotal) * 100, 1) : 0,
                ];
            }

            return [
                'class' => $class,
                'total_students' => $classTotal,
                'per_batch' => $perBatch,
            ];
        })->values();

        return view('admin.dashboard', compact(
            'comparisonBatches',
            'students',
            'classes',
            'batchStats',
            'totalStudents',
            'totalSubmissions',
            'overallCompletionRate',
            'overallAverageScore',
            'completionChart',
            'avgScoreChart',
            'industryCompareChart',
            'competencyCompareChart',
            'classStats'
        ));
    }

    private function resolveDashboardComparisonBatches($allBatches)
    {
        $selected = collect();

        foreach (['batch 1', 'batch 2'] as $expectedName) {
            $batch = $allBatches->first(function ($item) use ($expectedName) {
                return strtolower(trim((string) $item->batch_name)) === $expectedName;
            });

            if ($batch) {
                $selected->push($batch);
            }
        }

        if ($selected->count() < 2) {
            foreach ($allBatches as $batch) {
                if ($selected->contains('id', $batch->id)) {
                    continue;
                }

                $selected->push($batch);

                if ($selected->count() === 2) {
                    break;
                }
            }
        }

        return $selected->sortBy('id')->values();
    }

    public function results(Request $request)
    {
        $batches = AssessmentBatch::orderByDesc('id')->get();
        $selectedBatchId = $this->resolveBatchId($request->integer('batch_id'));
        $selectedBatch = $batches->firstWhere('id', $selectedBatchId);
        [$batchOneId, $batchTwoId] = $this->resolveBatchOneAndTwoIdsFromCollection($batches);
        $isBatchTwoView = $batchTwoId && $selectedBatchId === $batchTwoId;

        $query = Student::with('studentClass')->orderBy('class_id')->orderBy('full_name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'selesai') {
                $query->whereHas('submissions', function ($submissionQuery) use ($selectedBatchId) {
                    $submissionQuery->where('batch_id', $selectedBatchId);
                });
            } else if ($request->status === 'belum') {
                $query->whereDoesntHave('submissions', function ($submissionQuery) use ($selectedBatchId) {
                    $submissionQuery->where('batch_id', $selectedBatchId);
                });
            }
        }

        $students = $query->with(['submissions' => function ($submissionQuery) use ($selectedBatchId) {
            $submissionQuery->where('batch_id', $selectedBatchId)
                ->with(['recommendation.industry', 'batch', 'answers.question.category'])
                ->latest('submitted_at');
        }])->get();

        $batchOneSubmissionsByStudent = collect();
        if ($isBatchTwoView && $batchOneId) {
            $batchOneSubmissionsByStudent = AssessmentSubmission::with(['recommendation.industry'])
                ->where('batch_id', $batchOneId)
                ->whereIn('student_id', $students->pluck('id')->all())
                ->get()
                ->keyBy('student_id');
        }

        foreach ($students as $student) {
            $student->selected_submission = $student->submissions->first();
            $student->category_scores = [];
            $student->dominant_primary_label = null;
            $student->dominant_secondary_label = null;
            $student->recommendation_primary_label = null;
            $student->recommendation_secondary_label = null;
            $student->batch_one_recommendation_field = null;
            $student->batch_two_rank = null;
            $student->batch_two_rank_total = null;

            if ($isBatchTwoView && $batchOneId) {
                $batchOneSubmission = $batchOneSubmissionsByStudent->get($student->id);
                $student->batch_one_recommendation_field = $this->resolveRecommendationFieldFromSubmission($batchOneSubmission);
            }

            if ($student->selected_submission) {
                $student->category_scores = $this->calculateCategoryScores($student->selected_submission);

                $sortedScores = collect($student->category_scores)
                    ->sortByDesc('percentage')
                    ->values();

                $student->dominant_primary_label = $sortedScores->get(0)['name'] ?? null;
                $student->dominant_secondary_label = $sortedScores->get(1)['name'] ?? $student->dominant_primary_label;

                $industry = optional(optional($student->selected_submission->recommendation)->industry);
                if ($industry) {
                    $student->recommendation_primary_label = $this->normalizeCompetencyLabel((string) $industry->primary_competency);
                    $student->recommendation_secondary_label = $this->normalizeCompetencyLabel((string) $industry->secondary_competency);

                    if ($student->recommendation_primary_label === '-') {
                        $student->recommendation_primary_label = $student->dominant_primary_label;
                    }

                    if ($student->recommendation_secondary_label === '-') {
                        $student->recommendation_secondary_label = $student->dominant_secondary_label;
                    }
                } else {
                    $student->recommendation_primary_label = $student->dominant_primary_label;
                    $student->recommendation_secondary_label = $student->dominant_secondary_label;
                }
            }
        }

        if ($isBatchTwoView) {
            $rankBuckets = $students
                ->filter(function ($student) {
                    return $student->selected_submission && !empty($student->batch_one_recommendation_field);
                })
                ->groupBy('batch_one_recommendation_field');

            foreach ($rankBuckets as $field => $bucketStudents) {
                $sorted = $bucketStudents
                    ->sort(function ($studentA, $studentB) {
                        $scoreA = (float) optional($studentA->selected_submission->recommendation)->score;
                        $scoreB = (float) optional($studentB->selected_submission->recommendation)->score;

                        if (abs($scoreA - $scoreB) < 0.00001) {
                            $timeA = optional($studentA->selected_submission->submitted_at)?->timestamp ?? PHP_INT_MAX;
                            $timeB = optional($studentB->selected_submission->submitted_at)?->timestamp ?? PHP_INT_MAX;

                            if ($timeA === $timeB) {
                                return strcmp((string) $studentA->full_name, (string) $studentB->full_name);
                            }

                            return $timeA <=> $timeB;
                        }

                        return $scoreA < $scoreB ? 1 : -1;
                    })
                    ->values();

                $totalInField = $sorted->count();
                $position = 0;
                $rank = 0;
                $previousScore = null;

                foreach ($sorted as $studentItem) {
                    $position++;
                    $score = (float) optional($studentItem->selected_submission->recommendation)->score;

                    if (is_null($previousScore) || abs($score - $previousScore) > 0.00001) {
                        $rank = $position;
                        $previousScore = $score;
                    }

                    $studentItem->batch_two_rank = $rank;
                    $studentItem->batch_two_rank_total = $totalInField;
                }
            }
        }

        $classes = StudentClass::whereIn('class_name', ['11 PPLG 1', '11 PPLG 2', '11 PPLG 3'])->orderBy('class_name')->get();

        return view('admin.results', compact('students', 'classes', 'batches', 'selectedBatch', 'selectedBatchId', 'isBatchTwoView'));
    }

    private function resolveBatchOneAndTwoIdsFromCollection($batches): array
    {
        $ordered = $batches->sortBy('id')->values();

        $batchOne = $ordered->first(function ($batch) {
            return strtolower(trim((string) $batch->batch_name)) === 'batch 1';
        }) ?? $ordered->first();

        $batchTwo = $ordered->first(function ($batch) {
            return strtolower(trim((string) $batch->batch_name)) === 'batch 2';
        });

        if (!$batchTwo) {
            $batchTwo = $ordered->first(function ($batch) use ($batchOne) {
                return $batchOne && $batch->id !== $batchOne->id;
            });
        }

        return [$batchOne?->id, $batchTwo?->id];
    }

    private function resolveRecommendationFieldFromSubmission(?AssessmentSubmission $submission): ?string
    {
        if (!$submission || !$submission->recommendation || !$submission->recommendation->industry) {
            return null;
        }

        $industry = $submission->recommendation->industry;
        $field = CompetencyCategory::normalizeDisplayName((string) $industry->display_industry_name);

        if (in_array($field, ['Administrasi', 'Digital Marketing', 'Pemrograman'], true)) {
            return $field;
        }

        $fallback = $this->normalizeCompetencyLabel((string) $industry->primary_competency);
        return $fallback !== '-' ? $fallback : null;
    }

    public function resetAssessment(Request $request, $student_id)
    {
        $batchId = $request->integer('batch_id') ?: $this->resolveBatchId(null);

        $submission = AssessmentSubmission::where('student_id', $student_id)
            ->where('batch_id', $batchId)
            ->first();

        if ($submission) {
            $submission->delete();
            return back()->with('success', 'Hasil asesmen siswa pada batch terpilih berhasil direset.');
        }

        return back()->with('error', 'Siswa ini belum mengerjakan asesmen pada batch terpilih.');
    }

    public function manageStudents(Request $request)
    {
        $query = Student::with('studentClass')->orderBy('class_id')->orderBy('full_name');
        
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        $students = $query->get();
        $classes = StudentClass::whereIn('class_name', ['11 PPLG 1', '11 PPLG 2', '11 PPLG 3'])->orderBy('class_name')->get();
        return view('admin.students', compact('students', 'classes'));
    }

    public function deleteStudent($id)
    {
        Student::findOrFail($id)->delete();
        return back()->with('success', 'Siswa berhasil dihapus!');
    }

    public function downloadTemplate()
    {
        if (!$this->isZipArchiveAvailable()) {
            return back()->with('error', 'Ekstensi ZIP pada PHP belum aktif. Semua import/export menggunakan format .xlsx, jadi aktifkan extension=zip lalu restart web server.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Nama Kelas (misal: 11 PPLG 1)');
        $sheet->setCellValue('B1', 'Nama Lengkap Siswa');

        $sheet->setCellValue('A2', '11 PPLG 1');
        $sheet->setCellValue('B2', 'Budi Santoso');

        $this->streamSpreadsheetDownload($spreadsheet, 'Template_Import_Siswa');
    }

    public function importStudents(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx'
        ]);

        try {
            if (!$this->isZipArchiveAvailable()) {
                return back()->with('error', 'Ekstensi ZIP pada PHP belum aktif. Import file .xlsx memerlukan extension=zip.');
            }

            $file = $request->file('excel_file');

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Skip header
            unset($rows[0]);

            $count = 0;
            foreach ($rows as $row) {
                if (empty($row[0]) || empty($row[1])) continue;

                $className = trim($row[0]);
                $studentName = trim($row[1]);
                
                if (!in_array($className, ['11 PPLG 1', '11 PPLG 2', '11 PPLG 3'])) {
                    continue;
                }

                $studentClass = StudentClass::firstOrCreate(['class_name' => $className]);

                Student::updateOrCreate(
                    ['class_id' => $studentClass->id, 'full_name' => $studentName]
                );
                $count++;
            }

            return back()->with('success', "$count data siswa berhasil diimport!");
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat import data: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        if (!$this->isZipArchiveAvailable()) {
            return back()->with('error', 'Ekstensi ZIP pada PHP belum aktif. Semua import/export menggunakan format .xlsx, jadi aktifkan extension=zip lalu restart web server.');
        }

        $batches = AssessmentBatch::orderByDesc('id')->get();
        $selectedBatchId = $this->resolveBatchId($request->integer('batch_id'));
        $selectedBatch = $batches->firstWhere('id', $selectedBatchId);

        $categories = CompetencyCategory::orderBy('id')->get();

        $studentsQuery = Student::with('studentClass')->orderBy('class_id')->orderBy('full_name');

        if ($request->filled('class_id')) {
            $studentsQuery->where('class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'selesai') {
                $studentsQuery->whereHas('submissions', function ($submissionQuery) use ($selectedBatchId) {
                    $submissionQuery->where('batch_id', $selectedBatchId);
                });
            } else if ($request->status === 'belum') {
                $studentsQuery->whereDoesntHave('submissions', function ($submissionQuery) use ($selectedBatchId) {
                    $submissionQuery->where('batch_id', $selectedBatchId);
                });
            }
        }

        $students = $studentsQuery->with(['submissions' => function ($submissionQuery) use ($selectedBatchId) {
            $submissionQuery->where('batch_id', $selectedBatchId)
                ->with(['batch', 'recommendation.industry', 'answers.question.category'])
                ->latest('submitted_at');
        }])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Batch');
        $sheet->setCellValue('C1', 'Nama Lengkap');
        $sheet->setCellValue('D1', 'Kelas');

        $columnIndex = 5;
        foreach ($categories as $category) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . '1', 'Skor ' . $category->display_name);
            $columnIndex++;
        }

        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . '1', 'Rekomendasi Industri');
        $columnIndex++;
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . '1', 'Skor Rekomendasi');
        $columnIndex++;
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . '1', 'Tanggal Pengerjaan');

        $rowNum = 2;
        $no = 1;

        foreach ($students as $student) {
            $submission = $student->submissions->first();
            $categoryScores = $submission ? $this->calculateCategoryScores($submission) : [];

            $batchName = $submission && $submission->batch ? $submission->batch->batch_name : ($selectedBatch ? $selectedBatch->batch_name : '-');

            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", $batchName);
            $sheet->setCellValue("C{$rowNum}", $student->full_name);
            $sheet->setCellValue("D{$rowNum}", $student->studentClass->class_name);

            $columnIndex = 5;
            foreach ($categories as $category) {
                $categoryName = $category->display_name;
                $value = isset($categoryScores[$categoryName])
                    ? number_format($categoryScores[$categoryName]['percentage'], 1) . '%'
                    : '-';

                $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . $rowNum, $submission ? $value : '-');
                $columnIndex++;
            }

            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($columnIndex) . $rowNum,
                $submission && $submission->recommendation && $submission->recommendation->industry
                    ? $submission->recommendation->industry->display_industry_name
                    : '-'
            );
            $columnIndex++;

            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($columnIndex) . $rowNum,
                $submission && $submission->recommendation
                    ? number_format($submission->recommendation->score, 1) . '%'
                    : '-'
            );
            $columnIndex++;

            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($columnIndex) . $rowNum,
                $submission && $submission->submitted_at
                    ? $submission->submitted_at->format('d/m/Y H:i')
                    : '-'
            );

            $rowNum++;
        }

        $this->streamSpreadsheetDownload($spreadsheet, 'Hasil_Assessment_PKL_' . date('Ymd_His'));
    }

    public function manageQuestions(Request $request)
    {
        $batches = AssessmentBatch::orderByDesc('id')->get();
        $selectedBatchId = $this->resolveBatchId($request->integer('batch_id'));
        $categories = CompetencyCategory::orderBy('category_name')->get();
        $selectedCategoryId = $request->filled('category_id') ? $request->integer('category_id') : null;

        $questionsQuery = AssessmentQuestion::with([
            'batch',
            'category',
            'options' => function ($query) {
                $query->orderBy('option_order')->orderBy('id');
            },
        ])->orderBy('category_id')->orderBy('question_order')->orderBy('id');

        if ($selectedBatchId) {
            $questionsQuery->where('batch_id', $selectedBatchId);
        }

        if ($selectedCategoryId) {
            $questionsQuery->where('category_id', $selectedCategoryId);
        }

        $questions = $questionsQuery->get();
        $selectedBatch = $batches->firstWhere('id', $selectedBatchId);

        return view('admin.questions.index', compact(
            'batches',
            'selectedBatchId',
            'selectedBatch',
            'categories',
            'selectedCategoryId',
            'questions'
        ));
    }

    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'batch_name' => 'required|string|max:100|unique:assessment_batches,batch_name',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        DB::transaction(function () use ($validated, $request) {
            if ($request->boolean('is_active')) {
                AssessmentBatch::query()->update(['is_active' => false]);
            }

            AssessmentBatch::create([
                'batch_name' => $validated['batch_name'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return back()->with('success', 'Batch asesmen berhasil ditambahkan.');
    }

    public function updateBatch(Request $request, AssessmentBatch $batch)
    {
        $validated = $request->validate([
            'batch_name' => 'required|string|max:100|unique:assessment_batches,batch_name,' . $batch->id,
            'description' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        DB::transaction(function () use ($request, $validated, $batch) {
            $setActive = $request->boolean('is_active');

            if ($setActive) {
                AssessmentBatch::query()->update(['is_active' => false]);
            }

            $batch->update([
                'batch_name' => $validated['batch_name'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]);

            if ($setActive) {
                // Use direct query to avoid stale model state after mass update above.
                AssessmentBatch::whereKey($batch->id)->update(['is_active' => true]);
            }
        });

        return back()->with('success', 'Batch asesmen berhasil diperbarui.');
    }

    public function activateBatch(AssessmentBatch $batch)
    {
        DB::transaction(function () use ($batch) {
            AssessmentBatch::query()->update(['is_active' => false]);
            AssessmentBatch::whereKey($batch->id)->update(['is_active' => true]);
        });

        return back()->with('success', 'Batch ' . $batch->batch_name . ' sekarang aktif untuk asesmen siswa.');
    }

    public function storeQuestion(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:assessment_batches,id',
            'category_id' => 'required|exists:competency_categories,id',
            'question_text' => 'required|string|max:500',
            'question_order' => 'nullable|integer|min:1',
            'option_text' => 'required|array|min:2',
            'option_score' => 'required|array|min:2',
            'option_order' => 'nullable|array',
            'option_active' => 'nullable|array',
        ]);

        $optionPayloads = $this->buildOptionPayloads($request);
        if (count($optionPayloads) < 2) {
            return back()->withInput()->with('error', 'Minimal 2 opsi jawaban harus diisi untuk setiap soal.');
        }

        DB::transaction(function () use ($validated, $request, $optionPayloads) {
            $question = AssessmentQuestion::create([
                'batch_id' => $validated['batch_id'],
                'category_id' => $validated['category_id'],
                'question_text' => $validated['question_text'],
                'question_order' => $validated['question_order'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            foreach ($optionPayloads as $optionPayload) {
                $question->options()->create($optionPayload);
            }
        });

        return redirect()->route('admin.questions', ['batch_id' => $validated['batch_id']])
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function editQuestion(AssessmentQuestion $question)
    {
        $question->load(['batch', 'category', 'options' => function ($query) {
            $query->orderBy('option_order')->orderBy('id');
        }]);

        $batches = AssessmentBatch::orderByDesc('id')->get();
        $categories = CompetencyCategory::orderBy('category_name')->get();

        return view('admin.questions.edit', compact('question', 'batches', 'categories'));
    }

    public function updateQuestion(Request $request, AssessmentQuestion $question)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:assessment_batches,id',
            'category_id' => 'required|exists:competency_categories,id',
            'question_text' => 'required|string|max:500',
            'question_order' => 'nullable|integer|min:1',
            'option_id' => 'nullable|array',
            'option_text' => 'required|array|min:2',
            'option_score' => 'required|array|min:2',
            'option_order' => 'nullable|array',
            'option_active' => 'nullable|array',
        ]);

        $optionPayloads = $this->buildOptionPayloads($request, true);
        if (count($optionPayloads) < 2) {
            return back()->withInput()->with('error', 'Minimal 2 opsi jawaban harus diisi untuk setiap soal.');
        }

        DB::transaction(function () use ($question, $validated, $request, $optionPayloads) {
            $question->update([
                'batch_id' => $validated['batch_id'],
                'category_id' => $validated['category_id'],
                'question_text' => $validated['question_text'],
                'question_order' => $validated['question_order'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $keptOptionIds = [];
            foreach ($optionPayloads as $optionPayload) {
                $optionId = $optionPayload['id'] ?? null;
                unset($optionPayload['id']);

                if ($optionId) {
                    $option = $question->options()->where('id', $optionId)->first();
                    if ($option) {
                        $option->update($optionPayload);
                        $keptOptionIds[] = $option->id;
                        continue;
                    }
                }

                $newOption = $question->options()->create($optionPayload);
                $keptOptionIds[] = $newOption->id;
            }

            if (!empty($keptOptionIds)) {
                $question->options()->whereNotIn('id', $keptOptionIds)->delete();
            }
        });

        return redirect()->route('admin.questions', ['batch_id' => $validated['batch_id']])
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function deleteQuestion(AssessmentQuestion $question)
    {
        $batchId = $question->batch_id;
        $alreadyAnswered = AssessmentAnswer::where('question_id', $question->id)->exists();

        if ($alreadyAnswered) {
            $question->update(['is_active' => false]);

            return redirect()->route('admin.questions', ['batch_id' => $batchId])
                ->with('success', 'Soal sudah pernah dikerjakan siswa, sehingga hanya dinonaktifkan untuk menjaga histori data.');
        }

        $question->delete();

        return redirect()->route('admin.questions', ['batch_id' => $batchId])
            ->with('success', 'Soal berhasil dihapus.');
    }

    public function downloadQuestionTemplate()
    {
        if (!$this->isZipArchiveAvailable()) {
            return back()->with('error', 'Ekstensi ZIP pada PHP belum aktif. Semua import/export menggunakan format .xlsx, jadi aktifkan extension=zip lalu restart web server.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $batch2 = AssessmentBatch::where('batch_name', 'Batch 2')->first();
        $sampleQuestions = collect();
        $optionSlots = 2;

        if ($batch2) {
            $sampleQuestions = AssessmentQuestion::with([
                'batch',
                'category',
                'options' => function ($query) {
                    $query->orderBy('option_order')->orderBy('id');
                },
            ])->where('batch_id', $batch2->id)->orderBy('id')->limit(5)->get();

            $optionSlots = max(2, (int) $sampleQuestions->max(function ($question) {
                return $question->options->count();
            }));
        }

        $this->writeQuestionExcelHeader($sheet, $optionSlots);

        $row = 2;
        if ($sampleQuestions->isNotEmpty()) {
            foreach ($sampleQuestions as $question) {
                $sheet->setCellValue("A{$row}", optional($question->batch)->batch_name ?? 'Batch 2');
                $sheet->setCellValue("B{$row}", optional($question->category)->display_name ?? 'Administrasi');
                $sheet->setCellValue("C{$row}", $question->question_order ?: '');
                $sheet->setCellValue("D{$row}", $question->question_text);
                $sheet->setCellValue("E{$row}", $question->is_active ? 1 : 0);

                $options = $question->options->sortBy('option_order')->values();
                foreach ($options as $index => $option) {
                    $baseColumn = 6 + ($index * 3);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($baseColumn) . $row, $option->option_text);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($baseColumn + 1) . $row, $option->option_score);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($baseColumn + 2) . $row, $option->is_active ? 1 : 0);
                }

                $row++;
            }
        } else {
            $sheet->setCellValue('A2', 'Batch 2');
            $sheet->setCellValue('B2', 'Pemrograman');
            $sheet->setCellValue('C2', '');
            $sheet->setCellValue('D2', 'Contoh teks soal sesuai format Batch 2');
            $sheet->setCellValue('E2', '1');
            $sheet->setCellValue('F2', 'Ya');
            $sheet->setCellValue('G2', '1');
            $sheet->setCellValue('H2', '1');
            $sheet->setCellValue('I2', 'Tidak');
            $sheet->setCellValue('J2', '0');
            $sheet->setCellValue('K2', '1');
        }

        $referenceSheet = $spreadsheet->createSheet();
        $referenceSheet->setTitle('Referensi');
        $referenceSheet->setCellValue('A1', 'Kategori Kompetensi yang valid');
        $referenceSheet->setCellValue('A2', 'Administrasi');
        $referenceSheet->setCellValue('A3', 'Digital Marketing');
        $referenceSheet->setCellValue('A4', 'Pemrograman');
        $referenceSheet->setCellValue('C1', 'Catatan');
        $referenceSheet->setCellValue('C2', 'Urutan Soal boleh kosong (opsional)');
        $referenceSheet->setCellValue('C3', 'Status Soal/Opsi gunakan 1 (aktif) atau 0 (nonaktif)');
        $referenceSheet->setCellValue('C4', 'Satu soal = satu baris; isi Opsi 1, Opsi 2, dst dalam kolom yang tersedia');

        $this->streamSpreadsheetDownload($spreadsheet, 'Template_Import_Soal');
    }

    public function exportQuestions(Request $request)
    {
        if (!$this->isZipArchiveAvailable()) {
            return back()->with('error', 'Ekstensi ZIP pada PHP belum aktif. Semua import/export menggunakan format .xlsx, jadi aktifkan extension=zip lalu restart web server.');
        }

        $selectedBatchId = $request->integer('batch_id');

        $questionsQuery = AssessmentQuestion::with([
            'batch',
            'category',
            'options' => function ($query) {
                $query->orderBy('option_order')->orderBy('id');
            },
        ])->orderBy('batch_id')->orderBy('category_id')->orderBy('question_order')->orderBy('id');

        if ($selectedBatchId) {
            $questionsQuery->where('batch_id', $selectedBatchId);
        }

        $questions = $questionsQuery->get();

        $optionSlots = max(2, (int) $questions->max(function ($question) {
            return $question->options->count();
        }));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->writeQuestionExcelHeader($sheet, $optionSlots);

        $row = 2;
        foreach ($questions as $question) {
            $sheet->setCellValue("A{$row}", optional($question->batch)->batch_name);
            $sheet->setCellValue("B{$row}", optional($question->category)->display_name);
            $sheet->setCellValue("C{$row}", $question->question_order ?: '');
            $sheet->setCellValue("D{$row}", $question->question_text);
            $sheet->setCellValue("E{$row}", $question->is_active ? 1 : 0);

            $options = $question->options->sortBy('option_order')->values();
            foreach ($options as $index => $option) {
                $baseColumn = 6 + ($index * 3);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($baseColumn) . $row, $option->option_text);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($baseColumn + 1) . $row, $option->option_score);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($baseColumn + 2) . $row, $option->is_active ? 1 : 0);
            }

            $row++;
        }

        $this->streamSpreadsheetDownload($spreadsheet, 'Bank_Soal_Assessment_' . date('Ymd_His'));
    }

    public function importQuestions(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx',
        ]);

        try {
            if (!$this->isZipArchiveAvailable()) {
                return back()->with('error', 'Ekstensi ZIP pada PHP belum aktif. Import file .xlsx memerlukan extension=zip.');
            }

            $file = $request->file('excel_file');

            $spreadsheet = IOFactory::load($file->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray();

            if (count($rows) <= 1) {
                return back()->with('error', 'File Excel tidak memiliki data untuk diimpor.');
            }

            $headerRow = array_map(function ($value) {
                return $this->normalizeHeaderValue($value);
            }, $rows[0]);

            $legacyColumnMap = $this->resolveLegacyQuestionImportColumnMap($headerRow);
            $baseColumnMap = $this->resolveQuestionImportColumnMap($headerRow);
            $optionColumns = $this->resolveQuestionOptionColumns($headerRow);

            unset($rows[0]);

            $processedRows = 0;
            $skippedRows = 0;

            if ($this->isLegacyQuestionExcelHeader($headerRow) && !empty($legacyColumnMap)) {
                DB::transaction(function () use ($rows, $legacyColumnMap, &$processedRows, &$skippedRows) {
                    foreach ($rows as $row) {
                        $batchName = trim((string) ($row[$legacyColumnMap['batch']] ?? ''));
                        $categoryName = trim((string) ($row[$legacyColumnMap['category']] ?? ''));
                        $questionOrderValue = $row[$legacyColumnMap['question_order']] ?? null;
                        $questionText = trim((string) ($row[$legacyColumnMap['question_text']] ?? ''));
                        $questionActiveValue = $row[$legacyColumnMap['question_active']] ?? null;
                        $optionOrderValue = $row[$legacyColumnMap['option_order']] ?? null;
                        $optionText = trim((string) ($row[$legacyColumnMap['option_text']] ?? ''));
                        $optionScoreValue = $row[$legacyColumnMap['option_score']] ?? null;
                        $optionActiveValue = $row[$legacyColumnMap['option_active']] ?? null;

                        if ($batchName === '' || $categoryName === '' || $questionText === '' || $optionText === '') {
                            $skippedRows++;
                            continue;
                        }

                        $batch = AssessmentBatch::firstOrCreate(
                            ['batch_name' => $batchName],
                            [
                                'description' => 'Batch hasil import soal.',
                                'is_active' => false,
                            ]
                        );

                        $category = $this->resolveCategoryByLabel($categoryName);
                        if (!$category) {
                            $skippedRows++;
                            continue;
                        }

                        $questionOrder = is_numeric($questionOrderValue) ? (int) $questionOrderValue : null;
                        $questionIsActive = $this->parseExcelBoolean($questionActiveValue, true);

                        $question = AssessmentQuestion::updateOrCreate(
                            [
                                'batch_id' => $batch->id,
                                'category_id' => $category->id,
                                'question_text' => $questionText,
                            ],
                            [
                                'question_order' => $questionOrder,
                                'is_active' => $questionIsActive,
                            ]
                        );

                        $optionOrder = is_numeric($optionOrderValue) ? (int) $optionOrderValue : null;
                        $optionScore = is_numeric($optionScoreValue) ? (float) $optionScoreValue : 0;
                        $optionIsActive = $this->parseExcelBoolean($optionActiveValue, true);

                        $matchAttributes = ['question_id' => $question->id];
                        if (!is_null($optionOrder)) {
                            $matchAttributes['option_order'] = $optionOrder;
                        } else {
                            $matchAttributes['option_text'] = $optionText;
                        }

                        AssessmentQuestionOption::updateOrCreate(
                            $matchAttributes,
                            [
                                'option_text' => $optionText,
                                'option_score' => $optionScore,
                                'option_order' => $optionOrder,
                                'is_active' => $optionIsActive,
                            ]
                        );

                        $processedRows++;
                    }
                });
            } else {
                if (empty($optionColumns)) {
                    return back()->with('error', 'Format kolom opsi tidak dikenali. Silakan gunakan Template Import terbaru (1 soal = 1 baris).');
                }

                DB::transaction(function () use ($rows, $baseColumnMap, $optionColumns, &$processedRows, &$skippedRows) {
                    foreach ($rows as $row) {
                        $batchName = trim((string) ($row[$baseColumnMap['batch']] ?? ''));
                        $categoryName = trim((string) ($row[$baseColumnMap['category']] ?? ''));
                        $questionOrderValue = $row[$baseColumnMap['question_order']] ?? null;
                        $questionText = trim((string) ($row[$baseColumnMap['question_text']] ?? ''));
                        $questionActiveValue = $row[$baseColumnMap['question_active']] ?? null;

                        if ($batchName === '' || $categoryName === '' || $questionText === '') {
                            $skippedRows++;
                            continue;
                        }

                        $hasOption = false;
                        foreach ($optionColumns as $optionColumn) {
                            $optionText = trim((string) ($row[$optionColumn['text']] ?? ''));
                            if ($optionText !== '') {
                                $hasOption = true;
                                break;
                            }
                        }

                        if (!$hasOption) {
                            $skippedRows++;
                            continue;
                        }

                        $batch = AssessmentBatch::firstOrCreate(
                            ['batch_name' => $batchName],
                            [
                                'description' => 'Batch hasil import soal.',
                                'is_active' => false,
                            ]
                        );

                        $category = $this->resolveCategoryByLabel($categoryName);
                        if (!$category) {
                            $skippedRows++;
                            continue;
                        }

                        $questionOrder = is_numeric($questionOrderValue) ? (int) $questionOrderValue : null;
                        $questionIsActive = $this->parseExcelBoolean($questionActiveValue, true);

                        $question = AssessmentQuestion::updateOrCreate(
                            [
                                'batch_id' => $batch->id,
                                'category_id' => $category->id,
                                'question_text' => $questionText,
                            ],
                            [
                                'question_order' => $questionOrder,
                                'is_active' => $questionIsActive,
                            ]
                        );

                        $keptOptionOrders = [];
                        foreach ($optionColumns as $slot => $optionColumn) {
                            $optionText = trim((string) ($row[$optionColumn['text']] ?? ''));
                            if ($optionText === '') {
                                continue;
                            }

                            $optionScoreValue = isset($optionColumn['score']) ? ($row[$optionColumn['score']] ?? null) : null;
                            $optionActiveValue = isset($optionColumn['active']) ? ($row[$optionColumn['active']] ?? null) : null;
                            $optionScore = is_numeric($optionScoreValue) ? (float) $optionScoreValue : 0;
                            $optionIsActive = $this->parseExcelBoolean($optionActiveValue, true);

                            AssessmentQuestionOption::updateOrCreate(
                                [
                                    'question_id' => $question->id,
                                    'option_order' => (int) $slot,
                                ],
                                [
                                    'option_text' => $optionText,
                                    'option_score' => $optionScore,
                                    'option_order' => (int) $slot,
                                    'is_active' => $optionIsActive,
                                ]
                            );

                            $keptOptionOrders[] = (int) $slot;
                        }

                        if (!empty($keptOptionOrders)) {
                            $question->options()->whereNotIn('option_order', $keptOptionOrders)->delete();
                        }

                        $processedRows++;
                    }
                });
            }

            return back()->with('success', "Import soal selesai. Baris diproses: {$processedRows}, baris dilewati: {$skippedRows}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat import soal: ' . $e->getMessage());
        }
    }

    private function resolveBatchId(?int $requestedBatchId = null): ?int
    {
        if ($requestedBatchId && AssessmentBatch::whereKey($requestedBatchId)->exists()) {
            return $requestedBatchId;
        }

        $activeBatch = AssessmentBatch::where('is_active', true)->orderByDesc('id')->first();
        if ($activeBatch) {
            return $activeBatch->id;
        }

        return AssessmentBatch::orderByDesc('id')->value('id');
    }

    private function streamSpreadsheetDownload(Spreadsheet $spreadsheet, string $baseFileName): void
    {
        $writer = new Xlsx($spreadsheet);
        $fileName = $baseFileName . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
        exit;
    }

    private function isZipArchiveAvailable(): bool
    {
        return class_exists(\ZipArchive::class);
    }

    private function calculateCategoryScores(AssessmentSubmission $submission): array
    {
        $scores = [];

        foreach ($submission->answers as $answer) {
            if (!$answer->question || !$answer->question->category) {
                continue;
            }

            $categoryName = CompetencyCategory::normalizeDisplayName($answer->question->category->category_name);
            if (!isset($scores[$categoryName])) {
                $scores[$categoryName] = [
                    'name' => $categoryName,
                    'obtained' => 0,
                    'max' => 0,
                    'percentage' => 0,
                ];
            }

            $scores[$categoryName]['obtained'] += (float) $answer->score_value;
            $maxScore = (float) $answer->max_score_value;
            $scores[$categoryName]['max'] += $maxScore > 0 ? $maxScore : 1;
        }

        foreach ($scores as &$score) {
            $score['percentage'] = $score['max'] > 0
                ? ($score['obtained'] / $score['max']) * 100
                : 0;
        }

        return $scores;
    }

    private function writeQuestionExcelHeader($sheet, int $optionSlots = 2): void
    {
        $sheet->setCellValue('A1', 'Batch');
        $sheet->setCellValue('B1', 'Kategori Kompetensi');
        $sheet->setCellValue('C1', 'Urutan Soal (Opsional)');
        $sheet->setCellValue('D1', 'Teks Soal');
        $sheet->setCellValue('E1', 'Status Soal (1 Aktif / 0 Nonaktif)');

        for ($slot = 1; $slot <= $optionSlots; $slot++) {
            $baseColumn = 6 + (($slot - 1) * 3);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($baseColumn) . '1', "Opsi {$slot} Teks");
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($baseColumn + 1) . '1', "Opsi {$slot} Skor");
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($baseColumn + 2) . '1', "Opsi {$slot} Status (1 Aktif / 0 Nonaktif)");
        }
    }

    private function resolveQuestionImportColumnMap(array $headerRow): array
    {
        return [
            'batch' => $this->resolveColumnIndexByAliases($headerRow, ['batch', 'batch name', 'nama batch'], 0),
            'category' => $this->resolveColumnIndexByAliases($headerRow, ['kategori kompetensi', 'kategori', 'category', 'category name'], 1),
            'question_order' => $this->resolveColumnIndexByAliases($headerRow, ['urutan soal (opsional)', 'urutan soal', 'question order', 'question_order'], 2),
            'question_text' => $this->resolveColumnIndexByAliases($headerRow, ['teks soal', 'soal', 'question text', 'question_text'], 3),
            'question_active' => $this->resolveColumnIndexByAliases($headerRow, ['status soal (1 aktif / 0 nonaktif)', 'soal aktif (1/0)', 'status soal', 'question active'], 4),
        ];
    }

    private function resolveLegacyQuestionImportColumnMap(array $headerRow): array
    {
        if (!$this->isLegacyQuestionExcelHeader($headerRow)) {
            return [];
        }

        return [
            'batch' => $this->resolveColumnIndexByAliases($headerRow, ['batch', 'batch name', 'nama batch'], 0),
            'category' => $this->resolveColumnIndexByAliases($headerRow, ['kategori kompetensi', 'kategori', 'category', 'category name'], 1),
            'question_order' => $this->resolveColumnIndexByAliases($headerRow, ['urutan soal (opsional)', 'urutan soal', 'question order', 'question_order'], 2),
            'question_text' => $this->resolveColumnIndexByAliases($headerRow, ['teks soal', 'soal', 'question text', 'question_text'], 3),
            'question_active' => $this->resolveColumnIndexByAliases($headerRow, ['status soal (1 aktif / 0 nonaktif)', 'soal aktif (1/0)', 'status soal', 'question active'], 4),
            'option_order' => $this->resolveColumnIndexByAliases($headerRow, ['urutan opsi', 'option order', 'option_order'], 5),
            'option_text' => $this->resolveColumnIndexByAliases($headerRow, ['teks opsi', 'opsi', 'jawaban', 'option text', 'option_text'], 6),
            'option_score' => $this->resolveColumnIndexByAliases($headerRow, ['skor opsi', 'score', 'option score', 'option_score'], 7),
            'option_active' => $this->resolveColumnIndexByAliases($headerRow, ['status opsi (1 aktif / 0 nonaktif)', 'opsi aktif (1/0)', 'status opsi', 'option active'], 8),
        ];
    }

    private function resolveQuestionOptionColumns(array $headerRow): array
    {
        $columns = [];

        foreach ($headerRow as $index => $header) {
            if ($header === '') {
                continue;
            }

            if (preg_match('/^opsi\s*(\d+)\s*teks$/', $header, $matches)) {
                $columns[(int) $matches[1]]['text'] = $index;
                continue;
            }

            if (preg_match('/^opsi\s*(\d+)\s*skor$/', $header, $matches)) {
                $columns[(int) $matches[1]]['score'] = $index;
                continue;
            }

            if (preg_match('/^opsi\s*(\d+)\s*status/', $header, $matches)) {
                $columns[(int) $matches[1]]['active'] = $index;
            }
        }

        ksort($columns);

        return array_filter($columns, function ($column) {
            return isset($column['text']);
        });
    }

    private function isLegacyQuestionExcelHeader(array $headerRow): bool
    {
        return in_array('urutan opsi', $headerRow, true)
            && in_array('teks opsi', $headerRow, true)
            && in_array('skor opsi', $headerRow, true);
    }

    private function resolveColumnIndexByAliases(array $headerRow, array $aliases, int $fallback): int
    {
        foreach ($aliases as $alias) {
            $normalizedAlias = $this->normalizeHeaderValue($alias);
            $index = array_search($normalizedAlias, $headerRow, true);
            if ($index !== false) {
                return (int) $index;
            }
        }

        return $fallback;
    }

    private function normalizeHeaderValue(mixed $value): string
    {
        $normalized = strtolower(trim((string) $value));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized ?? '';
    }

    private function parseExcelBoolean(mixed $value, bool $default = true): bool
    {
        if (is_null($value) || trim((string) $value) === '') {
            return $default;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'ya', 'yes', 'aktif', 'active'], true);
    }

    private function normalizeCompetencyLabel(string $value): string
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return '-';
        }

        if (str_contains($normalized, 'administrasi') || str_contains($normalized, 'administration')) {
            return 'Administrasi';
        }

        if (str_contains($normalized, 'digital marketing') || str_contains($normalized, 'marketing')) {
            return 'Digital Marketing';
        }

        if (str_contains($normalized, 'pemrograman') || str_contains($normalized, 'programming')) {
            return 'Pemrograman';
        }

        return '-';
    }

    private function resolveCategoryByLabel(string $categoryName): ?CompetencyCategory
    {
        $exactCategory = CompetencyCategory::where('category_name', $categoryName)->first();
        if ($exactCategory) {
            return $exactCategory;
        }

        $normalizedInput = CompetencyCategory::normalizeDisplayName($categoryName);

        return CompetencyCategory::all()->first(function (CompetencyCategory $category) use ($normalizedInput) {
            return $category->display_name === $normalizedInput;
        });
    }

    private function buildOptionPayloads(Request $request, bool $includeIds = false): array
    {
        $optionTexts = $request->input('option_text', []);
        $optionScores = $request->input('option_score', []);
        $optionOrders = $request->input('option_order', []);
        $optionActives = $request->input('option_active', []);
        $optionIds = $request->input('option_id', []);

        $payloads = [];
        foreach ($optionTexts as $index => $optionText) {
            $cleanText = trim((string) $optionText);
            if ($cleanText === '') {
                continue;
            }

            $scoreValue = isset($optionScores[$index]) && is_numeric($optionScores[$index])
                ? (float) $optionScores[$index]
                : 0;

            $orderValue = isset($optionOrders[$index]) && $optionOrders[$index] !== '' && is_numeric($optionOrders[$index])
                ? (int) $optionOrders[$index]
                : $index + 1;

            $payload = [
                'option_text' => $cleanText,
                'option_score' => $scoreValue,
                'option_order' => $orderValue,
                'is_active' => isset($optionActives[$index]) && (string) $optionActives[$index] === '1',
            ];

            if ($includeIds && isset($optionIds[$index]) && is_numeric($optionIds[$index])) {
                $payload['id'] = (int) $optionIds[$index];
            }

            $payloads[] = $payload;
        }

        return $payloads;
    }
}
