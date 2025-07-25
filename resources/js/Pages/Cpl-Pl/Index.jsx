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
    const { data: cpls, meta, links } = props.cpls;
    const [params, setParams] = useState(props.state);
    const { flash_message, auth } = usePage().props;

    const isSuperAdmin = auth.user?.role_id === 'Super Admin';

    useEffect(() => {
        if (flash_message?.message) {
            toast[flash_message.type ?? 'success'](flash_message.message);
        }
    }, [flash_message]);

    const onSortable = (field) => {
        setParams({
            ...params,
            field: field,
            direction: params.direction === 'asc' ? 'desc' : 'asc',
        });
    };

    UseFilter({
        route: route('cpl-profiles.index'),
        values: params,
        only: ['cpls'],
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
                                <Link href={route('cpl-profiles.create')}>
                                    <IconPlus className="size-4" />
                                    Tambah
                                </Link>
                            </Button>
                        </div>
                        <Filter params={params} setParams={setParams} state={props.state} />
                        <ShowFilter params={params} />
                    </CardHeader>
                    <CardContent className="p-0 [&-td]:whitespace-nowrap [&-td]:px-6 [&-th]:px-6">
                        {cpls.length === 0 ? (
                            <EmptyState
                                icon={IconArrowsSplit}
                                title="Tidak ada pemetaan CPL-PL"
                                subtitle="Mulailah dengan membuat pemetaan CPL-PL baru"
                            />
                        ) : (
                            <Table className="w-full">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>#</TableHead>
                                        {isSuperAdmin && <TableHead>Prodi</TableHead>}
                                        <TableHead>Kode CPL</TableHead>
                                        <TableHead>PL Terhubung</TableHead> {/* <-- 1. TAMBAH HEADER BARU */}
                                        <TableHead>Aksi</TableHead> {/* Asumsi ada kolom Aksi */}
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {cpls.map((cpl, index) => (
                                        <TableRow key={cpl.id}>
                                            <TableCell>{index + 1 + (meta.current_page - 1) * meta.per_page}</TableCell>
                                            {isSuperAdmin && <TableCell>{cpl.prodi?.name}</TableCell>}
                                            <TableCell>
                                                <Tooltip>
                                                    <TooltipTrigger className="cursor-default">
                                                        {cpl.code}
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p>{cpl.description}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TableCell>

                                            {/* 2. TAMBAH CELL BARU UNTUK MENAMPILKAN PEMETAAN */}
                                            <TableCell>
                                                {/* Cek apakah ada PL yang terhubung */}
                                                {cpl.graduate_profiles && cpl.graduate_profiles.length > 0 ? (
                                                    <div className="flex flex-wrap gap-1">
                                                        {/* Lakukan perulangan dan tampilkan sebagai Badge */}
                                                        {cpl.graduate_profiles.map((pl) => (
                                                            <Tooltip key={pl.id}>
                                                                <TooltipTrigger>
                                                                    <Badge variant="secondary">{pl.code}</Badge>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <p>{pl.description}</p>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        ))}
                                                    </div>
                                                ) : (
                                                    // Tampilkan pesan jika tidak ada pemetaan
                                                    <span className="text-xs text-muted-foreground">
                                                        Belum dipetakan
                                                    </span>
                                                )}
                                            </TableCell>

                                            <TableCell>
                                                <div className="flex items-center gap-x-1">
                                                    <Button variant="blue" size="sm" asChild>
                                                        <Link href={route('cpl-profiles.edit', [cpl])}>
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
                                                            deleteAction(route('cpl-profiles.destroy', [cpl]))
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
                            Menampilkan <span className="font-medium text-emerald-600">{meta.from ?? 0}</span> dari{' '}
                            {meta.total} CPL-PL
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
