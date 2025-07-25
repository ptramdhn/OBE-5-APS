<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\CplPlRequest;
use App\Http\Resources\ProgramLearningOutcomeResource;
use App\Models\GraduateProfile;
use App\Models\ProgramLearningOutcome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Throwable;

class CplPlController extends Controller
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
            ->has('graduateProfiles')
            ->with(['graduateProfiles:id,code,description']) // Eager load relasi
            ->with(['programStudy']) // Eager load relasi
            ->filter(request()->only(['search'])) // <-- FIX 3: Pastikan scope ini ada
            ->sorting(request()->only(['field', 'direction'])) // <-- FIX 3: Pastikan scope ini ada
            ->latest('created_at')
            ->paginate(request()->load ?? 10)
            ->withQueryString();

        return inertia('Cpl-Pl/Index', [
            'pageSettings' => fn() => [
                'title' => 'CPL-PL',
                'subtitle' => 'Menampilkan semua pemetaan CPL-PL yang sudah terdaftar dalam sistem OBE',
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
                ['label' => 'CPL-PL'],
            ], 
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();

        // 1. Query untuk mengambil data CPL
        $cplsQuery = ProgramLearningOutcome::query()->select(['id', 'prodi_id', 'code', 'description']);

        // 2. Query untuk mengambil data PL
        $plsQuery = GraduateProfile::query()->select(['id', 'prodi_id', 'code', 'description']);

        // 3. Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $cplsQuery->where('prodi_id', $user->prodi_id);
            $plsQuery->where('prodi_id', $user->prodi_id);
        }
 
        // 4. Eksekusi query dan format data untuk dropdown
        $cpls = $cplsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id, 
        ]);

        $pls = $plsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id, 
        ]);

        return inertia('Cpl-Pl/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah Pemetaan CPL-PL',
                'subtitle' => 'Pilih CPL dan petakan ke beberapa PL yang sesuai',
                'method' => 'POST',
                'action' => route('cpl-profiles.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-PL', 'href' => route('cpl-profiles.index')],
                ['label' => 'Tambah Pemetaan'],
            ],
            // Kirim data CPL dan PL ke frontend
            'cpls' => fn() => $cpls,
            'pls' => fn() => $pls,
        ]);
    }

    public function store(CplPlRequest $request): RedirectResponse
    {
        try {
            $cpl = ProgramLearningOutcome::findOrFail($request->cpl_id);
            $plIds = collect($request->pl_ids)->pluck('value');
            // $cpl->graduateProfiles()->sync($plIds);
            $cpl->graduateProfiles()->syncWithoutDetaching($plIds);
            
            flashMessage(MessageType::CREATED->message('Pemetaan CPL-PL'));
            return to_route('cpl-profiles.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-profiles.index');
        }
    }
    
    public function edit(ProgramLearningOutcome $cpl): Response
    {
        $user = auth()->user();

        // Query untuk mengambil semua opsi CPL dan PL
        $cplsQuery = ProgramLearningOutcome::query()->select(['id', 'prodi_id', 'code', 'description']);
        $plsQuery = GraduateProfile::query()->select(['id', 'prodi_id', 'code', 'description']);

        // Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $cplsQuery->where('prodi_id', $user->prodi_id);
            $plsQuery->where('prodi_id', $user->prodi_id);
        }

        // Eksekusi dan format data untuk dropdown
        $cpls = $cplsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        $pls = $plsQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        return inertia('Cpl-Pl/Edit', [ // Sebaiknya gunakan view terpisah, misal 'CplPl/Edit'
            'pageSettings' => fn() => [
                'title' => 'Edit Pemetaan CPL-PL',
                'subtitle' => 'Ubah pemetaan untuk CPL yang dipilih',
                'method' => 'PUT',
                'action' => route('cpl-profiles.update', $cpl->id),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPL-PL', 'href' => route('cpl-profiles.index')],
                ['label' => 'Edit Pemetaan'],
            ],
            // Data pemetaan yang akan diedit
            'mapping' => [
                'cpl_id' => $cpl->id,
                'pl_ids' => $cpl->graduateProfiles->pluck('id'), // Ambil ID dari PL yang sudah terhubung
            ],
            // Data untuk mengisi pilihan dropdown
            'cpls' => fn() => $cpls,
            'pls' => fn() => $pls,
        ]);
    }

    public function update(CplPlRequest $request, ProgramLearningOutcome $cpl): RedirectResponse
    {
        try {
            // `sync()` adalah method yang sempurna untuk update.
            // Ia akan menghapus relasi lama dan menggantinya dengan yang baru dari request.
            $plIds = collect($request->pl_ids)->pluck('value');
            $cpl->graduateProfiles()->sync($plIds);

            flashMessage(MessageType::UPDATED->message('Pemetaan CPL-PL'));
            return to_route('cpl-profiles.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-profiles.index');
        }
    }

    public function destroy(ProgramLearningOutcome $cpl): RedirectResponse
    {
        try {
            // Gunakan method `detach()` pada relasi untuk menghapus semua
            // record yang terkait dengan CPL ini di tabel pivot `cpl_pl`.
            $cpl->graduateProfiles()->detach();

            flashMessage(MessageType::DELETED->message('Pemetaan CPL-PL'));
            return to_route('cpl-profiles.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('cpl-profiles.index');
        }
    }
}
