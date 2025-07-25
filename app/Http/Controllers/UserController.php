<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\ProgramStudy;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Hash;
use Inertia\Response;
use Throwable;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('can:update,user', only: (['edit', 'update'])),
            new Middleware('can:delete,user', only: (['destroy'])),
        ];
    }

    public function index(): Response
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'role_id', 'prodi_id', 'created_at'])
            ->where('id', '!=', auth()->id())
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->latest('created_at') 
            ->with(['role', 'programStudy'])
            ->paginate(request()->load ?? 10);

        return inertia('User/Index', [
            'pageSettings' => fn() => [
                'title' => 'Pengguna',
                'subtitle' => 'Menampilkan semua data pengguna yang sudah terdaftar dalam sistem OBE',
            ],
            'users' => fn() => UserResource::collection($users)->additional([
                'meta' => [
                    'has_pages' => $users->hasPages(),
                ],
            ]),
            'state' => fn() => [
                'page' =>request()->page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10, 
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Pengguna'],
            ], 
        ]);
    }

    public function create()
    {
        return inertia('User/Create', [
            'pageSettings' => fn() => [
                'title' => 'Tambah pengguna',
                'subtitle' => 'Buat pengguna baru disini. Klik simpan setelah selesai',
                'method' => 'POST',
                'action' => route('users.store'),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Pengguna', 'href' => route('users.index')],
                ['label' => 'Tambah Pengguna'],
            ],
            'roles' => fn() => Role::query()
                ->select(['id', 'name'])
                ->get()
                ->map(fn($item) => [
                    'value' => $item->id,
                    'label' => $item->name,
                ]),
            'programStudies' => function () {
                // Jika tidak ada role yang dipilih dari request, kembalikan array kosong.
                if (!request()->role) {
                    return [];
                }

                // Cari role berdasarkan ID yang dikirim dari frontend.
                $role = Role::find(request()->role);

                // Jika role ditemukan DAN namanya BUKAN 'Super Admin', kirim daftar prodi.
                // Gunakan strtolower() untuk perbandingan yang case-insensitive (lebih aman).
                if ($role && strtolower($role->name) !== 'super admin') {
                    return ProgramStudy::query()
                        ->select(['id', 'code', 'name'])
                        ->get()
                        ->map(fn($item) => [
                            'value' => $item->id,
                            'label' => $item->name,
                            'code' => $item->code,
                        ]);
                }

                // Jika role adalah 'Super Admin' atau tidak ditemukan, kembalikan array kosong.
                return [];
            },
            'state' => fn() => [
                'role' => request()->role ?? '',
            ],
        ]);
    }

    public function store(UserRequest $request)
    {
        try{
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role,
                'prodi_id' => $request->prodi_id,
            ]);

            flashMessage(MessageType::CREATED->message('Pengguna'));
            return to_route('users.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('users.index');
        }
    }

    public function edit(User $user)
    {
        // Ambil role pertama yang dimiliki user untuk menentukan state awal
        $currentUserRole = $user->role_id;

        // dd($currentUserRole);

        return inertia('User/Edit', [ 
            'pageSettings' => fn() => [
                'title' => 'Edit Pengguna',
                'subtitle' => 'Ubah data pengguna disini. Klik simpan setelah selesai.',
                'method' => 'PUT',
                'action' => route('users.update', $user),
            ],
            'items' => fn() => [
                ['label' => 'Sistem OBE', 'href' => route('dashboard')],
                ['label' => 'Pengguna', 'href' => route('users.index')],
                ['label' => 'Edit Pengguna'],
            ],
            // Kirim data user yang akan diedit ke frontend
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $currentUserRole,
                'prodi_id' => $user->prodi_id,
            ],
            'roles' => fn() => Role::query()
                ->select(['id', 'name'])
                ->get()
                ->map(fn($item) => [
                    'value' => $item->id,
                    'label' => $item->name,
                ]),
            'programStudies' => function () use ($currentUserRole) {
                // Cek apakah ada request filter `role` baru, jika tidak, gunakan role user saat ini
                $roleId = request()->role ?? ($currentUserRole ?? null);

                if (!$roleId) {
                    return [];
                }
                $role = Role::find($roleId);

                // Logika sama persis dengan method `create`
                if ($role && strtolower($role->name) !== 'super admin') {
                    return ProgramStudy::query()->select(['id', 'code', 'name'])->get()->map(fn($item) => [
                        'value' => $item->id,
                        'label' => $item->name,
                        'code' => $item->code,
                    ]);
                }
                return [];
            },
            'state' => fn() => [
                'role' => request()->role ?? '',
            ],
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        try{
            // 1. Ambil semua data yang sudah lolos validasi
            $validated = $request->validated();

            // 2. Handle password (jika ada, jika kosong hapus dari array)
            if (empty($validated['password'])) {
                unset($validated['password']);
            } else {
                $validated['password'] = Hash::make($validated['password']);
            }

            // 3. Mapping 'role' ke 'role_id' (INI BAGIAN KUNCINYA)
            // Buat key baru 'role_id' yang isinya sama dengan 'role'
            $validated['role_id'] = $validated['role'];
            // Hapus key 'role' yang lama agar tidak mengganggu proses update
            unset($validated['role']);

            // 4. Lakukan update dengan data yang key-nya sudah benar
            $user->update($validated);

            flashMessage(MessageType::UPDATED->message('Pengguna'));
            return to_route('users.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('users.index');
        }
    }

    public function destroy(User $user)
    {
        try{
            $user->delete();

            flashMessage(MessageType::DELETED->message('Pengguna'));
            return to_route('users.index');
        }catch (Throwable $e){
            flashMessage(MessageType::ERROR->message(error: $e->getMessage()), 'error');
            return to_route('users.index');
        }
    }
}
