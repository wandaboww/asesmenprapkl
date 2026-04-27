<?php

namespace App\Http\Controllers;

use App\Models\BatchTwoCtQuestion;
use App\Models\BatchTwoCtStudentResult;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentBatchTwoCtController extends Controller
{
    private const CT_TYPES = [
        '-',
        'Decomposition',
        'Pattern Recognition',
        'Abstraction',
        'Algorithmic Thinking',
    ];

    public function assessment(Request $request)
    {
        $studentId = (int) session('student_id');
        if ($studentId <= 0) {
            return redirect()->route('login')->with('error', 'Sesi siswa tidak valid. Silakan login ulang.');
        }

        $selectedJenisCt = $this->canonicalCt((string) $request->query('jenis_ct'));
        $randomize = !$request->has('random') || $request->boolean('random', true);

        $questionsQuery = BatchTwoCtQuestion::with(['options' => function ($query) {
            $query->where('is_active', true)->orderBy('label_opsi');
        }])->where('is_active', true);

        if ($selectedJenisCt) {
            $questionsQuery->where('jenis_ct', $selectedJenisCt);
        }

        if ($randomize) {
            $questionsQuery->inRandomOrder();
        } else {
            $questionsQuery->orderBy('id');
        }

        $questions = $questionsQuery->get()->filter(function (BatchTwoCtQuestion $question) {
            return $question->options->isNotEmpty();
        })->values();

        if ($questions->isEmpty()) {
            return redirect()->route('student.dashboard')->with('error', 'Belum ada soal Batch 2 CT yang aktif.');
        }

        $latestResult = BatchTwoCtStudentResult::where('siswa_id', $studentId)->latest('attempt_no')->first();
        $nextAttempt = ((int) optional($latestResult)->attempt_no) + 1;

        return view('student.batch2ct.assessment', [
            'questions' => $questions,
            'ctTypes' => self::CT_TYPES,
            'selectedJenisCt' => $selectedJenisCt,
            'randomize' => $randomize,
            'nextAttempt' => max(1, $nextAttempt),
            'latestResult' => $latestResult,
        ]);
    }

    public function submit(Request $request)
    {
        $studentId = (int) session('student_id');
        if ($studentId <= 0) {
            return redirect()->route('login')->with('error', 'Sesi siswa tidak valid. Silakan login ulang.');
        }

        $questionIds = collect($request->input('question_ids', []))
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values();

        if ($questionIds->isEmpty()) {
            return back()->with('error', 'Daftar soal tidak valid. Silakan ulangi asesmen.')->withInput();
        }

        $questions = BatchTwoCtQuestion::with(['options' => function ($query) {
            $query->where('is_active', true)->orderBy('label_opsi');
        }])
            ->where('is_active', true)
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        if ($questions->count() !== $questionIds->count()) {
            return back()->with('error', 'Sebagian soal sudah tidak aktif. Silakan mulai ulang asesmen.');
        }

        $totalWeb = 0;
        $totalMarketing = 0;
        $totalAdmin = 0;
        $answerSnapshot = [];

        foreach ($questionIds as $questionId) {
            $question = $questions->get($questionId);
            $selectedOptionId = (int) $request->input('q_' . $questionId);

            if ($selectedOptionId <= 0) {
                return back()->with('error', 'Harap jawab semua soal sebelum submit.')->withInput();
            }

            $selectedOption = $question->options->firstWhere('id', $selectedOptionId);
            if (!$selectedOption) {
                return back()->with('error', 'Ada opsi jawaban yang tidak valid.')->withInput();
            }

            $web = (int) $selectedOption->bobot_web;
            $marketing = (int) $selectedOption->bobot_marketing;
            $admin = (int) $selectedOption->bobot_admin;

            $totalWeb += $web;
            $totalMarketing += $marketing;
            $totalAdmin += $admin;

            $answerSnapshot[] = [
                'question_id' => $question->id,
                'jenis_ct' => $question->jenis_ct,
                'narasi_soal' => $question->narasi_soal,
                'selected_option_id' => $selectedOption->id,
                'label_opsi' => $selectedOption->label_opsi,
                'teks_opsi' => $selectedOption->teks_opsi,
                'bobot_web' => $web,
                'bobot_marketing' => $marketing,
                'bobot_admin' => $admin,
            ];
        }

        $totalAll = $totalWeb + $totalMarketing + $totalAdmin;
        $percentWeb = $totalAll > 0 ? round(($totalWeb / $totalAll) * 100, 2) : 0;
        $percentMarketing = $totalAll > 0 ? round(($totalMarketing / $totalAll) * 100, 2) : 0;
        $percentAdmin = $totalAll > 0 ? round(($totalAdmin / $totalAll) * 100, 2) : 0;

        $recommendation = $this->resolveRecommendation($totalWeb, $totalMarketing, $totalAdmin);

        $result = DB::transaction(function () use ($studentId, $totalWeb, $totalMarketing, $totalAdmin, $percentWeb, $percentMarketing, $percentAdmin, $recommendation, $answerSnapshot) {
            $nextAttempt = ((int) BatchTwoCtStudentResult::where('siswa_id', $studentId)->max('attempt_no')) + 1;

            return BatchTwoCtStudentResult::create([
                'siswa_id' => $studentId,
                'attempt_no' => max(1, $nextAttempt),
                'total_web' => $totalWeb,
                'total_marketing' => $totalMarketing,
                'total_admin' => $totalAdmin,
                'persen_web' => $percentWeb,
                'persen_marketing' => $percentMarketing,
                'persen_admin' => $percentAdmin,
                'rekomendasi' => $recommendation,
                'jawaban_json' => $answerSnapshot,
                'submitted_at' => now(),
            ]);
        });

        return redirect()->route('student.batch2ct.result', ['result' => $result->id])
            ->with('success', 'Asesmen Batch 2 CT berhasil disubmit.');
    }

    public function result(Request $request, ?int $result = null)
    {
        $studentId = (int) session('student_id');
        if ($studentId <= 0) {
            return redirect()->route('login')->with('error', 'Sesi siswa tidak valid. Silakan login ulang.');
        }

        $resultQuery = BatchTwoCtStudentResult::with('student.studentClass')->where('siswa_id', $studentId);

        if ($result) {
            $selectedResult = $resultQuery->where('id', $result)->first();
        } else {
            $selectedResult = $resultQuery->latest('attempt_no')->first();
        }

        if (!$selectedResult) {
            return redirect()->route('student.batch2ct.assessment')->with('error', 'Belum ada hasil Batch 2 CT untuk ditampilkan.');
        }

        $student = Student::with('studentClass')->findOrFail($studentId);
        $attempts = BatchTwoCtStudentResult::where('siswa_id', $studentId)
            ->orderByDesc('attempt_no')
            ->get();

        $chartData = [
            'labels' => ['Web Programming', 'Digital Marketing', 'Administratif'],
            'totals' => [
                (int) $selectedResult->total_web,
                (int) $selectedResult->total_marketing,
                (int) $selectedResult->total_admin,
            ],
            'percents' => [
                (float) $selectedResult->persen_web,
                (float) $selectedResult->persen_marketing,
                (float) $selectedResult->persen_admin,
            ],
        ];

        return view('student.batch2ct.result', [
            'student' => $student,
            'result' => $selectedResult,
            'attempts' => $attempts,
            'chartData' => $chartData,
        ]);
    }

    private function resolveRecommendation(int $totalWeb, int $totalMarketing, int $totalAdmin): string
    {
        $scores = [
            'web' => $totalWeb,
            'marketing' => $totalMarketing,
            'admin' => $totalAdmin,
        ];

        $max = max($scores);
        $leaders = array_keys(array_filter($scores, function ($value) use ($max) {
            return $value === $max;
        }));

        if (count($leaders) === 1) {
            return match ($leaders[0]) {
                'web' => 'Web Programming',
                'marketing' => 'Digital Marketing',
                default => 'Administratif',
            };
        }

        sort($leaders);
        $combo = implode('+', $leaders);

        return match ($combo) {
            'marketing+web' => 'Startup / Produk Digital',
            'admin+web' => 'Backend / System Support',
            'admin+marketing' => 'Admin Marketplace / Operasional Digital',
            default => 'Eksplorasi Lintas Bidang (W-M-A Seimbang)',
        };
    }

    private function canonicalCt(string $value): ?string
    {
        $needle = strtolower(trim($value));
        if ($needle === '') {
            return null;
        }

        foreach (self::CT_TYPES as $type) {
            if (strtolower($type) === $needle) {
                return $type;
            }
        }

        return null;
    }
}
