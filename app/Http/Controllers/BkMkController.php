<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\BkMkRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\StudyMaterialResource;
use App\Models\Course;
use App\Models\StudyMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Throwable;

class BkMkController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Query utama sekarang ke model StudyMaterial (BK)
        $queryBks = StudyMaterial::query();

        // Terapkan filter prodi jika user bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $queryBks->where('prodi_id', $user->prodi_id);
        }

        $bks = $queryBks
            // Eager load relasi ke Mata Kuliah (MK)
            ->has('courses')
            ->with(['courses:id,id_mk,name', 'programStudy:id,name']) 
            ->filter(request()->only(['search'])) // Pastikan scope ini ada di model StudyMaterial
            ->sorting(request()->only(['field', 'direction'])) // Pastikan scope ini ada
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('BK-MK/Index', [
            'pageSettings' => [
                'title' => 'Pemetaan BK-MK',
                'subtitle' => 'Menampilkan semua pemetaan Bahan Kajian ke Mata Kuliah',
            ],
            // Kirim data 'bks' ke frontend
            'bks' => StudyMaterialResource::collection($bks),
            'items' => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'BK-MK'],
            ],
            'state' => fn() => [
                'page' =>request()->page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10, 
            ],
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();

        // 1. Query untuk mengambil data Bahan Kajian (BK)
        $bksQuery = StudyMaterial::query()->select(['id', 'prodi_id', 'code', 'description']);

        // 2. Query untuk mengambil data Mata Kuliah (MK)
        $mksQuery = Course::query()->select(['id', 'prodi_id', 'id_mk', 'name']);

        // 3. Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $bksQuery->where('prodi_id', $user->prodi_id);
            $mksQuery->where('prodi_id', $user->prodi_id);
        }

        // 4. Eksekusi query dan format data untuk dropdown
        $bks = $bksQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        $mks = $mksQuery->orderBy('id_mk')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->id_mk}) {$item->name}",
            'prodi_id' => $item->prodi_id,
        ]);

        return inertia('BK-MK/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah Pemetaan BK-MK',
                'subtitle' => 'Pilih BK dan petakan ke beberapa MK yang sesuai',
                'action' => route('bk-mk.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'BK-MK', 'href' => route('bk-mk.index')],
                ['label' => 'Tambah Pemetaan'],
            ],
            // Kirim data BK dan MK ke frontend
            'bks' => fn() => $bks,
            'mks' => fn() => $mks,
        ]);
    }

    public function store(BkMkRequest $request): RedirectResponse
    {
        try {
            $bk = StudyMaterial::findOrFail($request->bk_id);
            $mkIds = collect($request->mk_ids)->pluck('value');
            
            // Gunakan nama relasi yang sesuai di model StudyMaterial
            $bk->courses()->syncWithoutDetaching($mkIds);
            
            flashMessage(MessageType::CREATED->message('Pemetaan BK-MK'));
            return to_route('bk-mk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('bk-mk.index');
        }
    }

    public function edit(StudyMaterial $bk): Response
    {
        $user = auth()->user();

        // Query untuk mengambil semua opsi BK dan MK
        $bksQuery = StudyMaterial::query()->select(['id', 'prodi_id', 'code', 'description']);
        $mksQuery = Course::query()->select(['id', 'prodi_id', 'id_mk', 'name']);

        // Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $bksQuery->where('prodi_id', $user->prodi_id);
            $mksQuery->where('prodi_id', $user->prodi_id);
        }

        // Eksekusi dan format data untuk dropdown
        $bks = $bksQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            'prodi_id' => $item->prodi_id,
        ]);

        $mks = $mksQuery->orderBy('id_mk')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->id_mk}) {$item->name}",
            'prodi_id' => $item->prodi_id,
        ]);

        return inertia('BK-MK/Edit', [
            'pageSettings' => fn() => [
                'title' => 'Edit Pemetaan BK-MK',
                'subtitle' => 'Ubah pemetaan untuk Bahan Kajian yang dipilih',
                'method' => 'PUT',
                'action' => route('bk-mk.update', $bk->id),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'BK-MK', 'href' => route('bk-mk.index')],
                ['label' => 'Edit Pemetaan'],
            ],
            // Data pemetaan yang akan diedit
            'mapping' => [
                'bk_id' => $bk->id,
                'mk_ids' => $bk->courses->pluck('id'), // Ambil ID dari MK yang sudah terhubung
            ],
            // Data untuk mengisi pilihan dropdown
            'bks' => fn() => $bks,
            'mks' => fn() => $mks,
        ]);
    }

    public function update(BkMkRequest $request, StudyMaterial $bk): RedirectResponse
    {
        try {
            // `sync()` akan menghapus relasi lama dan menggantinya dengan yang baru.
            $mkIds = collect($request->mk_ids)->pluck('value');
            $bk->courses()->sync($mkIds);

            flashMessage(MessageType::UPDATED->message('Pemetaan BK-MK'));
            return to_route('bk-mk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('bk-mk.index');
        }
    }

    public function destroy(StudyMaterial $bk): RedirectResponse
    {
        try {
            // Gunakan method `detach()` pada relasi untuk menghapus semua
            // record yang terkait dengan CPL ini di tabel pivot `cpl_pl`.
            $bk->courses()->detach();

            flashMessage(MessageType::DELETED->message('Pemetaan BK-MK'));
            return to_route('bk-mk.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('bk-mk.index');
        }
    }
}
