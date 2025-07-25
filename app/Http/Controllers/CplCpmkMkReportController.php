<?php

namespace App\Http\Controllers;

use App\Enums\UserRoleEnum;
use App\Models\ProgramLearningOutcome;
use Illuminate\Http\Request;
use Inertia\Response;

class CplCpmkMkReportController extends Controller
{
    public function cplCpmkMk(): Response
    {
        $user = auth()->user();
        $query = ProgramLearningOutcome::query();

        // Terapkan filter prodi jika user bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $query->where('prodi_id', $user->prodi_id);
        }

        // Ambil data CPL, beserta relasi cpmks, DAN relasi courses di dalam cpmks
        $cpls = $query
            ->with([
                // Muat relasi cpmks, dan untuk setiap cpmk, muat relasi courses-nya
                'cpmks.courses' => function ($query) {
                    $query->select('courses.id', 'courses.id_mk', 'courses.name');
                }
            ])
            ->whereHas('cpmks.courses') // Hanya ambil CPL yang punya hubungan sampai ke MK
            ->get();

        return inertia('Reports/CplCpmkMk', [
            'pageSettings' => [
                'title' => 'Laporan CPL-CPMK-MK',
                'subtitle' => 'Menampilkan matriks relasi gabungan dari CPL, CPMK, hingga Mata Kuliah',
            ],
            'items' => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Analisis & Laporan Capaian'],
                ['label' => 'Laporan CPL-CPMK-MK'],
            ],
            'cpls' => $cpls, // Kirim data yang sudah di-load
        ]);
    }
}
