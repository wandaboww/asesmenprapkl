<?php

namespace App\Http\Controllers;

use App\Models\BatchTwoCtQuestion;
use App\Models\BatchTwoCtStudentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function index(Request $request)
    {
        $selectedJenisCt = $this->canonicalCt((string) $request->query('jenis_ct'));
        $selectedDifficulty = $this->canonicalDifficulty((string) $request->query('level_kesulitan'));

        $questionQuery = BatchTwoCtQuestion::with(['options' => function ($query) {
            $query->orderBy('label_opsi');
        }])->orderBy('id');

        if ($selectedJenisCt) {
            $questionQuery->where('jenis_ct', $selectedJenisCt);
        }

        if ($selectedDifficulty) {
            $questionQuery->where('level_kesulitan', $selectedDifficulty);
        }

        $questions = $questionQuery->get();

        $allLatestResults = BatchTwoCtStudentResult::with(['student.studentClass'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->unique('siswa_id')
            ->values();

        $dashboardSummary = [
            'total_questions' => BatchTwoCtQuestion::count(),
            'active_questions' => BatchTwoCtQuestion::where('is_active', true)->count(),
            'evaluated_students' => $allLatestResults->count(),
            'avg_web' => round((float) $allLatestResults->avg('total_web'), 2),
            'avg_marketing' => round((float) $allLatestResults->avg('total_marketing'), 2),
            'avg_admin' => round((float) $allLatestResults->avg('total_admin'), 2),
        ];

        $ctCounts = BatchTwoCtQuestion::query()
            ->select('jenis_ct', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_ct')
            ->pluck('total', 'jenis_ct')
            ->toArray();

        $recommendationCounts = $allLatestResults
            ->groupBy('rekomendasi')
            ->map(function ($items) {
                return $items->count();
            })
            ->sortDesc();

        $rankingWeb = $allLatestResults->sortByDesc('total_web')->take(10)->values();
        $rankingMarketing = $allLatestResults->sortByDesc('total_marketing')->take(10)->values();
        $rankingAdmin = $allLatestResults->sortByDesc('total_admin')->take(10)->values();

        return view('admin.batch2ct.index', [
            'ctTypes' => self::CT_TYPES,
            'difficultyLevels' => self::DIFFICULTY_LEVELS,
            'selectedJenisCt' => $selectedJenisCt,
            'selectedDifficulty' => $selectedDifficulty,
            'questions' => $questions,
            'dashboardSummary' => $dashboardSummary,
            'ctCounts' => $ctCounts,
            'recommendationCounts' => $recommendationCounts,
            'rankingWeb' => $rankingWeb,
            'rankingMarketing' => $rankingMarketing,
            'rankingAdmin' => $rankingAdmin,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_ct' => ['required', Rule::in(self::CT_TYPES)],
            'narasi_soal' => ['required', 'string'],
            'level_kesulitan' => ['required', Rule::in(self::DIFFICULTY_LEVELS)],
            'options' => ['required', 'array', 'min:3'],
            'options.*.label' => ['required', 'string', 'max:5'],
            'options.*.teks' => ['required', 'string'],
            'options.*.bobot_web' => ['required', 'integer', 'min:0', 'max:4'],
            'options.*.bobot_marketing' => ['required', 'integer', 'min:0', 'max:4'],
            'options.*.bobot_admin' => ['required', 'integer', 'min:0', 'max:4'],
        ]);

        $exists = BatchTwoCtQuestion::where('jenis_ct', $validated['jenis_ct'])
            ->where('narasi_soal', $validated['narasi_soal'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'narasi_soal' => 'Soal dengan narasi dan jenis yang sama sudah terdaftar di sistem.',
            ]);
        }

        $options = $this->normalizeOptions($validated['options']);
        $this->validateOptionDominance($options);

        DB::transaction(function () use ($validated, $options, $request) {
            $question = BatchTwoCtQuestion::create([
                'jenis_ct' => $validated['jenis_ct'],
                'narasi_soal' => $validated['narasi_soal'],
                'level_kesulitan' => $validated['level_kesulitan'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            foreach ($options as $option) {
                $question->options()->create($option);
            }
        });

        return back()->with('success', 'Soal Batch 2 CT berhasil ditambahkan.');
    }

    public function edit(BatchTwoCtQuestion $question)
    {
        $question->load(['options' => function ($query) {
            $query->orderBy('label_opsi');
        }]);

        return view('admin.batch2ct.edit', [
            'question' => $question,
            'ctTypes' => self::CT_TYPES,
            'difficultyLevels' => self::DIFFICULTY_LEVELS,
        ]);
    }

    public function update(Request $request, BatchTwoCtQuestion $question)
    {
        $validated = $request->validate([
            'jenis_ct' => ['required', Rule::in(self::CT_TYPES)],
            'narasi_soal' => ['required', 'string'],
            'level_kesulitan' => ['required', Rule::in(self::DIFFICULTY_LEVELS)],
            'options' => ['required', 'array', 'min:3'],
            'options.*.label' => ['required', 'string', 'max:5'],
            'options.*.teks' => ['required', 'string'],
            'options.*.bobot_web' => ['required', 'integer', 'min:0', 'max:4'],
            'options.*.bobot_marketing' => ['required', 'integer', 'min:0', 'max:4'],
            'options.*.bobot_admin' => ['required', 'integer', 'min:0', 'max:4'],
        ]);

        $options = $this->normalizeOptions($validated['options']);
        $this->validateOptionDominance($options);

        DB::transaction(function () use ($request, $question, $validated, $options) {
            $question->update([
                'jenis_ct' => $validated['jenis_ct'],
                'narasi_soal' => $validated['narasi_soal'],
                'level_kesulitan' => $validated['level_kesulitan'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            $question->options()->delete();
            foreach ($options as $option) {
                $question->options()->create($option);
            }
        });

        return redirect()->route('admin.batch2ct.index')->with('success', 'Soal Batch 2 CT berhasil diperbarui.');
    }

    public function destroy(BatchTwoCtQuestion $question)
    {
        $question->delete();

        return back()->with('success', 'Soal Batch 2 CT berhasil dihapus.');
    }

    public function exportJson()
    {
        $questions = BatchTwoCtQuestion::with(['options' => function ($query) {
            $query->orderBy('label_opsi');
        }])->orderBy('id')->get();

        if ($questions->isEmpty()) {
            $questions = collect([
                (object) [
                    'jenis_ct' => 'Decomposition',
                    'narasi_soal' => 'Contoh 1: Sebuah sistem e-commerce memiliki fitur keranjang belanja. Bagaimana cara memecah masalah besar ini menjadi bagian-bagian kecil?',
                    'level_kesulitan' => 'medium',
                    'is_active' => true,
                    'options' => collect([
                        (object) ['label_opsi' => 'A', 'teks_opsi' => 'Membuat database produk', 'bobot_web' => 4, 'bobot_marketing' => 0, 'bobot_admin' => 2],
                        (object) ['label_opsi' => 'B', 'teks_opsi' => 'Merancang alur checkout', 'bobot_web' => 2, 'bobot_marketing' => 0, 'bobot_admin' => 4],
                        (object) ['label_opsi' => 'C', 'teks_opsi' => 'Mempromosikan fitur keranjang', 'bobot_web' => 0, 'bobot_marketing' => 4, 'bobot_admin' => 0],
                    ])
                ],
                (object) [
                    'jenis_ct' => 'Pattern Recognition',
                    'narasi_soal' => 'Contoh 2: Setelah menganalisis data penjualan selama 3 tahun, Anda menemukan bahwa penjualan jas hujan selalu meningkat di bulan Oktober. Apa langkah selanjutnya?',
                    'level_kesulitan' => 'medium',
                    'is_active' => true,
                    'options' => collect([
                        (object) ['label_opsi' => 'A', 'teks_opsi' => 'Menyiapkan stok lebih banyak di bulan September', 'bobot_web' => 0, 'bobot_marketing' => 4, 'bobot_admin' => 2],
                        (object) ['label_opsi' => 'B', 'teks_opsi' => 'Mengabaikan data karena hujan tidak pasti', 'bobot_web' => 0, 'bobot_marketing' => 0, 'bobot_admin' => 1],
                        (object) ['label_opsi' => 'C', 'teks_opsi' => 'Membuat promo diskon jas hujan di bulan Januari', 'bobot_web' => 0, 'bobot_marketing' => 2, 'bobot_admin' => 0],
                    ])
                ],
                (object) [
                    'jenis_ct' => 'Abstraction',
                    'narasi_soal' => 'Contoh 3: Anda ingin membuat aplikasi peta digital. Informasi apa yang paling penting untuk ditampilkan kepada pengguna?',
                    'level_kesulitan' => 'medium',
                    'is_active' => true,
                    'options' => collect([
                        (object) ['label_opsi' => 'A', 'teks_opsi' => 'Nama jalan dan arah lalu lintas', 'bobot_web' => 4, 'bobot_marketing' => 0, 'bobot_admin' => 0],
                        (object) ['label_opsi' => 'B', 'teks_opsi' => 'Warna cat rumah di pinggir jalan', 'bobot_web' => 0, 'bobot_marketing' => 0, 'bobot_admin' => 1],
                        (object) ['label_opsi' => 'C', 'teks_opsi' => 'Jumlah pohon di setiap trotoar', 'bobot_web' => 0, 'bobot_marketing' => 0, 'bobot_admin' => 2],
                    ])
                ],
            ]);
        }


        $payload = [
            'generated_at' => now()->toDateTimeString(),
            'questions' => $questions->map(function ($question) {
                return [
                    'jenis_ct' => $question->jenis_ct,
                    'narasi_soal' => $question->narasi_soal,
                    'level_kesulitan' => $question->level_kesulitan,
                    'is_active' => (bool) $question->is_active,
                    'options' => $question->options->map(function ($option) {
                        return [
                            'label' => $option->label_opsi,
                            'teks' => $option->teks_opsi,
                            'bobot_web' => (int) $option->bobot_web,
                            'bobot_marketing' => (int) $option->bobot_marketing,
                            'bobot_admin' => (int) $option->bobot_admin,
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];

        $fileName = 'batch2_ct_questions_' . date('Ymd_His') . '.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function importJson(Request $request)
    {
        $request->validate([
            'json_file' => ['required', 'file', 'mimes:json,txt'],
        ]);

        $content = file_get_contents($request->file('json_file')->getRealPath());
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            return back()->with('error', 'File JSON tidak valid.');
        }

        $questionPayloads = $decoded['questions'] ?? $decoded;
        if (!is_array($questionPayloads)) {
            return back()->with('error', 'Struktur JSON tidak dikenali.');
        }

        $processedQuestions = 0;
        $processedOptions = 0;

        DB::transaction(function () use ($questionPayloads, &$processedQuestions, &$processedOptions) {
            foreach ($questionPayloads as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $jenisCt = $this->canonicalCt((string) ($item['jenis_ct'] ?? ''));
                $levelKesulitan = $this->canonicalDifficulty((string) ($item['level_kesulitan'] ?? ''));
                $narasi = trim((string) ($item['narasi_soal'] ?? ''));
                $optionsRaw = $item['options'] ?? [];

                if (!$jenisCt || !$levelKesulitan || $narasi === '' || !is_array($optionsRaw)) {
                    continue;
                }

                $options = $this->normalizeOptions($optionsRaw);
                if (count($options) < 3) {
                    continue;
                }

                $this->validateOptionDominance($options);

                $question = BatchTwoCtQuestion::create([
                    'jenis_ct' => $jenisCt,
                    'narasi_soal' => $narasi,
                    'level_kesulitan' => $levelKesulitan,
                    'is_active' => (bool) ($item['is_active'] ?? true),
                ]);

                foreach ($options as $option) {
                    $question->options()->create($option);
                    $processedOptions++;
                }

                $processedQuestions++;
            }
        });

        return back()->with('success', 'Import JSON selesai. Soal: ' . $processedQuestions . ', opsi: ' . $processedOptions . '.');
    }

    public function exportExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Jenis CT',
            'Narasi Soal',
            'Level Kesulitan',
            'Label Opsi',
            'Teks Opsi',
            'Bobot Web',
            'Bobot Marketing',
            'Bobot Admin',
            'Status Soal',
        ];

        foreach ($headers as $index => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($col . '1', $header);
        }

        $row = 2;
        $questions = BatchTwoCtQuestion::with(['options' => function ($query) {
            $query->orderBy('label_opsi');
        }])->orderBy('id')->get();

        if ($questions->isEmpty()) {
            // Add 3 example questions to the spreadsheet
            $examples = [
                [
                    'jenis' => 'Decomposition',
                    'narasi' => 'Contoh 1: Sebuah sistem e-commerce memiliki fitur keranjang belanja. Bagaimana cara memecah masalah besar ini menjadi bagian-bagian kecil?',
                    'options' => [
                        ['A', 'Membuat database produk', 4, 0, 2],
                        ['B', 'Merancang alur checkout', 2, 0, 4],
                        ['C', 'Mempromosikan fitur keranjang', 0, 4, 0],
                    ]
                ],
                [
                    'jenis' => 'Pattern Recognition',
                    'narasi' => 'Contoh 2: Setelah menganalisis data penjualan selama 3 tahun, Anda menemukan bahwa penjualan jas hujan selalu meningkat di bulan Oktober. Apa langkah selanjutnya?',
                    'options' => [
                        ['A', 'Menyiapkan stok lebih banyak di bulan September', 0, 4, 2],
                        ['B', 'Mengabaikan data karena hujan tidak pasti', 0, 0, 1],
                        ['C', 'Membuat promo diskon jas hujan di bulan Januari', 0, 2, 0],
                    ]
                ],
                [
                    'jenis' => 'Abstraction',
                    'narasi' => 'Contoh 3: Anda ingin membuat aplikasi peta digital. Informasi apa yang paling penting untuk ditampilkan kepada pengguna?',
                    'options' => [
                        ['A', 'Nama jalan dan arah lalu lintas', 4, 0, 0],
                        ['B', 'Warna cat rumah di pinggir jalan', 0, 0, 1],
                        ['C', 'Jumlah pohon di setiap trotoar', 0, 0, 2],
                    ]
                ],
            ];

            foreach ($examples as $ex) {
                foreach ($ex['options'] as $opt) {
                    $sheet->setCellValue('A' . $row, $ex['jenis']);
                    $sheet->setCellValue('B' . $row, $ex['narasi']);
                    $sheet->setCellValue('C' . $row, 'medium');
                    $sheet->setCellValue('D' . $row, $opt[0]);
                    $sheet->setCellValue('E' . $row, $opt[1]);
                    $sheet->setCellValue('F' . $row, $opt[2]);
                    $sheet->setCellValue('G' . $row, $opt[3]);
                    $sheet->setCellValue('H' . $row, $opt[4]);
                    $sheet->setCellValue('I' . $row, 1);
                    $row++;
                }
            }
        } else {
            foreach ($questions as $question) {
                foreach ($question->options as $option) {
                    $sheet->setCellValue('A' . $row, $question->jenis_ct);
                    $sheet->setCellValue('B' . $row, $question->narasi_soal);
                    $sheet->setCellValue('C' . $row, $question->level_kesulitan);
                    $sheet->setCellValue('D' . $row, $option->label_opsi);
                    $sheet->setCellValue('E' . $row, $option->teks_opsi);
                    $sheet->setCellValue('F' . $row, (int) $option->bobot_web);
                    $sheet->setCellValue('G' . $row, (int) $option->bobot_marketing);
                    $sheet->setCellValue('H' . $row, (int) $option->bobot_admin);
                    $sheet->setCellValue('I' . $row, $question->is_active ? 1 : 0);
                    $row++;
                }
            }
        }


        $fileName = 'batch2_ct_questions_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx'],
        ]);

        set_time_limit(0); // Allow processing many questions without timeout

        if (!class_exists(\ZipArchive::class)) {
            return back()->with('error', 'Ekstensi ZIP belum aktif, import Excel belum dapat dijalankan.');
        }

        $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (count($rows) <= 1) {
            return back()->with('error', 'File Excel tidak memiliki data.');
        }

        array_shift($rows);

        $grouped = [];
        foreach ($rows as $row) {
            $jenisCt = $this->canonicalCt((string) ($row[0] ?? ''));
            $narasi = trim((string) ($row[1] ?? ''));
            $levelKesulitan = $this->canonicalDifficulty((string) ($row[2] ?? ''));
            $label = strtoupper(trim((string) ($row[3] ?? '')));
            $teks = trim((string) ($row[4] ?? ''));

            if (!$jenisCt || !$levelKesulitan || $narasi === '' || $label === '' || $teks === '') {
                continue;
            }

            $key = md5(strtolower($jenisCt . '|' . $narasi . '|' . $levelKesulitan));
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'jenis_ct' => $jenisCt,
                    'narasi_soal' => $narasi,
                    'level_kesulitan' => $levelKesulitan,
                    'is_active' => ((int) ($row[8] ?? 1)) === 1,
                    'options' => [],
                ];
            }

            $grouped[$key]['options'][] = [
                'label' => $label,
                'teks' => $teks,
                'bobot_web' => $this->boundWeight($row[5] ?? 0),
                'bobot_marketing' => $this->boundWeight($row[6] ?? 0),
                'bobot_admin' => $this->boundWeight($row[7] ?? 0),
            ];
        }

        $processedQuestions = 0;
        $processedOptions = 0;
        $duplicateQuestions = 0;

        DB::transaction(function () use ($grouped, &$processedQuestions, &$processedOptions, &$duplicateQuestions) {
            foreach ($grouped as $item) {
                // Check for duplicates in DB
                $exists = BatchTwoCtQuestion::where('jenis_ct', $item['jenis_ct'])
                    ->where('narasi_soal', $item['narasi_soal'])
                    ->exists();

                if ($exists) {
                    $duplicateQuestions++;
                    continue;
                }

                $options = $this->normalizeOptions($item['options']);
                if (count($options) < 3) {
                    continue;
                }

                $this->validateOptionDominance($options);

                $question = BatchTwoCtQuestion::create([
                    'jenis_ct' => $item['jenis_ct'],
                    'narasi_soal' => $item['narasi_soal'],
                    'level_kesulitan' => $item['level_kesulitan'],
                    'is_active' => (bool) $item['is_active'],
                ]);

                foreach ($options as $option) {
                    $question->options()->create($option);
                    $processedOptions++;
                }

                $processedQuestions++;
            }
        });

        $msg = 'Import Excel selesai. Soal: ' . $processedQuestions . ', opsi: ' . $processedOptions . '.';
        if ($duplicateQuestions > 0) {
            $msg .= ' (Serta ' . $duplicateQuestions . ' soal dilewati karena sudah ada/duplikat).';
        }

        return back()->with('success', $msg);
    }

    private function normalizeOptions(array $rawOptions): array
    {
        $normalized = [];

        foreach ($rawOptions as $index => $option) {
            if (!is_array($option)) {
                continue;
            }

            $label = strtoupper(trim((string) ($option['label'] ?? '')));
            if ($label === '') {
                $label = chr(65 + $index);
            }

            $text = trim((string) ($option['teks'] ?? ''));
            if ($text === '') {
                continue;
            }

            $normalized[] = [
                'label_opsi' => $label,
                'teks_opsi' => $text,
                'bobot_web' => $this->boundWeight($option['bobot_web'] ?? 0),
                'bobot_marketing' => $this->boundWeight($option['bobot_marketing'] ?? 0),
                'bobot_admin' => $this->boundWeight($option['bobot_admin'] ?? 0),
                'is_active' => true,
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

        foreach ($options as $index => $option) {
            $max = max([
                (int) $option['bobot_web'],
                (int) $option['bobot_marketing'],
                (int) $option['bobot_admin'],
            ]);

            if ($max <= 0) {
                throw ValidationException::withMessages([
                    'options.' . $index => 'Setiap opsi wajib memiliki minimal satu bobot dominan (> 0).',
                ]);
            }
        }
    }

    private function boundWeight(mixed $value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return max(0, min(4, (int) $value));
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

    private function canonicalDifficulty(string $value): ?string
    {
        $needle = strtolower(trim($value));
        if ($needle === '') {
            return null;
        }

        return in_array($needle, self::DIFFICULTY_LEVELS, true) ? $needle : null;
    }
}
