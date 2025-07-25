<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Http\Requests\ProgramStudyRequest;
use App\Http\Resources\ProgramStudyResource;
use App\Models\ProgramStudy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Response;
use Throwable;

class ProgramStudyController extends Controller
{
    // public static function middleware(): array
    // {
    //     return [
    //         new Middleware('auth'),
    //         new Middleware('can:update,programStudy', only: (['edit', 'update'])),
    //         new Middleware('can:delete,programStudy', only: (['destroy'])),
    //     ];
    // }

    public function index(): Response
    {
        $programStudies = ProgramStudy::query()
            ->select(['id', 'code', 'name', 'created_at'])
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->latest('created_at') 
            ->paginate(request()->load ?? 10);

        return inertia('Prodi/Index', [
            'pageSettings' => fn() => [
                'title' => 'Program Studi',
                'subtitle' => 'Menampilkan semua data program studi yang ada pada fakultas sains dan teknologi',
            ],
            'programStudies' => fn() => ProgramStudyResource::collection($programStudies)->additional([
                'meta' => [
                    'has_pages' => $programStudies->hasPages(),
                ],
            ]),
            'state' => fn() => [
                'page' =>request()->page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10, 
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Program Studi'],
            ], 
        ]);
    }

    public function create(): Response
    {
        return inertia('Prodi/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah program studi',
                'subtitle' => 'Buat program studi baru disini. Klik simpan setelah selesai',
                'method' => 'POST',
                'action' => route('program-studies.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Program Studi', 'href' => route('program-studies.index')],
                ['label' => 'Tambah Program Studi'],
            ],
        ]);
    }

    public function store(ProgramStudyRequest $request): RedirectResponse
    {
        try{
            ProgramStudy::create([
                'code' => $request->code,
                'name' => $request->name,
            ]);

            flashMessage(MessageType::CREATED->message('Program Studi'));
            return to_route('program-studies.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('program-studies.index');
        }
    }

    public function edit(ProgramStudy $programStudy)
    {
        return inertia('Prodi/Edit', [
            'pageSettings' => fn() => [
                'title' => 'Edit program studi',
                'subtitle' => 'Edit program studi disini. Klik disimpan ketika selesai',
                'method' => 'PUT',
                'action' => route('program-studies.update', $programStudy),
            ],
            'programStudy' => fn() => $programStudy,
            'items' => fn() => [
                ['label' => 'Cuan+',  'href' => route('dashboard')],
                ['label' => 'Program Studi', 'href' => route('program-studies.index')],
                ['label' => 'Edit Program Studi'],
            ],
        ]);
    }

    public function update(ProgramStudyRequest $request, ProgramStudy $programStudy)
    {
        try{
            $programStudy->update([
                'code' => $request->code,
                'name' => $request->name,
            ]);

            flashMessage(MessageType::UPDATED->message('Program Studi'));
            return to_route('program-studies.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('program-studies.index');
        }
    }

    public function destroy(ProgramStudy $programStudy)
    {
        try{
            $programStudy->delete();

            flashMessage(MessageType::DELETED->message('Program Studi'));
            return to_route('program-studies.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('program-studies.index');
        }
    }
}
