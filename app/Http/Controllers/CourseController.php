<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\UserRoleEnum;
use App\Http\Requests\CourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\ProgramStudy;
use Illuminate\Http\Request;
use Inertia\Response;
use Throwable;

class CourseController extends Controller
{
    public function index(): Response

    {
        $user = auth()->user();

        $query = Course::query()
            ->select(['id', 'prodi_id', 'id_mk', 'kode_mk', 'name', 'semester', 'sks', 'jenis_mk', 'kelompok_mk', 'lingkup_kelas', 'mode_kuliah', 'metode_pembelajaran', 'created_at']);

        if ($user && optional($user->role)->name !== UserRoleEnum::SUPER_ADMIN->value) {
            $query->where('prodi_id', $user->prodi_id);
        }

        $courses = $query
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->latest('created_at') 
            ->with(['programStudy'])
            ->paginate(request()->load ?? 10);

        return inertia('MK/Index', [
            'pageSettings' => fn() => [
                'title' => 'Mata Kuliah',
                'subtitle' => 'Menampilkan semua data mata kuliah yang sudah terdaftar dalam sistem OBE',
            ],
            'courses' => fn() => CourseResource::collection($courses)->additional([
                'meta' => [
                    'has_pages' => $courses->hasPages(),
                ],
            ]),
            'state' => fn() => [
                'page' =>request()->page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10, 
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Mata Kuliah'],
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

        return inertia('MK/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah mata kuliah',
                'subtitle' => 'Buat mata kuliah baru disini. Klik simpan setelah selesai',
                'method' => 'POST',
                'action' => route('courses.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Mata Kuliah', 'href' => route('courses.index')],
                ['label' => 'Tambah Mata Kuliah'],
            ],
            'programStudies' => fn() => $programStudies,
        ]);
    }

    public function store(CourseRequest $request)
    {
        try{
            Course::create([
                'prodi_id' => $request->prodi_id,
                'id_mk' => $request->id_mk,
                'kode_mk' => $request->kode_mk,
                'name' => $request->name,
                'semester' => $request->semester,
                'sks' => $request->sks,
                'jenis_mk' => $request->jenis_mk,
                'kelompok_mk' => $request->kelompok_mk,
                'lingkup_kelas' => $request->lingkup_kelas,
                'mode_kuliah' => $request->mode_kuliah,
                'metode_pembelajaran' => $request->metode_pembelajaran,
            ]);

            flashMessage(MessageType::CREATED->message('Mata Kuliah'));
            return to_route('courses.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('courses.index');
        }
    }

    public function edit(Course $course)
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

        return inertia('MK/Edit', [
            'pageSettings' => fn() => [
                'title' => 'Edit mata kuliah',
                'subtitle' => 'Buat mata kuliah baru disini. Klik simpan setelah selesai',
                'method' => 'PUT',
                'action' => route('courses.update', $course),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Mata Kuliah', 'href' => route('courses.index')],
                ['label' => 'Edit MK'],
            ],
            'course' => [
                'id' => $course->id,
                'prodi_id' => $course->prodi_id,
                'id_mk' => $course->id_mk,
                'kode_mk' => $course->kode_mk,
                'name' => $course->name,
                'semester' => $course->semester,
                'sks' => $course->sks,
                'jenis_mk' => $course->jenis_mk,
                'kelompok_mk' => $course->kelompok_mk,
                'lingkup_kelas' => $course->lingkup_kelas,
                'mode_kuliah' => $course->mode_kuliah,
                'metode_pembelajaran' => $course->metode_pembelajaran,
            ],
            'programStudies' => fn() => $programStudies,
        ]);
    }

    public function update(CourseRequest $request, Course $course)
    {
        try{
            $course->update([
                'prodi_id' => $request->prodi_id,
                'id_mk' => $request->id_mk,
                'kode_mk' => $request->kode_mk,
                'name' => $request->name,
                'semester' => $request->semester,
                'sks' => $request->sks,
                'jenis_mk' => $request->jenis_mk,
                'kelompok_mk' => $request->kelompok_mk,
                'lingkup_kelas' => $request->lingkup_kelas,
                'mode_kuliah' => $request->mode_kuliah,
                'metode_pembelajaran' => $request->metode_pembelajaran,
            ]);

            flashMessage(MessageType::UPDATED->message('Mata Kuliah'));
            return to_route('courses.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('courses.index');
        }
    }

   public function destroy(Course $course)
    {
        try{
            $course->delete();

            flashMessage(MessageType::DELETED->message('Mata Kuliah'));
            return to_route('courses.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('courses.index');
        }
    }
}
