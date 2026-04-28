<?php

namespace App\Http\Controllers;

use App\Models\BatchTwoCtQuestion;
use App\Models\BatchTwoCtStudentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BatchTwoCtAdminController extends Controller
{
    private const CT_TYPES = [
        '-',
        'Decomposition',
        'Pattern Recognition',
        'Abstraction',
        'Algorithmic Thinking',
    ];

    private const DIFFICULTY_LEVELS = ['easy', 'medium', 'hard'];

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    public function index()
    {
        $allLatestResults = BatchTwoCtStudentResult::with(['student.studentClass'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->unique('siswa_id')
            ->values();

        $dashboardSummary = [
            'total_questions'   => BatchTwoCtQuestion::count(),
            'active_questions'  => BatchTwoCtQuestion::where('is_active', true)->count(),
            'evaluated_students'=> $allLatestResults->count(),
            'avg_web'           => round((float) $allLatestResults->avg('total_web'), 2),
            'avg_marketing'     => round((float) $allLatestResults->avg('total_marketing'), 2),
            'avg_admin'         => round((float) $allLatestResults->avg('total_admin'), 2),
        ];

        $ctCounts = BatchTwoCtQuestion::query()
            ->select('jenis_ct', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_ct')
            ->pluck('total', 'jenis_ct')
            ->toArray();

        $recommendationCounts = $allLatestResults
            ->groupBy('rekomendasi')
            ->map(fn($items) => $items->count())
            ->sortDesc();

        return view('admin.batch2ct.ringkasan', [
            'dashboardSummary'    => $dashboardSummary,
            'ctCounts'            => $ctCounts,
            'recommendationCounts'=> $recommendationCounts,
        ]);
    }

    public function ranking()
    {
        $allLatestResults = BatchTwoCtStudentResult::with(['student.studentClass'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->unique('siswa_id')
            ->values();

        $rankingWeb       = $allLatestResults->sortByDesc('total_web')->take(10)->values();
        $rankingMarketing = $allLatestResults->sortByDesc('total_marketing')->take(10)->values();
        $rankingAdmin     = $allLatestResults->sortByDesc('total_admin')->take(10)->values();

        return view('admin.batch2ct.ranking', compact(
            'rankingWeb', 'rankingMarketing', 'rankingAdmin'
        ));
    }

    public function rankingLengkap(Request $request)
    {
        $query = BatchTwoCtStudentResult::with(['student.studentClass'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if ($request->filled('kelas')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->kelas);
            });
        }

        if ($request->filled('rekomendasi')) {
            $query->where('rekomendasi', $request->rekomendasi);
        }

        $allLatestResults = $query->get()
            ->unique('siswa_id')
            ->values();

        $allRanked = $allLatestResults->map(function ($r) {
            $r->total_combined = $r->total_web + $r->total_marketing + $r->total_admin;
            return $r;
        })->sortByDesc('total_combined')->values();

        if ($request->query('export') === 'excel') {
            return $this->exportRankingExcel($allRanked);
        }

        $classes = \App\Models\StudentClass::orderBy('class_name')->get();
        $recommendations = ['Pemrograman', 'Digital Marketing', 'Administrasi'];

        return view('admin.batch2ct.ranking-lengkap', compact('allRanked', 'classes', 'recommendations'));
    }

    private function exportRankingExcel($allRanked)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ranking Lengkap');

        // Headers
        $headers = ['Peringkat', 'Nama Siswa', 'Kelas', 'Skor Pemrograman (W)', 'Skor Digital Marketing (M)', 'Skor Administrasi (A)', 'Total Skor', 'Rekomendasi'];
        $sheet->fromArray($headers, null, 'A1');

        // Styling Headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Data
        $data = [];
        foreach ($allRanked as $idx => $r) {
            $data[] = [
                $idx + 1,
                optional($r->student)->full_name ?? '-',
                optional(optional($r->student)->studentClass)->class_name ?? '-',
                $r->total_web,
                $r->total_marketing,
                $r->total_admin,
                $r->total_combined,
                $r->rekomendasi ?? '-',
            ];
        }
        $sheet->fromArray($data, null, 'A2');

        // Styling Data
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 2) {
            $dataStyle = [
                'borders' => [
                    'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ];
            $sheet->getStyle('A2:H' . $highestRow)->applyFromArray($dataStyle);

            // Center align specific columns
            $centerCols = ['A', 'D', 'E', 'F', 'G'];
            foreach ($centerCols as $col) {
                $sheet->getStyle($col . '2:' . $col . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }

        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Ranking_Lengkap_Batch2_CT_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function resetResult(Request $request)
    {
        $request->validate(['siswa_id' => 'required|integer']);
        BatchTwoCtStudentResult::where('siswa_id', $request->siswa_id)->delete();
        return back()->with('success', 'Hasil asesmen Batch 2 CT siswa berhasil direset.');
    }

    public function kelolaSoal()
    {
        return view('admin.batch2ct.kelola-soal', [
            'ctTypes'          => self::CT_TYPES,
            'difficultyLevels' => self::DIFFICULTY_LEVELS,
        ]);
    }

    public function bankSoal(Request $request)
    {
        $selectedJenisCt   = $this->canonicalCt((string) $request->query('jenis_ct'));
        $selectedDifficulty = $this->canonicalDifficulty((string) $request->query('level_kesulitan'));

        $q = BatchTwoCtQuestion::with(['options' => fn($q) => $q->orderBy('label_opsi')])
            ->orderBy('id');

        if ($selectedJenisCt)   $q->where('jenis_ct', $selectedJenisCt);
        if ($selectedDifficulty) $q->where('level_kesulitan', $selectedDifficulty);

        return view('admin.batch2ct.bank-soal', [
            'ctTypes'           => self::CT_TYPES,
            'difficultyLevels'  => self::DIFFICULTY_LEVELS,
            'selectedJenisCt'   => $selectedJenisCt,
            'selectedDifficulty'=> $selectedDifficulty,
            'questions'         => $q->get(),
        ]);
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_ct'                  => ['required', Rule::in(self::CT_TYPES)],
            'narasi_soal'               => ['required', 'string'],
            'level_kesulitan'           => ['required', Rule::in(self::DIFFICULTY_LEVELS)],
            'options'                   => ['required', 'array', 'min:3'],
            'options.*.label'           => ['required', 'string', 'max:5'],
            'options.*.teks'            => ['required', 'string'],
            'options.*.bobot_web'       => ['required', 'integer', 'min:0', 'max:4'],
            'options.*.bobot_marketing' => ['required', 'integer', 'min:0', 'max:4'],
            'options.*.bobot_admin'     => ['required', 'integer', 'min:0', 'max:4'],
        ]);

        if (BatchTwoCtQuestion::where('jenis_ct', $validated['jenis_ct'])
            ->where('narasi_soal', $validated['narasi_soal'])->exists()) {
            throw ValidationException::withMessages([
                'narasi_soal' => 'Soal dengan narasi dan jenis yang sama sudah terdaftar di sistem.',
            ]);
        }

        $options = $this->normalizeOptions($validated['options']);
        $this->validateOptionDominance($options);

        DB::transaction(function () use ($validated, $options, $request) {
            $q = BatchTwoCtQuestion::create([
                'jenis_ct'       => $validated['jenis_ct'],
                'narasi_soal'    => $validated['narasi_soal'],
                'level_kesulitan'=> $validated['level_kesulitan'],
                'is_active'      => $request->boolean('is_active', true),
            ]);
            foreach ($options as $opt) $q->options()->create($opt);
        });

        return back()->with('success', 'Soal Batch 2 CT berhasil ditambahkan.');
    }

    public function edit(BatchTwoCtQuestion $question)
    {
        $question->load(['options' => fn($q) => $q->orderBy('label_opsi')]);
        return view('admin.batch2ct.edit', [
            'question'         => $question,
            'ctTypes'          => self::CT_TYPES,
            'difficultyLevels' => self::DIFFICULTY_LEVELS,
        ]);
    }

    public function update(Request $request, BatchTwoCtQuestion $question)
    {
        $validated = $request->validate([
            'jenis_ct'                  => ['required', Rule::in(self::CT_TYPES)],
            'narasi_soal'               => ['required', 'string'],
            'level_kesulitan'           => ['required', Rule::in(self::DIFFICULTY_LEVELS)],
            'options'                   => ['required', 'array', 'min:3'],
            'options.*.label'           => ['required', 'string', 'max:5'],
            'options.*.teks'            => ['required', 'string'],
            'options.*.bobot_web'       => ['required', 'integer', 'min:0', 'max:4'],
            'options.*.bobot_marketing' => ['required', 'integer', 'min:0', 'max:4'],
            'options.*.bobot_admin'     => ['required', 'integer', 'min:0', 'max:4'],
        ]);

        $options = $this->normalizeOptions($validated['options']);
        $this->validateOptionDominance($options);

        DB::transaction(function () use ($request, $question, $validated, $options) {
            $question->update([
                'jenis_ct'       => $validated['jenis_ct'],
                'narasi_soal'    => $validated['narasi_soal'],
                'level_kesulitan'=> $validated['level_kesulitan'],
                'is_active'      => $request->boolean('is_active', true),
            ]);
            $question->options()->delete();
            foreach ($options as $opt) $question->options()->create($opt);
        });

        return redirect()->route('admin.batch2ct.bank-soal')
            ->with('success', 'Soal Batch 2 CT berhasil diperbarui.');
    }

    public function destroy(BatchTwoCtQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Soal Batch 2 CT berhasil dihapus.');
    }

    public function destroyAll()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('batch_two_ct_question_options')->truncate();
        BatchTwoCtQuestion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return back()->with('success', 'Semua soal Batch 2 CT berhasil dihapus.');
    }

    // -------------------------------------------------------------------------
    // Export / Import JSON
    // -------------------------------------------------------------------------

    public function exportJson()
    {
        $questions = BatchTwoCtQuestion::with(['options' => fn($q) => $q->orderBy('label_opsi')])
            ->orderBy('id')->get();

        $payload = [
            'generated_at' => now()->toDateTimeString(),
            'questions'    => ($questions->isEmpty() ? collect($this->exampleQuestions()) : $questions)
                ->map(fn($q) => [
                    'jenis_ct'       => $q->jenis_ct,
                    'narasi_soal'    => $q->narasi_soal,
                    'level_kesulitan'=> $q->level_kesulitan,
                    'is_active'      => (bool) $q->is_active,
                    'options'        => $q->options->map(fn($o) => [
                        'label'           => $o->label_opsi,
                        'teks'            => $o->teks_opsi,
                        'bobot_web'       => (int) $o->bobot_web,
                        'bobot_marketing' => (int) $o->bobot_marketing,
                        'bobot_admin'     => (int) $o->bobot_admin,
                    ])->values()->all(),
                ])->values()->all(),
        ];

        return response()->streamDownload(
            fn() => print(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            'batch2_ct_questions_' . date('Ymd_His') . '.json',
            ['Content-Type' => 'application/json']
        );
    }

    /** Import JSON — upsert (update jika sudah ada, insert jika baru). */
    public function importJson(Request $request)
    {
        $request->validate(['json_file' => ['required', 'file', 'mimes:json,txt']]);

        $content = file_get_contents($request->file('json_file')->getRealPath());
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            return back()->with('error', 'File JSON tidak valid.');
        }

        $payloads = $decoded['questions'] ?? $decoded;
        if (!is_array($payloads)) {
            return back()->with('error', 'Struktur JSON tidak dikenali.');
        }

        $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'options' => 0];

        DB::transaction(function () use ($payloads, &$stats) {
            foreach ($payloads as $item) {
                if (!is_array($item)) { $stats['skipped']++; continue; }

                $jenisCt  = $this->resolveCtType((string) ($item['jenis_ct'] ?? ''));
                $level    = $this->canonicalDifficulty((string) ($item['level_kesulitan'] ?? ''));
                $narasi   = trim((string) ($item['narasi_soal'] ?? ''));
                $optRaw   = $item['options'] ?? [];

                if (!$jenisCt || !$level || $narasi === '' || !is_array($optRaw)) {
                    $stats['skipped']++; continue;
                }

                $options = $this->normalizeOptions($optRaw);
                if (count($options) < 3) { $stats['skipped']++; continue; }

                try {
                    $this->validateOptionDominance($options);
                } catch (ValidationException $e) {
                    Log::warning('importJson skip', ['narasi' => substr($narasi, 0, 60), 'err' => $e->errors()]);
                    $stats['skipped']++; continue;
                }

                $this->upsertQuestion($jenisCt, $narasi, $level,
                    (bool) ($item['is_active'] ?? true), $options, $stats);
            }
        });

        return back()->with('success', $this->buildImportMessage($stats));
    }

    // -------------------------------------------------------------------------
    // Export Excel
    // -------------------------------------------------------------------------

    public function exportExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Jenis CT', 'Narasi Soal', 'Level Kesulitan',
            'Label Opsi', 'Teks Opsi',
            'Bobot Web', 'Bobot Marketing', 'Bobot Admin', 'Status Soal',
        ];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '1', $h
            );
        }

        $questions = BatchTwoCtQuestion::with(['options' => fn($q) => $q->orderBy('label_opsi')])
            ->orderBy('id')->get();

        $rows = $questions->isEmpty()
            ? $this->exampleExcelRows()
            : $this->questionsToExcelRows($questions);

        $row = 2;
        foreach ($rows as $r) {
            foreach (['A','B','C','D','E','F','G','H','I'] as $ci => $col) {
                $sheet->setCellValue($col . $row, $r[$ci]);
            }
            $row++;
        }

        return response()->streamDownload(
            fn() => (new Xlsx($spreadsheet))->save('php://output'),
            'batch2_ct_questions_' . date('Ymd_His') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    // -------------------------------------------------------------------------
    // Import Excel  ← FIXED
    // -------------------------------------------------------------------------

    /**
     * Import Excel — upsert.
     *
     * Perbaikan utama vs versi sebelumnya:
     *  1. toArray() tanpa formatData agar nilai numerik tetap integer/float murni.
     *  2. Sticky-cell tidak memfilter CT_TYPES secara ketat saat parse baris;
     *     validasi dilakukan di tahap upsert.
     *  3. validateOptionDominance di-catch per item — soal buruk di-skip,
     *     soal lain tetap diproses.
     *  4. resolveCtType() dengan alias bahasa Indonesia & fuzzy match.
     *  5. Setiap alasan skip di-log agar mudah di-debug.
     */
    public function importExcel(Request $request)
    {
        $request->validate(['excel_file' => ['required', 'file', 'mimes:xlsx']]);

        set_time_limit(0);

        if (!class_exists(\ZipArchive::class)) {
            return back()->with('error', 'Ekstensi ZIP belum aktif.');
        }

        // ── Baca Excel ──────────────────────────────────────────────────────
        $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());

        // PENTING: gunakan parameter default (formatData=false) agar nilai
        // numerik seperti bobot tidak berubah menjadi string berformat.
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (count($rows) <= 1) {
            return back()->with('error', 'File Excel tidak memiliki data.');
        }

        array_shift($rows); // buang header

        // ── Parse & group ───────────────────────────────────────────────────
        [$grouped, $parseErrors] = $this->parseExcelRows($rows);

        if (empty($grouped)) {
            $hint = implode(' | ', array_slice($parseErrors, 0, 5));
            return back()->with('error',
                'Tidak ada data soal yang valid. Periksa format kolom. ' .
                ($hint ? "Detail: $hint" : '')
            );
        }

        // ── Upsert ke DB ────────────────────────────────────────────────────
        $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'options' => 0];

        DB::transaction(function () use ($grouped, &$stats) {
            foreach ($grouped as $item) {
                $options = $this->normalizeOptions($item['options']);

                if (count($options) < 3) {
                    Log::warning('importExcel: opsi < 3', [
                        'narasi' => substr($item['narasi_soal'], 0, 60),
                        'count'  => count($options),
                    ]);
                    $stats['skipped']++;
                    continue;
                }

                try {
                    $this->validateOptionDominance($options);
                } catch (ValidationException $e) {
                    Log::warning('importExcel: dominance fail', [
                        'narasi' => substr($item['narasi_soal'], 0, 60),
                        'errors' => $e->errors(),
                    ]);
                    $stats['skipped']++;
                    continue;
                }

                $this->upsertQuestion(
                    $item['jenis_ct'], $item['narasi_soal'],
                    $item['level_kesulitan'], $item['is_active'],
                    $options, $stats
                );
            }
        });

        $msg = $this->buildImportMessage($stats);
        if (!empty($parseErrors)) {
            $msg .= ' (' . count($parseErrors) . ' baris dilewati saat parsing: '
                  . implode('; ', array_slice($parseErrors, 0, 3))
                  . (count($parseErrors) > 3 ? '...' : '') . ')';
        }

        return back()->with('success', $msg);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Parse raw Excel rows menjadi array grouped per soal.
     *
     * @return array{0: array, 1: string[]}  [$grouped, $errors]
     */
    private function parseExcelRows(array $rows): array
    {
        $grouped = [];
        $errors  = [];

        // Sticky-cell state
        $lastJenisCt        = null;
        $lastNarasi         = '';
        $lastLevelKesulitan = '';
        $lastIsActive       = true;

        foreach ($rows as $rowNum => $row) {
            // Normalise: pastikan minimal 9 elemen
            $row = array_pad((array) $row, 9, null);

            // Baca setiap sel sebagai string yang dibersihkan
            $rawJenisCt        = $this->cellString($row[0]);
            $rawNarasi         = $this->cellString($row[1]);
            $rawLevelKesulitan = $this->cellString($row[2]);
            $label             = strtoupper($this->cellString($row[3]));
            $teks              = $this->cellString($row[4]);
            $isActiveRaw       = $row[8];

            // ── Update sticky-cell hanya jika kolom diisi ─────────────────
            if ($rawJenisCt !== '') {
                // Terima nilai apa pun — resolusi ke CT_TYPES dilakukan saat upsert
                $lastJenisCt = $rawJenisCt;
            }
            if ($rawNarasi !== '') {
                $lastNarasi = $rawNarasi;
            }
            if ($rawLevelKesulitan !== '') {
                $lastLevelKesulitan = $this->canonicalDifficulty($rawLevelKesulitan);
            }
            if ($isActiveRaw !== null && $this->cellString($isActiveRaw) !== '') {
                $lastIsActive = $this->parseExcelBoolean($isActiveRaw);
            }

            // ── Validasi minimal sebelum menambah opsi ────────────────────
            if ($lastNarasi === '' || $teks === '') {
                // Baris benar-benar kosong — lewati saja tanpa mencatat error
                continue;
            }

            // jenis_ct belum diisi sama sekali sejak baris pertama
            if ($lastJenisCt === null) {
                $errors[] = "Baris " . ($rowNum + 2) . ": Jenis CT belum diisi";
                continue;
            }

            // level_kesulitan belum diisi
            if ($lastLevelKesulitan === '') {
                $errors[] = "Baris " . ($rowNum + 2) . ": Level Kesulitan belum diisi";
                continue;
            }

            // Resolve jenis_ct ke nilai canonical CT_TYPES
            $resolvedCt = $this->resolveCtType($lastJenisCt);
            if (!$resolvedCt) {
                $errors[] = "Baris " . ($rowNum + 2) . ": Jenis CT '{$lastJenisCt}' tidak dikenali";
                continue;
            }

            $key = md5(strtolower($resolvedCt . '|' . $lastNarasi . '|' . $lastLevelKesulitan));

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'jenis_ct'        => $resolvedCt,
                    'narasi_soal'     => $lastNarasi,
                    'level_kesulitan' => $lastLevelKesulitan,
                    'is_active'       => $lastIsActive,
                    'options'         => [],
                ];
            }

            $grouped[$key]['options'][] = [
                'label'           => $label,
                'teks'            => $teks,
                'bobot_web'       => $this->boundWeight($row[5]),
                'bobot_marketing' => $this->boundWeight($row[6]),
                'bobot_admin'     => $this->boundWeight($row[7]),
            ];
        }

        return [$grouped, $errors];
    }

    /**
     * Resolve string apapun ke salah satu nilai CT_TYPES yang valid.
     * Mengembalikan null jika benar-benar tidak dikenali.
     */
    private function resolveCtType(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') return null;

        $lower = strtolower($trimmed);

        // 1. Exact match (case-insensitive)
        foreach (self::CT_TYPES as $type) {
            if (strtolower($type) === $lower) return $type;
        }

        // 2. Alias / variasi bahasa Indonesia & singkatan
        $aliases = [
            // Decomposition
            'decomposition'         => 'Decomposition',
            'dekomposisi'           => 'Decomposition',
            'decompose'             => 'Decomposition',
            'dekomp'                => 'Decomposition',
            // Pattern Recognition
            'pattern recognition'   => 'Pattern Recognition',
            'pattern'               => 'Pattern Recognition',
            'pengenalan pola'       => 'Pattern Recognition',
            'pola'                  => 'Pattern Recognition',
            'pr'                    => 'Pattern Recognition',
            // Abstraction
            'abstraction'           => 'Abstraction',
            'abstraksi'             => 'Abstraction',
            'abstract'              => 'Abstraction',
            'abs'                   => 'Abstraction',
            // Algorithmic Thinking
            'algorithmic thinking'  => 'Algorithmic Thinking',
            'algorithmic'           => 'Algorithmic Thinking',
            'algoritma'             => 'Algorithmic Thinking',
            'algorithm'             => 'Algorithmic Thinking',
            'berpikir algoritmik'   => 'Algorithmic Thinking',
            'berpikir algoritma'    => 'Algorithmic Thinking',
            'at'                    => 'Algorithmic Thinking',
        ];

        if (isset($aliases[$lower])) return $aliases[$lower];

        // 3. Starts-with match (e.g. "Decomp..." → Decomposition)
        foreach (self::CT_TYPES as $type) {
            if ($type === '-') continue;
            if (str_starts_with(strtolower($type), $lower) ||
                str_starts_with($lower, strtolower($type))) {
                return $type;
            }
        }

        return null; // benar-benar tidak dikenali
    }

    /**
     * Baca sel Excel sebagai string bersih (trim, null → '').
     */
    private function cellString(mixed $value): string
    {
        if ($value === null) return '';
        return trim((string) $value);
    }

    private function normalizeOptions(array $rawOptions): array
    {
        $normalized = [];
        foreach ($rawOptions as $idx => $opt) {
            if (!is_array($opt)) continue;

            $label = strtoupper(trim((string) ($opt['label'] ?? '')));
            if ($label === '') $label = chr(65 + $idx);

            $text = trim((string) ($opt['teks'] ?? ''));
            if ($text === '') continue;

            $normalized[] = [
                'label_opsi'      => $label,
                'teks_opsi'       => $text,
                'bobot_web'       => $this->boundWeight($opt['bobot_web'] ?? 0),
                'bobot_marketing' => $this->boundWeight($opt['bobot_marketing'] ?? 0),
                'bobot_admin'     => $this->boundWeight($opt['bobot_admin'] ?? 0),
                'is_active'       => true,
            ];
        }
        return $normalized;
    }

    private function validateOptionDominance(array $options): void
    {
        $labels = array_column($options, 'label_opsi');
        if (count($labels) !== count(array_unique($labels))) {
            throw ValidationException::withMessages([
                'options' => 'Label opsi harus unik untuk setiap soal.',
            ]);
        }

        $hasPositiveWeight = false;
        foreach ($options as $i => $opt) {
            if (max((int)$opt['bobot_web'], (int)$opt['bobot_marketing'], (int)$opt['bobot_admin']) > 0) {
                $hasPositiveWeight = true;
            }
        }

        if (!$hasPositiveWeight) {
            throw ValidationException::withMessages([
                'options' => 'Soal harus memiliki setidaknya satu opsi dengan bobot lebih dari 0.',
            ]);
        }
    }

    /**
     * Upsert satu soal: update jika sudah ada, insert jika baru.
     */
    private function upsertQuestion(
        string $jenisCt, string $narasi, string $level,
        bool $isActive, array $options, array &$stats
    ): void {
        $existing = BatchTwoCtQuestion::where('jenis_ct', $jenisCt)
            ->where('narasi_soal', $narasi)
            ->first();

        if ($existing) {
            $existing->update(['level_kesulitan' => $level, 'is_active' => $isActive]);
            $existing->options()->delete();
            foreach ($options as $opt) {
                $existing->options()->create($opt);
                $stats['options']++;
            }
            $stats['updated']++;
        } else {
            $q = BatchTwoCtQuestion::create([
                'jenis_ct'        => $jenisCt,
                'narasi_soal'     => $narasi,
                'level_kesulitan' => $level,
                'is_active'       => $isActive,
            ]);
            foreach ($options as $opt) {
                $q->options()->create($opt);
                $stats['options']++;
            }
            $stats['inserted']++;
        }
    }

    private function boundWeight(mixed $value): int
    {
        // Tangani format desimal dari Excel (e.g. "3.0", "2,0")
        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }
        if (!is_numeric($value)) return 0;
        return max(0, min(4, (int) (float) $value));
    }

    private function parseExcelBoolean(mixed $value, bool $default = true): bool
    {
        if (is_null($value) || trim((string) $value) === '') return $default;
        $n = strtolower(trim((string) $value));
        return in_array($n, ['1', 'true', 'ya', 'yes', 'aktif', 'active'], true);
    }

    private function canonicalCt(string $value): ?string
    {
        return $this->resolveCtType($value);
    }

    private function canonicalDifficulty(string $value): string
    {
        $needle = strtolower(trim($value));
        if ($needle === '') return '';
        if (in_array($needle, self::DIFFICULTY_LEVELS, true)) return $needle;
        return [
            'mudah'    => 'easy',  'gampang'   => 'easy',
            'sedang'   => 'medium','menengah'  => 'medium',
            'sulit'    => 'hard',  'susah'     => 'hard',
            'tinggi'   => 'hard',  'difficult' => 'hard',
        ][$needle] ?? 'medium';
    }

    private function buildImportMessage(array $stats): string
    {
        $parts = [];
        if ($stats['inserted'] > 0) $parts[] = $stats['inserted'] . ' soal baru ditambahkan';
        if (($stats['updated'] ?? 0) > 0) $parts[] = $stats['updated'] . ' soal diperbarui';
        if ($stats['skipped'] > 0) $parts[] = $stats['skipped'] . ' soal dilewati';
        if (empty($parts)) return 'Tidak ada soal yang diproses.';
        return 'Import selesai: ' . implode(', ', $parts) . '. Total opsi: ' . $stats['options'] . '.';
    }

    // ── Data contoh ──────────────────────────────────────────────────────────

    private function exampleQuestions(): array
    {
        return [
            (object)[
                'jenis_ct' => 'Decomposition', 'level_kesulitan' => 'medium', 'is_active' => true,
                'narasi_soal' => 'Sebuah sistem e-commerce memiliki fitur keranjang belanja. Bagaimana cara memecah masalah besar ini menjadi bagian-bagian kecil?',
                'options' => collect([
                    (object)['label_opsi'=>'A','teks_opsi'=>'Membuat database produk',       'bobot_web'=>4,'bobot_marketing'=>0,'bobot_admin'=>2],
                    (object)['label_opsi'=>'B','teks_opsi'=>'Merancang alur checkout',       'bobot_web'=>2,'bobot_marketing'=>0,'bobot_admin'=>4],
                    (object)['label_opsi'=>'C','teks_opsi'=>'Mempromosikan fitur keranjang', 'bobot_web'=>0,'bobot_marketing'=>4,'bobot_admin'=>0],
                ]),
            ],
            (object)[
                'jenis_ct' => 'Pattern Recognition', 'level_kesulitan' => 'easy', 'is_active' => true,
                'narasi_soal' => 'Data penjualan 3 tahun menunjukkan lonjakan di bulan Oktober. Apa langkah terbaik?',
                'options' => collect([
                    (object)['label_opsi'=>'A','teks_opsi'=>'Siapkan stok lebih banyak di bulan September', 'bobot_web'=>0,'bobot_marketing'=>4,'bobot_admin'=>2],
                    (object)['label_opsi'=>'B','teks_opsi'=>'Abaikan data karena hujan tidak pasti',        'bobot_web'=>0,'bobot_marketing'=>0,'bobot_admin'=>1],
                    (object)['label_opsi'=>'C','teks_opsi'=>'Buat promo diskon di bulan Januari',           'bobot_web'=>0,'bobot_marketing'=>2,'bobot_admin'=>0],
                ]),
            ],
        ];
    }

    private function questionsToExcelRows($questions): array
    {
        $rows = [];
        foreach ($questions as $q) {
            $first = true;
            foreach ($q->options as $opt) {
                $rows[] = [
                    $first ? $q->jenis_ct        : '',
                    $first ? $q->narasi_soal      : '',
                    $first ? $q->level_kesulitan  : '',
                    $opt->label_opsi,
                    $opt->teks_opsi,
                    (int) $opt->bobot_web,
                    (int) $opt->bobot_marketing,
                    (int) $opt->bobot_admin,
                    $first ? ($q->is_active ? 1 : 0) : '',
                ];
                $first = false;
            }
        }
        return $rows;
    }

    private function exampleExcelRows(): array
    {
        $rows = [];
        foreach ($this->exampleQuestions() as $q) {
            $first = true;
            foreach ($q->options as $opt) {
                $rows[] = [
                    $first ? $q->jenis_ct       : '',
                    $first ? $q->narasi_soal     : '',
                    $first ? $q->level_kesulitan : '',
                    $opt->label_opsi,
                    $opt->teks_opsi,
                    $opt->bobot_web,
                    $opt->bobot_marketing,
                    $opt->bobot_admin,
                    $first ? 1 : '',
                ];
                $first = false;
            }
        }
        return $rows;
    }
}
