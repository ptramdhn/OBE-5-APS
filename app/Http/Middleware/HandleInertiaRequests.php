<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => function () use ($request) {
                    if (! $user = $request->user()) {
                        return null;
                    }

                    // Eager load relasi untuk efisiensi
                    $user->loadMissing('role', 'programStudy');

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role_id' => optional($user->role)->name,
                        'prodi_id' => optional($user->programStudy)->name,
                    ];
                },
            ],
            'flash_message' => fn () => [
                'type' => $request->session()->get(key: 'type'),
                'message' => $request->session()->get(key: 'message'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
