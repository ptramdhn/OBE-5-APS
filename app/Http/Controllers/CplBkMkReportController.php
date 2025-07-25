<?php

namespace App\Http\Controllers;

use App\Enums\UserRoleEnum;
use App\Models\ProgramLearningOutcome;
use App\Models\StudyMaterial;
use Illuminate\Http\Request;
use Inertia\Response;

class CplBkMkReportController extends Controller
{
    public function cplBkMk(): Response
{
    $user = auth()->user();

    // 1. Ambil semua CPL yang relevan (tidak berubah)
    $cplsQuery = ProgramLearningOutcome::query();
    if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
        $cplsQuery->where('prodi_id', $user->prodi_id);
    }
    $allCpls = $cplsQuery->orderBy('code')->get();

    // 2. Ambil semua BK beserta relasi-relasinya
    $bksQuery = StudyMaterial::query();
    if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
        $bksQuery->where('prodi_id', $user->prodi_id);
    }
    
    // --- PERBAIKAN UTAMA ADA DI SINI ---
    // Pastikan nama relasi yang di-load sama persis dengan nama method di model
    $allBks = $bksQuery->with([
        // Dari BK, load `courses`, dan dari `courses`, load `courseLearningOutcomes` dst.
        'courses.courseLearningOutcomes.cpls'
    ])->get();

    // dd($allBks->toArray());
    // --- BATAS PERBAIKAN ---

    // 3. Proses data menjadi struktur matriks
    $formattedBks = $allBks->map(function ($bk) {
        $mappings = [];
        foreach ($bk->courses as $course) {
            // Gunakan nama relasi yang benar di sini juga
            foreach ($course->courseLearningOutcomes as $cpmk) {
                foreach ($cpmk->cpls as $cpl) {
                    if (!isset($mappings[$cpl->id])) {
                        $mappings[$cpl->id] = [];
                    }
                    // Simpan sebagai objek yang berisi kode dan nama MK
                    $mkData = ['code' => $course->id_mk, 'name' => $course->name];
                    // Cek agar tidak ada duplikasi objek MK
                    if (!in_array($mkData, $mappings[$cpl->id])) {
                        $mappings[$cpl->id][] = $mkData;
                    }
                }
            }
        }

        return [
            'id'          => $bk->id,
            'code'        => $bk->code,
            'description' => $bk->description,
            'mappings'    => $mappings,
        ];
    });

    return inertia('Reports/CplBkMk', [
        'pageSettings' => [
                'title' => 'Laporan CPL-BK-MK',
                'subtitle' => 'Menampilkan matriks relasi dari Bahan Kajian ke CPL dan Mata Kuliah',
            ],
            'items' => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Analisis & Laporan Capaian'],
                ['label' => 'Laporan CPL-BK-MK'],
            ],
        'cpls' => $allCpls,
        'bks' => $formattedBks,
    ]);
}
}