<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\CplBkRequest;
use App\Http\Resources\ProgramLearningOutcomeResource;
use App\Models\ProgramLearningOutcome;
use App\Models\StudyMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Throwable;

class CplBkController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $queryCpls = ProgramLearningOutcome::query()
            ->select(['id', 'prodi_id', 'code', 'description']);

        // $queryProfiles = GraduateProfile::query()
        //     ->select(['id', 'prodi_id', 'code', 'description']);

        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $queryCpls->where('prodi_id', $user->prodi_id);
            // $queryProfiles->where('prodi_id', $user->prodi_id);
        }

        $cpls = $queryCpls
            ->has('studyMaterials')
            ->with(['studyMaterials:id,code,description']) // Eager load relasi
            ->with(['programStudy']) // Eager load relasi
            ->filter(request()->only(['search'])) // <-- FIX 3: Pastikan scope ini ada
            ->sorting(request()->only(['field', 'direction'])) // <-- FIX 3: Pastikan scope ini ada
            ->latest('created_at')
            ->paginate(request()->load ?? 10)
            ->withQueryString();

        return inertia('CPL-BK/Index', [
            'pageSettings' => fn() => [
                'title' => 'CPL-BK',
                'subtitle' => 'Menampilkan semua pemetaan CPL-BK yang sudah terdaftar dalam sistem OBE',
            ],
            'cpls' => fn() => ProgramLearningOutcomeResource::collection($cpls)->additional([
                'meta' => [
                    'has_pages' => $cpls->hasPages(),
                ],
            ]),
            'state' => fn() => [
                'page' =>request()->page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10, 
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-BK'],
            ], 
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();

        // 1. Query untuk mengambil data CPL
        $cplsQuery = ProgramLearningOutcome::query()->select(['id', 'prodi_id', 'code', 'description']);

        // 2. Query untuk mengambil data PL
        $bksQuery = StudyMaterial::query()->select(['id', 'prodi_id', 'code', 'description']);

        // 3. Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $cplsQuery->where('prodi_id', $user->prodi_id);
            $bksQuery->where('prodi_id', $user->prodi_id);
        }
 
        // 4. Eksekusi query dan format data untuk dropdown
        $cpls = $cplsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id, 
        ]);

        $bks = $bksQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id, 
        ]);

        return inertia('CPL-BK/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah Pemetaan CPL-BK',
                'subtitle' => 'Pilih CPL dan petakan ke beberapa BK yang sesuai',
                'method' => 'POST',
                'action' => route('cpl-bk.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-BK', 'href' => route('cpl-bk.index')],
                ['label' => 'Tambah Pemetaan'],
            ],
            // Kirim data CPL dan PL ke frontend
            'cpls' => fn() => $cpls,
            'bks' => fn() => $bks,
        ]);
    }

    public function store(CplBkRequest $request): RedirectResponse
    {
        try {
            $cpl = ProgramLearningOutcome::findOrFail($request->cpl_id);
            $bkIds = collect($request->bk_ids)->pluck('value');
            // $cpl->graduateProfiles()->sync($plIds);
            $cpl->studyMaterials()->syncWithoutDetaching($bkIds);
            
            flashMessage(MessageType::CREATED->message('Pemetaan CPL-BK'));
            return to_route('cpl-bk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-bk.index');
        }
    }

    public function edit(ProgramLearningOutcome $cpl): Response
    {
        $user = auth()->user();

        // Query untuk mengambil semua opsi CPL dan PL
        $cplsQuery = ProgramLearningOutcome::query()->select(['id', 'prodi_id', 'code', 'description']);
        $bksQuery = StudyMaterial::query()->select(['id', 'prodi_id', 'code', 'description']);

        // Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $cplsQuery->where('prodi_id', $user->prodi_id);
            $bksQuery->where('prodi_id', $user->prodi_id);
        }

        // Eksekusi dan format data untuk dropdown
        $cpls = $cplsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        $bks = $bksQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        return inertia('CPL-BK/Edit', [ // Sebaiknya gunakan view terpisah, misal 'CplPl/Edit'
            'pageSettings' => fn() => [
                'title' => 'Edit Pemetaan CPL-BK',
                'subtitle' => 'Ubah pemetaan untuk CPL yang dipilih',
                'method' => 'PUT',
                'action' => route('cpl-bk.update', $cpl->id),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-BK', 'href' => route('cpl-bk.index')],
                ['label' => 'Edit Pemetaan'],
            ],
            // Data pemetaan yang akan diedit
            'mapping' => [
                'cpl_id' => $cpl->id,
                'bk_ids' => $cpl->studyMaterials->pluck('id'), // Ambil ID dari BK yang sudah terhubung
            ],
            // Data untuk mengisi pilihan dropdown
            'cpls' => fn() => $cpls,
            'bks' => fn() => $bks,
        ]);
    }

    public function update(CplBkRequest $request, ProgramLearningOutcome $cpl): RedirectResponse
    {
        try {
            // `sync()` adalah method yang sempurna untuk update.
            // Ia akan menghapus relasi lama dan menggantinya dengan yang baru dari request.
            $bkIds = collect($request->bk_ids)->pluck('value');
            $cpl->studyMaterials()->sync($bkIds);

            flashMessage(MessageType::UPDATED->message('Pemetaan CPL-BK'));
            return to_route('cpl-bk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-bk.index');
        }
    }

    public function destroy(ProgramLearningOutcome $cpl): RedirectResponse
    {
        try {
            // Gunakan method `detach()` pada relasi untuk menghapus semua
            // record yang terkait dengan CPL ini di tabel pivot `cpl_pl`.
            $cpl->studyMaterials()->detach();

            flashMessage(MessageType::DELETED->message('Pemetaan CPL-BK'));
            return to_route('cpl-bk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-bk.index');
        }
    }
}
