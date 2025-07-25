import { Link } from '@inertiajs/react';

import { IconTargetArrow } from '@tabler/icons-react';

export default function ApplicationLogo({ url = '/' }) {
    return (
        <Link href={url} className="flex items-center gap-x-2">
            <div className="rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-400 p-2">
                <IconTargetArrow className="size-6 text-white" />
            </div>
            <span className="text-xl font-semibold leading-relaxed tracking-wide text-slate-800 dark:text-slate-200">
                Sistem <span className="text-blue-600 dark:text-blue-400">OBE</span>
            </span>
        </Link>
    );
}
