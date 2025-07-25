<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\CourseLearningOutcomeRequest;
use App\Http\Resources\CourseLearningOutcomeResource;
use App\Models\Course;
use App\Models\CourseLearningOutcome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Throwable;

class CourseLearningOutcomeController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Query utama sekarang ke model Cpmk
        $queryCpmks = CourseLearningOutcome::query();

        // Gunakan `whereHas` untuk memfilter berdasarkan `prodi_id` pada relasi `mataKuliah`
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $queryCpmks->whereHas('courses', function ($query) use ($user) {
                $query->where('prodi_id', $user->prodi_id);
            });
        }

        $cpmks = $queryCpmks
            ->with(['courses:id,id_mk,name']) // Eager load relasi MK yang relevan
            ->filter(request()->only(['search'])) // Pastikan scope ini ada di model Cpmk
            ->sorting(request()->only(['field', 'direction'])) // Pastikan scope ini ada di model Cpmk
            ->latest('created_at')
            ->paginate(request()->load ?? 10)
            ->withQueryString();

        return inertia('CPMK-MK/Index', [
            'pageSettings' => fn() => [
                'title' => 'CPMK-MK',
                'subtitle' => 'Menampilkan semua pemetaan CPMK-MK yang sudah terdaftar',
            ],
            // Gunakan CpmkResource
            'cpmks' => fn() => CourseLearningOutcomeResource::collection($cpmks),
            'state' => fn() => [
                'page'   => request()->page ?? 1,
                'search' => request()->search ?? '',
                'load'   => request()->load ?? 10,
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPMK-MK'],
            ],
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();

        // Query hanya untuk mengambil data MK
        $mksQuery = Course::query()->select(['id', 'prodi_id', 'id_mk', 'name']);

        // Terapkan filter prodi jika bukan Super Admin
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $mksQuery->where('prodi_id', '==', $user->prodi_id);
        }

        // Eksekusi query dan format data untuk dropdown
        $mks = $mksQuery->orderBy('id_mk')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->id_mk}) {$item->name}",
            'prodi_id' => $item->prodi_id,
        ]);

        return inertia('CPMK-MK/Create', [
            'pageSettings' => [
                'title' => 'Tambah CPMK & Pemetaannya',
                'subtitle' => 'Buat CPMK baru dan langsung petakan ke Mata Kuliah',
                'method' => 'POST',
                'action' => route('course-learning-outcomes.store'),
            ],
            'items' => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPMK-MK', 'href' => route('course-learning-outcomes.index')],
                ['label' => 'Tambah CPMK & Peta'],
            ],
            // Hanya kirim data MK ke frontend
            'mks' => $mks,
        ]);
    }

    public function store(CourseLearningOutcomeRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                // 1. Buat CPMK baru
                $cpmk = CourseLearningOutcome::create([
                    'code' => $request->code,
                    'description' => $request->description,
                ]);

                // 2. INI BAGIAN PENTINGNYA:
                // Kita harus ekstrak HANYA 'value' (yang berisi ID MK) dari array request.
                $mkIds = collect($request->mk_ids)->pluck('value');

                // 3. Berikan array ID sederhana ke `attach()`, bukan array berisi objek.
                $cpmk->courses()->attach($mkIds);
            });

            flashMessage(MessageType::CREATED->message('CPMK dan Pemetaannya'));
            return to_route('course-learning-outcomes.index');

        } catch (Throwable $e) {
            // Jika ada error, batalkan semua query yang sudah dijalankan
            DB::rollBack();

            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('course-learning-outcomes.index');
        }
    }

    public function edit(CourseLearningOutcome $cpmk): Response
    {
        $user = auth()->user();

        // Query untuk mengambil semua opsi CPMK
        $cpmksQuery = CourseLearningOutcome::query()->select(['id', 'code', 'description']);

        // Query untuk mengambil data MK
        $mksQuery = Course::query()->select(['id', 'prodi_id', 'id_mk', 'name']);

        // Filter prodi HANYA diterapkan pada query Mata Kuliah (MK)
        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $mksQuery->where('prodi_id', $user->prodi_id);
        }

        // Eksekusi dan format data untuk dropdown
        $cpmks = $cpmksQuery->orderBy('code')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->code}) {$item->description}",
            // Tidak ada prodi_id di sini
        ]);

        $mks = $mksQuery->orderBy('id_mk')->get()->map(fn($item) => [
            'value' => $item->id,
            'label' => "({$item->id_mk}) {$item->name}",
            'prodi_id' => $item->prodi_id, // prodi_id di MK tetap ada untuk filter frontend
        ]);

        return inertia('CPMK-MK/Edit', [
            'pageSettings' => fn() => [
                'title' => 'Edit Pemetaan CPMK-MK',
                'subtitle' => 'Ubah pemetaan untuk CPMK yang dipilih',
                'method' => 'PUT',
                'action' => route('course-learning-outcomes.update', $cpmk->id),
            ],
            'items' => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'CPMK-MK', 'href' => route('course-learning-outcomes.index')],
                ['label' => 'Edit CPMK & Peta'],
            ],
            'mapping' => [
                'cpmk_id' => $cpmk->id,
                'code'        => $cpmk->code,        // <-- TAMBAHKAN INI
                'description' => $cpmk->description, // <-- TAMBAHKAN INI
                'mk_ids' => $cpmk->courses->pluck('id'),
            ],
            'cpmks' => fn() => $cpmks,
            'mks' => fn() => $mks,
        ]);
    }

    public function update(CourseLearningOutcomeRequest $request, CourseLearningOutcome $cpmk): RedirectResponse
    {
        try {
            $mkIds = collect($request->mk_ids)->pluck('value');
            $cpmk->courses()->sync($mkIds);

            flashMessage(MessageType::UPDATED->message('Pemetaan CPMK-MK'));
            return to_route('course-learning-outcomes.index');

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('course-learning-outcomes.index');
        }
    }

    public function destroy(CourseLearningOutcome $cpmk): RedirectResponse
    {
        try {
            $cpmk->delete();

            flashMessage(MessageType::DELETED->message('Pemetaan CPMK-MK'));
            return to_route('course-learning-outcomes.index'); 

        } catch (Throwable $e) {
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('course-learning-outcomes.index'); 
        }
    }
}
