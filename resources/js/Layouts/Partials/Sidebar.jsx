import ApplicationLogo from '@/Components/ApplicationLogo';
import NavLink from '@/Components/NavLink';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { Card, CardContent } from '@/Components/ui/card';
import {
    IconArrowsSplit,
    IconAward,
    IconBook2,
    IconChartBar,
    IconChecks,
    IconLayoutDashboard,
    IconLogout2,
    IconNotebook,
    IconReportAnalytics, // Mengganti IconBox
    IconSchool,
    IconUsers,
} from '@tabler/icons-react';

export default function Sidebar({ auth, url }) {
    return (
        <nav className="flex flex-1 flex-col gap-y-6 py-6">
            <ApplicationLogo url="#" />
            <Card>
                <CardContent className="flex items-center gap-x-3 p-3">
                    <Avatar>
                        <AvatarImage src="#" />
                        <AvatarFallback>{auth.name ? auth.name.substring(0, 1) : '?'}</AvatarFallback>{' '}
                    </Avatar>
                    <div className="flex min-w-0 flex-grow flex-col">
                        {' '}
                        <span className="line-clamp-1 text-base font-semibold leading-relaxed tracking-tight text-slate-800 dark:text-slate-100">
                            {auth.name}
                        </span>
                        <div className="mt-0.5 flex flex-col">
                            {' '}
                            <span className="line-clamp-1 text-xs font-medium text-blue-600 dark:text-blue-400">
                                {auth.role_id || 'Role Tidak Diketahui'}
                            </span>
                            <span className="line-clamp-1 flex-grow overflow-hidden text-ellipsis whitespace-nowrap text-xs font-light text-muted-foreground">
                                {auth.id}
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <ul role="list" className="flex flex-1 flex-col gap-y-2">
                {/* GENERAL */}
                <div className="px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-300">General</div>
                <NavLink url="#" active={url.startsWith('/dashboard')} title="Dashboard" icon={IconLayoutDashboard} />

                {/* ADMINISTRASI SISTEM */}
                <div className="px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-300">
                    Administrasi Sistem
                </div>
                <NavLink
                    url={route('program-studies.index')}
                    active={url.startsWith('/program-studies')}
                    title="Program Studi"
                    icon={IconSchool}
                />
                <NavLink
                    url={route('users.index')}
                    active={url.startsWith('/users')}
                    title="Pengguna"
                    icon={IconUsers}
                />

                {/* DATA UTAMA OBE */}
                <div className="px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-300">Data Utama OBE</div>
                <NavLink
                    url={route('graduate-profiles.index')}
                    active={url.startsWith('/graduate-profiles')}
                    title="Profil Lulusan (PL)"
                    icon={IconAward}
                />
                <NavLink
                    url={route('program-learning-outcomes.index')}
                    active={url.startsWith('/program-learning-outcomes')}
                    title="Capaian Lulusan (CPL)"
                    icon={IconChecks}
                />
                <NavLink
                    url={route('study-materials.index')}
                    active={url.startsWith('/study-materials')}
                    title="Bahan Kajian (BK)"
                    icon={IconBook2}
                />
                <NavLink
                    url={route('courses.index')}
                    active={url.startsWith('/courses')}
                    title="Mata Kuliah (MK)"
                    icon={IconNotebook}
                />
                <NavLink
                    url={route('course-learning-outcomes.index')}
                    active={url.startsWith('/course-learning-outcomes')}
                    title="Capaian Pembelajaran MK (CPMK)"
                    icon={IconChartBar}
                />

                {/* MATRIKS RELASI */}
                <div className="px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-300">Matriks Relasi</div>
                <NavLink
                    url={route('cpl-profiles.index')}
                    active={url.startsWith('/cpl-profiles')}
                    title="CPL - PL"
                    icon={IconArrowsSplit}
                />
                <NavLink
                    url={route('cpl-bk.index')}
                    active={url.startsWith('/cpl-bk')}
                    title="CPL - BK"
                    icon={IconArrowsSplit}
                />
                <NavLink
                    url={route('bk-mk.index')}
                    active={url.startsWith('/bk-mk')}
                    title="BK - MK"
                    icon={IconArrowsSplit}
                />
                <NavLink
                    url={route('cpl-mk.index')}
                    active={url.startsWith('/cpl-mk')}
                    title="CPL - MK"
                    icon={IconArrowsSplit}
                />
                <NavLink
                    url={route('cpl-cpmk.index')}
                    active={url.startsWith('/cpl-cpmk')}
                    title="CPL - CPMK"
                    icon={IconArrowsSplit}
                />

                {/* ANALISIS & LAPORAN CAPAIAN */}
                <div className="px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-300">
                    Analisis & Laporan Capaian
                </div>
                <NavLink
                    url={route('reports.cpl-bk-mk')}
                    active={url.startsWith('/reports/cpl-bk-mk')}
                    title="Laporan CPL-BK-MK"
                    icon={IconReportAnalytics}
                />
                <NavLink
                    url={route('reports.cpl-cpmk-mk')}
                    active={url.startsWith('/reports/cpl-cpmk-mk')}
                    title="Laporan CPL-CPMK-MK"
                    icon={IconReportAnalytics}
                />

                {/* LAINNYA */}
                <div className="px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-300">Lainnya</div>
                <NavLink
                    as="button"
                    method="post"
                    url={route('logout')}
                    active={url.startsWith('/logout')}
                    title="Logout"
                    icon={IconLogout2}
                    className="w-full"
                />
            </ul>
        </nav>
    );
}
