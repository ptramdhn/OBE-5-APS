<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\GraduateProfileRequest;
use App\Http\Resources\GraduateProfileResource;
use App\Models\GraduateProfile;
use App\Models\ProgramStudy;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Response;
use Throwable;

class GraduateProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('can:update,graduateProfile', only: (['edit', 'update'])),
            new Middleware('can:delete,graduateProfile', only: (['destroy'])),
        ];
    }

    public function index(): Response
    {
        $user = auth()->user();

        $query = GraduateProfile::query()
            ->select(['id', 'prodi_id', 'code', 'description', 'created_at']);

        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $query->where('prodi_id', $user->prodi_id);
        }

        $graduateProfiles = $query
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->latest('created_at') 
            ->with(['programStudy'])
            ->paginate(request()->load ?? 10);

        return inertia('PL/Index', [
            'pageSettings' => fn() => [
                'title' => 'Profil Lulusan',
                'subtitle' => 'Menampilkan semua data profil lulusan yang sudah terdaftar dalam sistem OBE',
            ],
            'graduateProfiles' => fn() => GraduateProfileResource::collection($graduateProfiles)->additional([
                'meta' => [
                    'has_pages' => $graduateProfiles->hasPages(),
                ],
            ]),
            'state' => fn() => [
                'page' =>request()->page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10, 
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Profil Lulusan'],
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

        return inertia('PL/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah profil lulusan',
                'subtitle' => 'Buat profil lulusan baru disini. Klik simpan setelah selesai',
                'method' => 'POST',
                'action' => route('graduate-profiles.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Profil Lulusan', 'href' => route('graduate-profiles.index')],
                ['label' => 'Tambah Profil Lulusan'],
            ],
            'programStudies' => fn() => $programStudies,
        ]);
    }

    public function store(GraduateProfileRequest $request)
    {
        try{
            GraduateProfile::create([
                'prodi_id' => $request->prodi_id,
                'code' => $request->code,
                'description' => $request->description,
            ]);

            flashMessage(MessageType::CREATED->message('Profil Lulusan'));
            return to_route('graduate-profiles.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('graduate-profiles.index');
        }
    }

    public function edit(GraduateProfile $graduateProfile)
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

        return inertia('PL/Edit', [
            'pageSettings' => fn() => [
                'title' => 'Edit profil lulusan',
                'subtitle' => 'Buat profil lulusan baru disini. Klik simpan setelah selesai',
                'method' => 'PUT',
                'action' => route('graduate-profiles.update', $graduateProfile),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Profil Lulusan', 'href' => route('graduate-profiles.index')],
                ['label' => 'Edit Profil Lulusan'],
            ],
            'graduateProfile' => [
                'id' => $graduateProfile->id,
                'prodi_id' => $graduateProfile->prodi_id,
                'code' => $graduateProfile->code,
                'description' => $graduateProfile->description,
            ],
            'programStudies' => fn() => $programStudies,
        ]);
    }

    public function update(GraduateProfileRequest $request, GraduateProfile $graduateProfile)
    {
        try{
            $graduateProfile->update([
                'prodi_id' => $request->prodi_id,
                'code' => $request->code,
                'description' => $request->description,
            ]);

            flashMessage(MessageType::UPDATED->message('Profil Lulusan'));
            return to_route('graduate-profiles.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('graduate-profiles.index');
        }
    }

    public function destroy(GraduateProfile $graduateProfile)
    {
        try{
            $graduateProfile->delete();

            flashMessage(MessageType::DELETED->message('Profil Lulusan'));
            return to_route('graduate-profiles.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('graduate-profiles.index');
        }
    }
}
