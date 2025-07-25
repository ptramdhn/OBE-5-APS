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
import { IconArrowBack, IconChecks, IconNotebook } from '@tabler/icons-react';
import { toast } from 'sonner';

export default function Edit(props) {
    const { data, setData, errors, post, processing, reset } = useForm({
        prodi_id: props.course?.prodi_id ?? null,
        id_mk: props.course?.id_mk ?? '',
        kode_mk: props.course?.kode_mk ?? '',
        name: props.course?.name ?? '',
        semester: props.course?.semester ?? '',
        sks: props.course?.sks ?? '',
        jenis_mk: props.course?.jenis_mk ?? '',
        kelompok_mk: props.course?.kelompok_mk ?? '',
        lingkup_kelas: props.course?.lingkup_kelas ?? '',
        mode_kuliah: props.course?.mode_kuliah ?? '',
        metode_pembelajaran: props.course?.metode_pembelajaran ?? '',
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
                            icon={IconNotebook}
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
                            <Label htmlFor="code">ID MK</Label>
                            <Input
                                type="text"
                                name="id_mk"
                                id="id_mk"
                                placeholder="Masukkan ID MK"
                                value={data.id_mk}
                                onChange={onHandleChange}
                            />
                            {errors.id_mk && <InputError message={errors.id_mk} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="kode_mk">Kode MK</Label>
                            <Input
                                type="text"
                                name="kode_mk"
                                id="kode_mk"
                                placeholder="Masukkan Kode MK"
                                value={data.kode_mk}
                                onChange={onHandleChange}
                            />
                            {errors.kode_mk && <InputError message={errors.kode_mk} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="name">Nama Mata Kuliah</Label>
                            <Input
                                type="text"
                                name="name"
                                id="name"
                                placeholder="Masukkan Nama Mata Kuliah"
                                value={data.name}
                                onChange={onHandleChange}
                            />
                            {errors.name && <InputError message={errors.name} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="semester">Semester</Label>
                            <Input
                                type="number"
                                name="semester"
                                id="semester"
                                placeholder="Masukkan Semester"
                                value={data.semester}
                                onChange={onHandleChange}
                            />
                            {errors.semester && <InputError message={errors.semester} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="sks">SKS</Label>
                            <Input
                                type="number"
                                name="sks"
                                id="sks"
                                placeholder="Masukkan SKS"
                                value={data.sks}
                                onChange={onHandleChange}
                            />
                            {errors.sks && <InputError message={errors.sks} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="jenis_mk">Jenis MK</Label>
                            <Input
                                type="text"
                                name="jenis_mk"
                                id="jenis_mk"
                                placeholder="Masukkan Jenis MK"
                                value={data.jenis_mk}
                                onChange={onHandleChange}
                            />
                            {errors.jenis_mk && <InputError message={errors.jenis_mk} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="kelompok_mk">Kelompok MK</Label>
                            <Input
                                type="text"
                                name="kelompok_mk"
                                id="kelompok_mk"
                                placeholder="Masukkan Kelompok MK"
                                value={data.kelompok_mk}
                                onChange={onHandleChange}
                            />
                            {errors.kelompok_mk && <InputError message={errors.kelompok_mk} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="lingkup_kelas">Lingkup Kelas</Label>
                            <Input
                                type="text"
                                name="lingkup_kelas"
                                id="lingkup_kelas"
                                placeholder="Masukkan Lingkup Kelas"
                                value={data.lingkup_kelas}
                                onChange={onHandleChange}
                            />
                            {errors.lingkup_kelas && <InputError message={errors.lingkup_kelas} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="kode_mk">Mode Kuliah</Label>
                            <Input
                                type="text"
                                name="mode_kuliah"
                                id="mode_kuliah"
                                placeholder="Masukkan Mode Kuliah"
                                value={data.mode_kuliah}
                                onChange={onHandleChange}
                            />
                            {errors.mode_kuliah && <InputError message={errors.mode_kuliah} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="metode_pembelajaran">Metode Pembelajaran</Label>
                            <Input
                                type="text"
                                name="metode_pembelajaran"
                                id="metode_pembelajaran"
                                placeholder="Masukkan Metode Pembelajaran"
                                value={data.metode_pembelajaran}
                                onChange={onHandleChange}
                            />
                            {errors.metode_pembelajaran && <InputError message={errors.metode_pembelajaran} />}
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
