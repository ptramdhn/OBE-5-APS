<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\CplMkRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\ProgramLearningOutcome;
use App\Models\StudyMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Throwable;

class CplMkController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $queryMks = Course::query()
            ->select(['id', 'prodi_id', 'id_mk', 'name']);

        // $queryProfiles = GraduateProfile::query()
        //     ->select(['id', 'prodi_id', 'code', 'description']);

        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $queryMks->where('prodi_id', $user->prodi_id);
            // $queryProfiles->where('prodi_id', $user->prodi_id);
        }

        $mks = $queryMks
            ->has('programLearningOutcomes')
            ->with(['programLearningOutcomes:id,code,description']) // Eager load relasi
            ->with(['programStudy']) // Eager load relasi
            ->filter(request()->only(['search'])) // <-- FIX 3: Pastikan scope ini ada
            ->sorting(request()->only(['field', 'direction'])) // <-- FIX 3: Pastikan scope ini ada
            ->latest('created_at')
            ->paginate(request()->load ?? 10)
            ->withQueryString();

        return inertia('CPL-MK/Index', [
            'pageSettings' => fn() => [
                'title' => 'CPL-MK',
                'subtitle' => 'Menampilkan semua pemetaan CPL-MK yang sudah terdaftar dalam sistem OBE',
            ],
            'mks' => fn() => CourseResource::collection($mks)->additional([
                'meta' => [
                    'has_pages' => $mks->hasPages(),
                ],
            ]),
            'state' => fn() => [
                'page' =>request()->page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10, 
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-MK'],
            ], 
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();

        // 1. Query untuk mengambil data CPL
        $cplsQuery = ProgramLearningOutcome::query()->select(['id', 'prodi_id', 'code', 'description']);

        // 2. Query untuk mengambil data PL
        $mksQuery = Course::query()->select(['id', 'prodi_id', 'id_mk', 'name']);

        // 3. Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $cplsQuery->where('prodi_id', $user->prodi_id);
            $mksQuery->where('prodi_id', $user->prodi_id);
        }
 
        // 4. Eksekusi query dan format data untuk dropdown
        $cpls = $cplsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id, 
        ]);

        $mks = $mksQuery->orderBy('id_mk')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->id_mk}) {$item->name}",
            'prodi_id' => $item->prodi_id, 
        ]);

        return inertia('CPL-MK/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah Pemetaan CPL-MK',
                'subtitle' => 'Pilih MK dan petakan ke beberapa CPL yang sesuai',
                'method' => 'POST',
                'action' => route('cpl-mk.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-MK', 'href' => route('cpl-mk.index')],
                ['label' => 'Tambah Pemetaan'],
            ],
            // Kirim data CPL dan PL ke frontend
            'cpls' => fn() => $cpls,
            'mks' => fn() => $mks,
        ]);
    }

    public function store(CplMkRequest $request): RedirectResponse
    {
        try {
            $mk = Course::findOrFail($request->mk_id);
            $cplIds = collect($request->cpl_ids)->pluck('value');
            // $cpl->graduateProfiles()->sync($plIds);
            $mk->programLearningOutcomes()->syncWithoutDetaching($cplIds);
            
            flashMessage(MessageType::CREATED->message('Pemetaan CPL-MK'));
            return to_route('cpl-mk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-mk.index');
        }
    }

    public function edit(Course $mk): Response
    {
        $user = auth()->user();

        // Query untuk mengambil semua opsi CPL dan PL
        $cplsQuery = ProgramLearningOutcome::query()->select(['id', 'prodi_id', 'code', 'description']);
        $mksQuery = Course::query()->select(['id', 'prodi_id', 'id_mk', 'name']);

        // Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $cplsQuery->where('prodi_id', $user->prodi_id);
            $mksQuery->where('prodi_id', $user->prodi_id);
        }

        // Eksekusi dan format data untuk dropdown
        $cpls = $cplsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        $mks = $mksQuery->orderBy('id_mk')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->id_mk}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        return inertia('CPL-MK/Edit', [ // Sebaiknya gunakan view terpisah, misal 'CplPl/Edit'
            'pageSettings' => fn() => [
                'title' => 'Edit Pemetaan CPL-MK',
                'subtitle' => 'Ubah pemetaan untuk MK yang dipilih',
                'method' => 'PUT',
                'action' => route('cpl-mk.update', $mk->id),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-MK', 'href' => route('cpl-mk.index')],
                ['label' => 'Edit Pemetaan'],
            ],
            // Data pemetaan yang akan diedit
            'mapping' => [
                'mk_id' => $mk->id,
                'cpl_ids' => $mk->programLearningOutcomes->pluck('id'), // Ambil ID dari BK yang sudah terhubung
            ],
            // Data untuk mengisi pilihan dropdown
            'cpls' => fn() => $cpls,
            'mks' => fn() => $mks,
        ]);
    }

    public function update(CplMkRequest $request, Course $mk): RedirectResponse
    {
        try {
            // `sync()` adalah method yang sempurna untuk update.
            // Ia akan menghapus relasi lama dan menggantinya dengan yang baru dari request.
            $cplIds = collect($request->cpl_ids)->pluck('value');
            $mk->programLearningOutcomes()->sync($cplIds);

            flashMessage(MessageType::UPDATED->message('Pemetaan CPL-MK'));
            return to_route('cpl-mk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-mk.index');
        }
    }

    public function destroy(Course $mk): RedirectResponse
    {
        try {
            // Gunakan method `detach()` pada relasi untuk menghapus semua
            // record yang terkait dengan CPL ini di tabel pivot `cpl_pl`.
            $mk->programLearningOutcomes()->detach();

            flashMessage(MessageType::DELETED->message('Pemetaan CPL-MK'));
            return to_route('cpl-mk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-mk.index');
        }
    }
}
