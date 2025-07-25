import BreadcrumbHeader from '@/Components/BreadcrumbHeader';
import HeaderTitle from '@/Components/HeaderTitle';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { flashMessage } from '@/lib/utils';
import { Link, useForm } from '@inertiajs/react';
import { IconArrowBack, IconBook2, IconChecks } from '@tabler/icons-react';
import { toast } from 'sonner';

export default function Edit(props) {
    const { data, setData, errors, post, processing, reset } = useForm({
        prodi_id: props.studyMaterial?.prodi_id ?? null,
        code: props.studyMaterial?.code ?? '',
        description: props.studyMaterial?.description ?? '',
        _method: props.pageSettings.method,
    });

    const onHandleChange = (e) => setData(e.target.name, e.target.value);

    const onHandleSubmit = (e) => {
        e.preventDefault();
        post(props.pageSettings.action, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (success) => {
                const flash = flashMessage(success);
                if (flash) toast[flash.type](flash.message);
            },
        });
    };

    return (
        <div className="flex w-full flex-col gap-y-6 pb-32">
            <BreadcrumbHeader items={props.items} />
            <Card>
                <CardHeader>
                    <div className="flex flex-col items-start justify-between gap-y-4 lg:flex-row lg:items-center">
                        <HeaderTitle
                            title={props.pageSettings.title}
                            subtitle={props.pageSettings.subtitle}
                            icon={IconBook2}
                        />
                        <Button variant="blue" size="xl" asChild>
                            <Link href={route('study-materials.index')}>
                                <IconArrowBack className="size-4" />
                                Kembali
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <form className="space-y-4" onSubmit={onHandleSubmit}>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="prodi_id">Program Studi</Label>
                            <Select defaultValue={data.prodi_id} onValueChange={(value) => setData('prodi_id', value)}>
                                <SelectTrigger>
                                    <SelectValue>
                                        {props.programStudies.find(
                                            (programStudy) => programStudy.value == data.prodi_id,
                                        )?.label ?? 'Pilih program studi'}
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    {props.programStudies.map((programStudy, index) => (
                                        <SelectItem key={index} value={programStudy.value}>
                                            {programStudy.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.prodi_id && <InputError message={errors.prodi_id} />}
                        </div>

                        <div className="flex flex-col gap-2">
                            <Label htmlFor="code">Kode BK</Label>
                            <Input
                                type="text"
                                name="code"
                                id="code"
                                placeholder="Masukkan Kode BK"
                                value={data.code}
                                onChange={onHandleChange}
                            />
                            {errors.code && <InputError message={errors.code} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="description">Deskripsi</Label>
                            <Input
                                type="text"
                                name="description"
                                id="description"
                                placeholder="Masukkan Deskripsi"
                                value={data.description}
                                onChange={onHandleChange}
                            />
                            {errors.description && <InputError message={errors.description} />}
                        </div>
                        <div className="mt-8 flex flex-col gap-2 lg:flex-row lg:justify-end">
                            <Button type="button" variant="ghost" size="xl" onClick={() => reset()}>
                                Reset
                            </Button>
                            <Button type="submit" variant="blue" size="xl" disabled={processing}>
                                <IconChecks />
                                Submit
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Edit.layout = (page) => <AppLayout children={page} title={page.props.pageSettings.title} />;
