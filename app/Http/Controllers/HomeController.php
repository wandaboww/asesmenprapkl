<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentClass;
use App\Models\Student;

class HomeController extends Controller
{
    public function index()
    {
        $classes = StudentClass::whereIn('class_name', ['11 PPLG 1', '11 PPLG 2', '11 PPLG 3'])->orderBy('class_name')->get();
        return view('auth.login', compact('classes'));
    }

    public function getStudentsByClass(Request $request)
    {
        $classId = $request->query('class_id');
        if (!$classId) {
            return response()->json([]);
        }

        $students = Student::where('class_id', $classId)->orderBy('full_name')->get(['id', 'full_name']);
        return response()->json($students);
    }

    public function attemptLogin(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::with('studentClass')->where('id', $request->student_id)->where('class_id', $request->class_id)->first();

        if ($student) {
            session([
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'class_name' => $student->studentClass->class_name,
            ]);
            return redirect()->route('student.dashboard');
        }

        return back()->with('error', 'Data tidak ditemukan!');
    }

    public function logout()
    {
        session()->forget(['student_id', 'student_name', 'class_name']);
        return redirect()->route('login');
    }
}
