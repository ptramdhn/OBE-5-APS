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
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

export default function Edit(props) {
    // Sesuaikan nama props
    const { bks, mks, mapping } = props;

    // --- Inisialisasi State Awal ---
    // 1. Hitung daftar MK yang sudah difilter saat pertama kali render
    const initialBk = bks.find((bk) => bk.value === mapping.bk_id);
    const initialProdiId = initialBk?.prodi_id;
    const initialFilteredMks = mks.filter((mk) => mk.prodi_id === initialProdiId);

    // 2. Gunakan hasil filter untuk state awal
    const [filteredMks, setFilteredMks] = useState(initialFilteredMks);

    // 3. Isi useForm dengan data dari `mapping`
    const { data, setData, errors, post, processing, reset } = useForm({
        bk_id: mapping.bk_id,
        mk_ids:
            mapping.mk_ids && mapping.mk_ids.length > 0
                ? mapping.mk_ids.map((id) => ({ id: id, value: id }))
                : [{ id: Date.now(), value: '' }],
        _method: props.pageSettings.method,
    });

    const isInitialMount = useRef(true);

    // useEffect untuk memfilter ulang daftar MK jika pilihan BK diubah
    useEffect(() => {
        if (isInitialMount.current) {
            isInitialMount.current = false;
            return;
        }

        const selectedBk = bks.find((bk) => bk.value === data.bk_id);
        const targetProdiId = selectedBk?.prodi_id;
        const newFilteredMks = mks.filter((mk) => mk.prodi_id === targetProdiId);

        setFilteredMks(newFilteredMks);
        setData('mk_ids', [{ id: Date.now(), value: '' }]);
    }, [data.bk_id]);

    // Handler dinamis disesuaikan untuk MK
    const addMkRow = () => setData('mk_ids', [...data.mk_ids, { id: Date.now(), value: '' }]);
    const removeMkRow = (id) =>
        setData(
            'mk_ids',
            data.mk_ids.filter((item) => item.id !== id),
        );
    const handleMkChange = (id, newValue) => {
        const updatedMkIds = data.mk_ids.map((item) => (item.id === id ? { ...item, value: newValue } : item));
        setData('mk_ids', updatedMkIds);
    };

    // Handler submit menggunakan `put` untuk update
    const onHandleSubmit = (e) => {
        e.preventDefault();
        post(props.pageSettings.action, {
            preserveScroll: true,
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
                            icon={IconArrowsSplit}
                        />
                        <Button variant="blue" size="xl" asChild>
                            <Link href={route('bk-mk.index')}>
                                <IconArrowBack className="size-4" />
                                Kembali
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <form className="space-y-4" onSubmit={onHandleSubmit}>
                        {/* Dropdown BK Utama */}
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="bk_id" required>
                                Pilih Bahan Kajian (BK)
                            </Label>
                            <Select value={data.bk_id} onValueChange={(value) => setData('bk_id', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih BK yang akan dipetakan" />
                                </SelectTrigger>
                                <SelectContent>
                                    {bks.map((bk) => (
                                        <SelectItem key={bk.value} value={bk.value}>
                                            {bk.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.bk_id} />
                        </div>

                        <hr className="my-6 border-dashed" />

                        {/* Area Dropdown MK Dinamis */}
                        <div className="space-y-3">
                            <Label>Petakan ke Mata Kuliah (MK) berikut:</Label>
                            {data.mk_ids.map((mkItem, index) => (
                                <div key={mkItem.id} className="flex w-full items-center gap-x-2">
                                    <div className="w-full">
                                        <Select
                                            value={mkItem.value}
                                            onValueChange={(value) => handleMkChange(mkItem.id, value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder={`Pilih MK #${index + 1}`} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {/* Gunakan state `filteredMks` untuk opsi */}
                                                {filteredMks.map((mk) => (
                                                    <SelectItem key={mk.value} value={mk.value}>
                                                        {mk.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors[`mk_ids.${index}.value`]} />
                                    </div>
                                    {data.mk_ids.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="icon"
                                            onClick={() => removeMkRow(mkItem.id)}
                                        >
                                            <IconTrash className="size-4" />
                                        </Button>
                                    )}
                                </div>
                            ))}
                        </div>

                        <Button type="button" variant="outline" size="sm" onClick={addMkRow} className="mt-2">
                            <IconPlus className="mr-2 size-4" />
                            Tambah Pilihan MK
                        </Button>

                        <div className="mt-8 flex flex-col gap-2 lg:flex-row lg:justify-end">
                            <Button type="button" variant="ghost" size="xl" onClick={() => reset()}>
                                Reset
                            </Button>
                            <Button type="submit" variant="blue" size="xl" disabled={processing}>
                                <IconChecks />
                                Simpan Perubahan
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Edit.layout = (page) => <AppLayout children={page} title={page.props.pageSettings.title} />;
