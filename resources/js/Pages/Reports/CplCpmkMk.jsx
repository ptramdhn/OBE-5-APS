import BreadcrumbHeader from '@/Components/BreadcrumbHeader';
import EmptyState from '@/Components/EmptyState';
import HeaderTitle from '@/Components/HeaderTitle';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import AppLayout from '@/Layouts/AppLayout';
import { IconReportAnalytics } from '@tabler/icons-react';

export default function CplCpmkMk(props) {
    const { cpls } = props;

    return (
        <div className="flex w-full flex-col gap-y-6 pb-32">
            <BreadcrumbHeader items={props.items} />
            <HeaderTitle
                title={props.pageSettings.title}
                subtitle={props.pageSettings.subtitle}
                icon={IconReportAnalytics}
            />
            <Card>
                <CardContent className="p-6">
                    {cpls.length > 0 ? (
                        <Accordion type="single" collapsible className="w-full">
                            {/* Level 1: Loop untuk setiap CPL */}
                            {cpls.map((cpl) => (
                                <AccordionItem key={cpl.id} value={`cpl-${cpl.id}`}>
                                    <AccordionTrigger>
                                        <div className="flex flex-col items-start text-left">
                                            <span className="font-semibold text-blue-600">{cpl.code}</span>
                                            <p className="mt-1 text-sm font-normal text-muted-foreground">
                                                {cpl.description}
                                            </p>
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent>
                                        <div className="pl-6">
                                            {/* Level 2: Loop untuk setiap CPMK di dalam CPL */}
                                            {cpl.cpmks.map((cpmk) => (
                                                <div key={cpmk.id} className="border-l-2 py-3 pl-4">
                                                    <h4 className="font-semibold">{cpmk.code}</h4>
                                                    <p className="mb-2 text-sm text-muted-foreground">
                                                        {cpmk.description}
                                                    </p>
                                                    {/* Level 3: Loop untuk setiap MK di dalam CPMK */}
                                                    <div className="flex flex-wrap gap-2">
                                                        {cpmk.courses.map((course) => (
                                                            <Badge key={course.id} variant="outline">
                                                                {course.id_mk} - {course.name}
                                                            </Badge>
                                                        ))}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                            ))}
                        </Accordion>
                    ) : (
                        <EmptyState
                            icon={IconReportAnalytics}
                            title="Tidak ada data laporan"
                            subtitle="Belum ada CPL yang terhubung hingga ke Mata Kuliah."
                        />
                    )}
                </CardContent>
            </Card>
        </div>
    );
}

CplCpmkMk.layout = (page) => <AppLayout children={page} title={page.props.pageSettings.title} />;
