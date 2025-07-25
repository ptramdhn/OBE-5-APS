<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\ProgramLearningOutcomeRequest;
use App\Http\Resources\ProgramLearningOutcomeResource;
use App\Models\ProgramLearningOutcome;
use App\Models\ProgramStudy;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Response;
use Throwable;

class ProgramLearningOutcomeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('can:update,programLearningOutcome', only: (['edit', 'update'])),
            new Middleware('can:delete,programLearningOutcome', only: (['destroy'])),
        ];
    }

    public function index(): Response
    {
        $user = auth()->user();

        $query = ProgramLearningOutcome::query()
            ->select(['id', 'prodi_id', 'code', 'description', 'created_at']);

        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $query->where('prodi_id', $user->prodi_id);
        }

        $programLearningOutcomes = $query
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->latest('created_at') 
            ->with(['programStudy'])
            ->paginate(request()->load ?? 10);

        return inertia('CPL/Index', [
            'pageSettings' => fn() => [
                'title' => 'Capaian Pembelajaran Lulusan',
                'subtitle' => 'Menampilkan semua data CPL yang sudah terdaftar dalam sistem OBE',
            ],
            'programLearningOutcomes' => fn() => ProgramLearningOutcomeResource::collection($programLearningOutcomes)->additional([
                'meta' => [
                    'has_pages' => $programLearningOutcomes->hasPages(),
                ],
            ]),
            'state' => fn() => [
                'page' =>request()->page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10, 
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Capaian Pembelajaran Lulusan'],
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

        return inertia('CPL/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah capaian pembelajaran lulusan',
                'subtitle' => 'Buat CPL baru disini. Klik simpan setelah selesai',
                'method' => 'POST',
                'action' => route('program-learning-outcomes.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Capaian Pembelajaran Lulusan', 'href' => route('program-learning-outcomes.index')],
                ['label' => 'Tambah CPL'],
            ],
            'programStudies' => fn() => $programStudies,
        ]);
    }

    public function store(ProgramLearningOutcomeRequest $request)
    {
        try{
            ProgramLearningOutcome::create([
                'prodi_id' => $request->prodi_id,
                'code' => $request->code,
                'description' => $request->description,
            ]);

            flashMessage(MessageType::CREATED->message('Capaian Pembelajaran Lulusan'));
            return to_route('program-learning-outcomes.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('program-learning-outcomes.index');
        }
    }

    public function edit(ProgramLearningOutcome $programLearningOutcome)
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

        return inertia('CPL/Edit', [
            'pageSettings' => fn() => [
                'title' => 'Edit capaian pembelajaran lulusan',
                'subtitle' => 'Buat CPL baru disini. Klik simpan setelah selesai',
                'method' => 'PUT',
                'action' => route('program-learning-outcomes.update', $programLearningOutcome),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Capaian Pembelajaran Lulusan', 'href' => route('program-learning-outcomes.index')],
                ['label' => 'Edit CPL'],
            ],
            'programLearningOutcome' => [
                'id' => $programLearningOutcome->id,
                'prodi_id' => $programLearningOutcome->prodi_id,
                'code' => $programLearningOutcome->code,
                'description' => $programLearningOutcome->description,
            ],
            'programStudies' => fn() => $programStudies,
        ]);
    }

    public function update(ProgramLearningOutcomeRequest $request, ProgramLearningOutcome $programLearningOutcome)
    {
        try{
            $programLearningOutcome->update([
                'prodi_id' => $request->prodi_id,
                'code' => $request->code,
                'description' => $request->description,
            ]);

            flashMessage(MessageType::UPDATED->message('Capaian Pembelajaran Lulusan'));
            return to_route('program-learning-outcomes.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('program-learning-outcomes.index');
        }
    }

    public function destroy(ProgramLearningOutcome $programLearningOutcome)
    {
        try{
            $programLearningOutcome->delete();

            flashMessage(MessageType::DELETED->message('Capaian Pembelajaran Lulusan'));
            return to_route('program-learning-outcomes.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('program-learning-outcomes.index');
        }
    }
}
