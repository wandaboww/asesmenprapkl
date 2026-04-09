<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\AssessmentSubmission;
use App\Models\Industry;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        $students = Student::with(['studentClass', 'submission.recommendation.industry'])->get();
        $results = AssessmentSubmission::with(['student.studentClass', 'recommendation.industry'])->get();
        $classes = StudentClass::whereIn('class_name', ['11 PPLG 1', '11 PPLG 2', '11 PPLG 3'])->orderBy('class_name')->get();

        // Chart Data: Frekuensi Rekomendasi Industri
        $chartLabels = [];
        $chartData = [];
        $industryCounts = [];

        foreach ($students as $student) {
            if ($student->submission && $student->submission->recommendation) {
                $industryName = $student->submission->recommendation->industry->industry_name;
                if (!isset($industryCounts[$industryName])) {
                    $industryCounts[$industryName] = 0;
                }
                $industryCounts[$industryName]++;
            }
        }

        foreach ($industryCounts as $name => $count) {
            $chartLabels[] = $name;
            $chartData[] = $count;
        }

        return view('admin.dashboard', compact('students', 'results', 'classes', 'chartLabels', 'chartData'));
    }

    public function results(Request $request)
    {
        $query = Student::with(['studentClass', 'submission.recommendation.industry'])->orderBy('class_id')->orderBy('full_name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'selesai') {
                $query->whereHas('submission');
            } else if ($request->status === 'belum') {
                $query->whereDoesntHave('submission');
            }
        }

        $students = $query->get();
        $classes = StudentClass::whereIn('class_name', ['11 PPLG 1', '11 PPLG 2', '11 PPLG 3'])->orderBy('class_name')->get();

        return view('admin.results', compact('students', 'classes'));
    }

    public function resetAssessment($student_id)
    {
        $submission = AssessmentSubmission::where('student_id', $student_id)->first();
        if ($submission) {
            $submission->delete(); // This will cascade delete answers and recommendations due to DB constraint
            return back()->with('success', 'Hasil asesmen siswa berhasil direset. Siswa kini dapat mengulang asesmen.');
        }
        return back()->with('error', 'Siswa ini belum mengerjakan asesmen.');
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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Nama Kelas (misal: 11 PPLG 1)');
        $sheet->setCellValue('B1', 'Nama Lengkap Siswa');

        $sheet->setCellValue('A2', '11 PPLG 1');
        $sheet->setCellValue('B2', 'Budi Santoso');

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Import_Siswa.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function importStudents(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
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
                
                // Security & Consistency update: Only allow 3 specific classes
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

    public function exportExcel()
    {
        $students = Student::with(['studentClass', 'submission.recommendation.industry', 'submission.answers.question.category'])
            ->orderBy('class_id')->orderBy('full_name')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Lengkap');
        $sheet->setCellValue('C1', 'Kelas');
        $sheet->setCellValue('D1', 'Skor Pemrograman Website');
        $sheet->setCellValue('E1', 'Skor Administrasi Perkantoran');
        $sheet->setCellValue('F1', 'Skor Digital Marketing');
        $sheet->setCellValue('G1', 'Rekomendasi Industri');
        $sheet->setCellValue('H1', 'Skor Rekomendasi');
        $sheet->setCellValue('I1', 'Tanggal Pengerjaan');

        $rowNum = 2;
        $no = 1;

        foreach ($students as $student) {
            $sub = $student->submission;
            $progScore = 0;
            $adminScore = 0;
            $mktScore = 0;

            if ($sub) {
                $scores = [];
                foreach ($sub->answers as $ans) {
                    $catName = $ans->question->category->category_name;
                    if (!isset($scores[$catName])) {
                        $scores[$catName] = ['total' => 0, 'yes' => 0];
                    }
                    $scores[$catName]['total']++;
                    if (strtolower($ans->answer) === 'ya') {
                        $scores[$catName]['yes']++;
                    }
                }

                $calculate = function($name) use ($scores) {
                    return isset($scores[$name]) && $scores[$name]['total'] > 0 
                        ? number_format(($scores[$name]['yes'] / $scores[$name]['total']) * 100, 1) 
                        : 0;
                };

                $progScore = $calculate('Pemrograman Website');
                $adminScore = $calculate('Administrasi Perkantoran');
                $mktScore = $calculate('Digital Marketing');
            }

            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", $student->full_name);
            $sheet->setCellValue("C{$rowNum}", $student->studentClass->class_name);
            $sheet->setCellValue("D{$rowNum}", $sub ? $progScore . '%' : '-');
            $sheet->setCellValue("E{$rowNum}", $sub ? $adminScore . '%' : '-');
            $sheet->setCellValue("F{$rowNum}", $sub ? $mktScore . '%' : '-');
            $sheet->setCellValue("G{$rowNum}", $sub && $sub->recommendation ? $sub->recommendation->industry->industry_name : '-');
            $sheet->setCellValue("H{$rowNum}", $sub && $sub->recommendation ? number_format($sub->recommendation->score, 1) . '%' : '-');
            $sheet->setCellValue("I{$rowNum}", $sub ? $sub->submitted_at->format('d/m/Y H:i') : '-');

            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Hasil_Assessment_PKL_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }
}
