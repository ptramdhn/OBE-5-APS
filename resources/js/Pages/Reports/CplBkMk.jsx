import BreadcrumbHeader from '@/Components/BreadcrumbHeader';
import EmptyState from '@/Components/EmptyState';
import HeaderTitle from '@/Components/HeaderTitle';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import AppLayout from '@/Layouts/AppLayout';
import { IconReportAnalytics } from '@tabler/icons-react';

// 1. Ganti nama komponen agar sesuai dengan nama file (praktik terbaik)
export default function CplBkMk(props) {
    // Ambil data cpls (untuk kolom) dan bks (untuk baris) dari props
    const { cpls, bks } = props;

    return (
        <TooltipProvider>
            <div className="flex w-full flex-col gap-y-6 pb-32">
                <BreadcrumbHeader items={props.items} />
                <HeaderTitle
                    title={props.pageSettings.title}
                    subtitle={props.pageSettings.subtitle}
                    icon={IconReportAnalytics}
                />
                <Card>
                    <CardContent className="p-4">
                        {bks.length > 0 ? (
                            <div className="overflow-x-auto rounded-lg border">
                                <Table className="min-w-full [&_td]:whitespace-nowrap [&_th]:whitespace-nowrap">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="sticky left-0 z-10 min-w-[250px] bg-secondary">
                                                Bahan Kajian (BK)
                                            </TableHead>
                                            {cpls.map((cpl) => (
                                                <TableHead key={cpl.id} className="min-w-[150px] text-center">
                                                    <Tooltip>
                                                        <TooltipTrigger className="font-bold">
                                                            {cpl.code}
                                                        </TooltipTrigger>
                                                        <TooltipContent>{cpl.description}</TooltipContent>
                                                    </Tooltip>
                                                </TableHead>
                                            ))}
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {bks.map((bk) => (
                                            <TableRow key={bk.id}>
                                                <TableCell className="sticky left-0 z-10 bg-background font-medium">
                                                    ({bk.code}) {bk.description}
                                                </TableCell>
                                                {cpls.map((cpl) => (
                                                    <TableCell key={`${bk.id}-${cpl.id}`} className="text-center">
                                                        {bk.mappings[cpl.id] ? (
                                                            <div className="flex flex-col items-center justify-center gap-1">
                                                                {/* Loop objek MK dari mapping */}
                                                                {bk.mappings[cpl.id].map((mk) => (
                                                                    // Bungkus Badge dengan Tooltip
                                                                    <Tooltip key={mk.code}>
                                                                        <TooltipTrigger>
                                                                            <Badge variant="outline">{mk.code}</Badge>
                                                                        </TooltipTrigger>
                                                                        <TooltipContent>
                                                                            <p>{mk.name}</p>
                                                                        </TooltipContent>
                                                                    </Tooltip>
                                                                ))}
                                                            </div>
                                                        ) : (
                                                            <span className="text-gray-300">-</span>
                                                        )}
                                                    </TableCell>
                                                ))}
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <EmptyState
                                icon={IconReportAnalytics}
                                title="Tidak ada data laporan"
                                subtitle="Belum ada data pemetaan yang bisa ditampilkan."
                            />
                        )}
                    </CardContent>
                </Card>
            </div>
        </TooltipProvider>
    );
}

// Sesuaikan juga nama komponen di sini
CplBkMk.layout = (page) => <AppLayout children={page} title={page.props.pageSettings.title} />;
