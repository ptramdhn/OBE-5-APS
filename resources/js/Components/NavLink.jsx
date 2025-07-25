import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';

export default function NavLink({ active = false, url = '#', title, icon: Icon, ...props }) {
    return (
        <Link
            {...props}
            href={url}
            className={cn(
                active
                    ? 'bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-400 font-medium text-white'
                    : 'hover:bg-blue-100 dark:hover:bg-blue-950',
                'flex items-center gap-3 rounded-lg p-2.5 transition-all dark:text-white',
            )}
        >
            <Icon className="size-5" />
            {title}
        </Link>
    );
}
