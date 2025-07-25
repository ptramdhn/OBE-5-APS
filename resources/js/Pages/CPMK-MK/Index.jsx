import AlertAction from '@/Components/AlertAction';
import BreadcrumbHeader from '@/Components/BreadcrumbHeader';
import Filter from '@/Components/Datatable/Filter';
import PaginationTable from '@/Components/Datatable/PaginationTable';
import ShowFilter from '@/Components/Datatable/ShowFilter';
import EmptyState from '@/Components/EmptyState';
import HeaderTitle from '@/Components/HeaderTitle';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { UseFilter } from '@/hooks/UseFilter';
import AppLayout from '@/Layouts/AppLayout';
import { deleteAction } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import { IconArrowsSplit, IconPencil, IconPlus, IconTrash } from '@tabler/icons-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

export default function Index(props) {
    // Sesuaikan nama prop dari 'cpls' menjadi 'cpmks'
    const { data: cpmks, meta, links } = props.cpmks;
    const [params, setParams] = useState(props.state);
    const { flash_message, auth } = usePage().props;

    const isSuperAdmin = auth.user?.role?.name === 'Super Admin'; // Cek berdasarkan nama role

    useEffect(() => {
        if (flash_message?.message) {
            toast[flash_message.type ?? 'success'](flash_message.message);
        }
    }, [flash_message]);

    UseFilter({
        route: route('course-learning-outcomes.index'), // Sesuaikan nama rute
        values: params,
        only: ['cpmks'], // Sesuaikan nama prop
    });

    return (
        <TooltipProvider>
            <div className="flex w-full flex-col gap-y-6 pb-32">
                <BreadcrumbHeader items={props.items} />
                <Card>
                    <CardHeader className="p-0">
                        <div className="flex flex-col items-start justify-between gap-y-4 p-4 lg:flex-row lg:items-center">
                            <HeaderTitle
                                title={props.pageSettings.title}
                                subtitle={props.pageSettings.subtitle}
                                icon={IconArrowsSplit}
                            />
                            <Button variant="blue" size="xl" asChild>
                                <Link href={route('course-learning-outcomes.create')}>
                                    {' '}
                                    {/* Sesuaikan nama rute */}
                                    <IconPlus className="size-4" />
                                    Tambah
                                </Link>
                            </Button>
                        </div>
                        <Filter params={params} setParams={setParams} state={props.state} />
                        <ShowFilter params={params} />
                    </CardHeader>
                    <CardContent className="p-0 [&-td]:whitespace-nowrap [&-td]:px-6 [&-th]:px-6">
                        {cpmks.length === 0 ? (
                            <EmptyState
                                icon={IconArrowsSplit}
                                title="Tidak ada pemetaan CPMK-MK"
                                subtitle="Mulailah dengan membuat pemetaan CPMK-MK baru"
                            />
                        ) : (
                            <Table className="w-full">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>#</TableHead>
                                        {isSuperAdmin && <TableHead>Prodi</TableHead>}
                                        <TableHead>Kode CPMK</TableHead>
                                        <TableHead>MK Terhubung</TableHead>
                                        <TableHead>Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {cpmks.map((cpmk, index) => (
                                        <TableRow key={cpmk.id}>
                                            <TableCell>{meta.from + index}</TableCell>
                                            {isSuperAdmin && <TableCell>{cpmk.prodi?.name}</TableCell>}
                                            <TableCell>
                                                <Tooltip>
                                                    <TooltipTrigger className="cursor-default">
                                                        {cpmk.code}
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p>{cpmk.description}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TableCell>
                                            <TableCell>
                                                {cpmk.courses && cpmk.courses.length > 0 ? (
                                                    <div className="flex flex-wrap gap-1">
                                                        {cpmk.courses.map((mk) => (
                                                            <Tooltip key={mk.id}>
                                                                <TooltipTrigger>
                                                                    <Badge variant="secondary">{mk.id_mk}</Badge>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <p>{mk.name}</p> {/* Asumsi MK punya 'name' */}
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        ))}
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">
                                                        Belum dipetakan
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-x-1">
                                                    <Button variant="blue" size="sm" asChild>
                                                        <Link href={route('course-learning-outcomes.edit', cpmk.id)}>
                                                            {' '}
                                                            {/* Sesuaikan nama rute */}
                                                            <IconPencil className="size-4" />
                                                        </Link>
                                                    </Button>
                                                    <AlertAction
                                                        trigger={
                                                            <Button variant="red" size="sm">
                                                                <IconTrash className="size-4" />
                                                            </Button>
                                                        }
                                                        action={() =>
                                                            deleteAction(
                                                                route('course-learning-outcomes.destroy', [cpmk.id]),
                                                            )
                                                        }
                                                    />
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                    <CardFooter className="flex w-full flex-col items-center justify-between gap-y-2 border-t py-3 lg:flex-row">
                        <p className="text-sm text-muted-foreground">
                            Menampilkan <span className="font-medium text-emerald-600">{meta.from ?? 0}</span> sampai{' '}
                            <span className="font-medium text-emerald-600">{meta.to ?? 0}</span> dari {meta.total}{' '}
                            CPMK-MK
                        </p>
                        <div className="overflow-x-auto">
                            {meta.has_pages && <PaginationTable meta={meta} links={links} />}
                        </div>
                    </CardFooter>
                </Card>
            </div>
        </TooltipProvider>
    );
}

Index.layout = (page) => <AppLayout title={page.props.pageSettings.title} children={page} />;
