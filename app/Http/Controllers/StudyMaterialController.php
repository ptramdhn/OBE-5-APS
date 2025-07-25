<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\StudyMaterialRequest;
use App\Http\Resources\StudyMaterialResource;
use App\Models\ProgramStudy;
use App\Models\StudyMaterial;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Response;
use Throwable;

class StudyMaterialController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('can:update,studyMaterial', only: (['edit', 'update'])),
            new Middleware('can:delete,studyMaterial', only: (['destroy'])),
        ];
    }

    public function index(): Response
    {
        $user = auth()->user();

        $query = StudyMaterial::query()
            ->select(['id', 'prodi_id', 'code', 'description', 'created_at']);

        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $query->where('prodi_id', $user->prodi_id);
        }

        $studyMaterials = $query
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->latest('created_at') 
            ->with(['programStudy'])
            ->paginate(request()->load ?? 10);

        return inertia('BK/Index', [
            'pageSettings' => fn() => [
                'title' => 'Bahan Kajian',
                'subtitle' => 'Menampilkan semua data bahan kajian yang sudah terdaftar dalam sistem OBE',
            ],
            'studyMaterials' => fn() => StudyMaterialResource::collection($studyMaterials)->additional([
                'meta' => [
                    'has_pages' => $studyMaterials->hasPages(),
                ],
            ]),
            'state' => fn() => [
                'page' =>request()->page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10, 
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Bahan Kajian'],
            ], 
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $programStudiesQuery = ProgramStudy::query()
            ->select(['id', 'name']);

        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $programStudiesQuery->where('id', $user->prodi_id);
        }

        $programStudies = $programStudiesQuery->get()
            ->map(fn($item) => [
                'value' => $item->id, // ID sebagai value
                'label' => $item->name, // Nama sebagai label
            ]);

        return inertia('BK/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah bahan kajian',
                'subtitle' => 'Buat bahan kajian baru disini. Klik simpan setelah selesai',
                'method' => 'POST',
                'action' => route('study-materials.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Bahan Kajian', 'href' => route('study-materials.index')],
                ['label' => 'Tambah Bahan Kajian'],
            ],
            'programStudies' => fn() => $programStudies,
        ]);
    }

    public function store(StudyMaterialRequest $request)
    {
        try{
            StudyMaterial::create([
                'prodi_id' => $request->prodi_id,
                'code' => $request->code,
                'description' => $request->description,
            ]);

            flashMessage(MessageType::CREATED->message('Bahan Kajian'));
            return to_route('study-materials.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('study-materials.index');
        }
    }

    public function edit(StudyMaterial $studyMaterial)
    {
        $user = auth()->user();

        $programStudiesQuery = ProgramStudy::query()
            ->select(['id', 'name']);

        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $programStudiesQuery->where('id', $user->prodi_id);
        }

        $programStudies = $programStudiesQuery->get()
            ->map(fn($item) => [
                'value' => $item->id, // ID sebagai value
                'label' => $item->name, // Nama sebagai label
            ]);

        return inertia('BK/Edit', [
            'pageSettings' => fn() => [
                'title' => 'Edit bahan kajian',
                'subtitle' => 'Buat bahan kajian baru disini. Klik simpan setelah selesai',
                'method' => 'PUT',
                'action' => route('study-materials.update', $studyMaterial),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Bahan Kajian', 'href' => route('study-materials.index')],
                ['label' => 'Edit BK'],
            ],
            'studyMaterial' => [
                'id' => $studyMaterial->id,
                'prodi_id' => $studyMaterial->prodi_id,
                'code' => $studyMaterial->code,
                'description' => $studyMaterial->description,
            ],
            'programStudies' => fn() => $programStudies,
        ]);
    }

    public function update(StudyMaterialRequest $request, StudyMaterial $studyMaterial)
    {
        try{
            $studyMaterial->update([
                'prodi_id' => $request->prodi_id,
                'code' => $request->code,
                'description' => $request->description,
            ]);

            flashMessage(MessageType::UPDATED->message('Bahan Kajian'));
            return to_route('study-materials.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('study-materials.index');
        }
    }

    public function destroy(StudyMaterial $studyMaterial)
    {
        try{
            $studyMaterial->delete();

            flashMessage(MessageType::DELETED->message('Bahan Kajian'));
            return to_route('study-materials.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('study-materials.index');
        }
    }
}
