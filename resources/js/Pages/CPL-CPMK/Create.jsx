import BreadcrumbHeader from '@/Components/BreadcrumbHeader';
import HeaderTitle from '@/Components/HeaderTitle';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { flashMessage } from '@/lib/utils';
import { Link, useForm } from '@inertiajs/react';
import { IconArrowBack, IconArrowsSplit, IconChecks, IconPlus, IconTrash } from '@tabler/icons-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

export default function Create(props) {
    // Sesuaikan nama props dari backend
    const { cpls, cpmks } = props;

    // State untuk menampung daftar CPMK yang sudah difilter
    const [filteredCpmks, setFilteredCpmks] = useState(cpmks);

    const { data, setData, errors, post, processing, reset } = useForm({
        cpl_id: '',
        cpmk_ids: [{ id: Date.now(), value: '' }],
    });

    // useEffect untuk memfilter daftar CPMK berdasarkan prodi dari CPL yang dipilih
    useEffect(() => {
        if (!data.cpl_id) {
            setFilteredCpmks(cpmks);
            return;
        }

        const selectedCpl = cpls.find((cpl) => cpl.value === data.cpl_id);
        const targetProdiId = selectedCpl?.prodi_id;

        const newFilteredCpmks = cpmks.filter((cpmk) => cpmk.prodi_id === targetProdiId);
        setFilteredCpmks(newFilteredCpmks);

        setData('cpmk_ids', [{ id: Date.now(), value: '' }]);
    }, [data.cpl_id]);

    // Handler untuk menambah baris dropdown CPMK
    const addCpmkRow = () => setData('cpmk_ids', [...data.cpmk_ids, { id: Date.now(), value: '' }]);
    // Handler untuk menghapus baris dropdown CPMK
    const removeCpmkRow = (id) =>
        setData(
            'cpmk_ids',
            data.cpmk_ids.filter((item) => item.id !== id),
        );
    // Handler untuk mengubah nilai pada dropdown CPMK tertentu
    const handleCpmkChange = (id, newValue) => {
        const updatedCpmkIds = data.cpmk_ids.map((item) => (item.id === id ? { ...item, value: newValue } : item));
        setData('cpmk_ids', updatedCpmkIds);
    };

    // Handler untuk submit form
    const onHandleSubmit = (e) => {
        e.preventDefault();
        post(props.pageSettings.action, {
            preserveScroll: true,
            onSuccess: (success) => {
                const flash = flashMessage(success);
                if (flash) toast[flash.type](flash.message);
                reset();
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
                            icon={IconArrowsSplit}
                        />
                        <Button variant="blue" size="xl" asChild>
                            <Link href={route('cpl-cpmk.index')}>
                                <IconArrowBack className="size-4" />
                                Kembali
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <form className="space-y-4" onSubmit={onHandleSubmit}>
                        {/* Dropdown CPL Utama */}
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="cpl_id" required>
                                Pilih CPL
                            </Label>
                            <Select value={data.cpl_id} onValueChange={(value) => setData('cpl_id', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih CPL yang akan dipetakan" />
                                </SelectTrigger>
                                <SelectContent>
                                    {cpls.map((cpl) => (
                                        <SelectItem key={cpl.value} value={cpl.value}>
                                            {cpl.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.cpl_id} />
                        </div>

                        <hr className="my-6 border-dashed" />

                        {/* Area Dropdown CPMK Dinamis */}
                        <div className="space-y-3">
                            <Label>Petakan ke Capaian Pembelajaran MK (CPMK) berikut:</Label>
                            {data.cpmk_ids.map((cpmkItem, index) => (
                                <div key={cpmkItem.id} className="flex w-full items-center gap-x-2">
                                    <div className="w-full">
                                        <Select
                                            value={cpmkItem.value}
                                            onValueChange={(value) => handleCpmkChange(cpmkItem.id, value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder={`Pilih CPMK #${index + 1}`} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {filteredCpmks.map((cpmk) => (
                                                    <SelectItem key={cpmk.value} value={cpmk.value}>
                                                        {cpmk.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors[`cpmk_ids.${index}.value`]} />
                                    </div>
                                    {data.cpmk_ids.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="icon"
                                            onClick={() => removeCpmkRow(cpmkItem.id)}
                                        >
                                            <IconTrash className="size-4" />
                                        </Button>
                                    )}
                                </div>
                            ))}
                        </div>

                        <Button type="button" variant="outline" size="sm" onClick={addCpmkRow} className="mt-2">
                            <IconPlus className="mr-2 size-4" />
                            Tambah Pilihan CPMK
                        </Button>

                        <div className="mt-8 flex flex-col gap-2 lg:flex-row lg:justify-end">
                            <Button type="button" variant="ghost" size="xl" onClick={() => reset()}>
                                Reset
                            </Button>
                            <Button type="submit" variant="blue" size="xl" disabled={processing}>
                                <IconChecks />
                                Simpan Pemetaan
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Create.layout = (page) => <AppLayout children={page} title={page.props.pageSettings.title} />;
