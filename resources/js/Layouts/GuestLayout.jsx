import { Head } from '@inertiajs/react';

export default function GuestLayout({ children, title }) {
    return (
        <>
            <Head title={title} />
            <div className="flex min-h-svh flex-col items-center justify-center bg-slate-100 p-6 dark:bg-background md:p-10">
                <div className="w-full max-w-sm md:max-w-3xl">{children}</div>
            </div>
        </>
    );
}
