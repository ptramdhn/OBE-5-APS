<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\CplCpmkRequest;
use App\Http\Resources\ProgramLearningOutcomeResource;
use App\Models\CourseLearningOutcome;
use App\Models\ProgramLearningOutcome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Throwable;

class CplCpmkController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $queryCpls = ProgramLearningOutcome::query();

        // Terapkan filter berdasarkan prodi jika user bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $queryCpls->where('prodi_id', $user->prodi_id);
        }

        $cpls = $queryCpls
            // Ambil hanya CPL yang memiliki relasi ke CPMK
            ->has('cpmks')
            // Eager load relasi cpmks (untuk ditampilkan) dan programStudy (untuk nama prodi)
            ->with(['cpmks:id,code,description', 'programStudy:id,name'])
            // Asumsi Anda memiliki scope untuk filter dan sorting
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Kirim data ke komponen Inertia
        return inertia('CPL-CPMK/Index', [
            'pageSettings' => [
                'title' => 'CPL-CPMK',
                'subtitle' => 'Menampilkan semua pemetaan CPL-CPMK yang sudah terdaftar',
            ],
            // Gunakan API Resource untuk transformasi data yang aman
            'cpls' => ProgramLearningOutcomeResource::collection($cpls),
            'items' => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-CPMK'],
            ],
            // State untuk filter di frontend
            'state' => [
                'page'   => request()->page ?? 1,
                'search' => request()->search ?? '',
                'load'   => request()->load ?? 10,
            ]
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();

        // 1. Query untuk mengambil data CPL
        $cplsQuery = ProgramLearningOutcome::query()->select(['id', 'prodi_id', 'code', 'description']);

        // 2. Query untuk mengambil data CPMK
        $cpmksQuery = CourseLearningOutcome::query()
                ->select(['id', 'code', 'description'])
                ->with('courses:id,prodi_id');

        // 3. Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            // Filter CPL berdasarkan prodi_id langsung
            $cplsQuery->where('prodi_id', $user->prodi_id);
            // Filter CPMK berdasarkan prodi dari MK yang terhubung dengannya
            $cpmksQuery->whereHas('courses', function ($query) use ($user) {
                $query->where('prodi_id', $user->prodi_id);
            });
        }

        // 4. Eksekusi query dan format data untuk dropdown
        $cpls = $cplsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        $cpmks = $cpmksQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->courses->first()?->prodi_id,
        ]);

        return inertia('CPL-CPMK/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah Pemetaan CPL-CPMK',
                'subtitle' => 'Pilih CPL dan petakan ke beberapa CPMK yang sesuai',
                'method' => 'POST',
                'action' => route('cpl-cpmk.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-CPMK', 'href' => route('cpl-cpmk.index')],
                ['label' => 'Tambah Pemetaan'],
            ],
            'cpls' => fn() => $cpls,
            'cpmks' => fn() => $cpmks,
        ]);
    }

    public function store(CplCpmkRequest $request): RedirectResponse
    {
        try {
            $cpl = ProgramLearningOutcome::findOrFail($request->cpl_id);
            $cpmkIds = collect($request->cpmk_ids)->pluck('value');
            
            // Gunakan nama relasi yang benar ('cpmks')
            $cpl->cpmks()->syncWithoutDetaching($cpmkIds);
            
            flashMessage(MessageType::CREATED->message('Pemetaan CPL-CPMK'));
            return to_route('cpl-cpmk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-cpmk.index');
        }
    }

    public function edit(ProgramLearningOutcome $cpl): Response
    {
        $user = auth()->user();

        // Query untuk mengambil semua opsi CPL dan CPMK
        $cplsQuery = ProgramLearningOutcome::query()->select(['id', 'prodi_id', 'code', 'description']);
        $cpmksQuery = CourseLearningOutcome::query()->with('courses:id,prodi_id');

        // Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $cplsQuery->where('prodi_id', $user->prodi_id);
            $cpmksQuery->whereHas('courses', function ($query) use ($user) {
                $query->where('prodi_id', $user->prodi_id);
            });
        }

        // Eksekusi dan format data untuk dropdown
        $cpls = $cplsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        $cpmks = $cpmksQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->courses->first()?->prodi_id,
        ]);

        return inertia('CPL-CPMK/Edit', [
            'pageSettings' => fn() => [
                'title' => 'Edit Pemetaan CPL-CPMK',
                'subtitle' => 'Ubah pemetaan untuk CPL yang dipilih',
                'method' => 'PUT',
                'action' => route('cpl-cpmk.update', $cpl->id),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-CPMK', 'href' => route('cpl-cpmk.index')],
                ['label' => 'Edit Pemetaan'],
            ],
            // Data pemetaan yang akan diedit
            'mapping' => [
                'cpl_id' => $cpl->id,
                'cpmk_ids' => $cpl->cpmks->pluck('id'), // Ambil ID dari CPMK yang sudah terhubung
            ],
            // Data untuk mengisi pilihan dropdown
            'cpls' => fn() => $cpls,
            'cpmks' => fn() => $cpmks,
        ]);
    }

    public function update(CplCpmkRequest $request, ProgramLearningOutcome $cpl): RedirectResponse
    {
        try {
            // `sync()` adalah method yang sempurna untuk update.
            // Ia akan menghapus relasi lama dan menggantinya dengan yang baru dari request.
            $cpmkIds = collect($request->cpmk_ids)->pluck('value');
            $cpl->cpmks()->sync($cpmkIds);

            flashMessage(MessageType::UPDATED->message('Pemetaan CPL-CPMK'));
            return to_route('cpl-cpmk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-cpmk.index');
        }
    }

    public function destroy(ProgramLearningOutcome $cpl): RedirectResponse
    {
        try {
            // Gunakan method `detach()` pada relasi `cpmks()`
            // untuk menghapus semua record terkait di tabel pivot.
            $cpl->cpmks()->detach();

            flashMessage(MessageType::DELETED->message('Pemetaan CPL-CPMK'));
            return to_route('cpl-cpmk.index'); // Sesuaikan nama rute

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-cpmk.index'); // Sesuaikan nama rute
        }
    }
}
