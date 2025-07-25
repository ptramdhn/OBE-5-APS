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

// Komponen ini menerima props: cpls (semua CPL), bks (semua BK), dan props standar lainnya
export default function Edit(props) {
    const { cpls, mks, mapping } = props;
    // const [filteredPls, setFilteredPls] = useState(pls);

    // 1. Cari prodi_id dari CPL yang sedang diedit SEBELUM melakukan render
    const initialMk = mks.find((mk) => mk.value === mapping.mk_id);
    const initialProdiId = initialMk?.prodi_id;

    // 2. Buat daftar PL yang sudah difilter untuk dijadikan state awal
    const initialFilteredCpls = cpls.filter((cpl) => cpl.prodi_id === initialProdiId);

    const [filteredCpls, setFilteredCpls] = useState(initialFilteredCpls);

    const { data, setData, errors, post, processing, reset } = useForm({
        mk_id: mapping.mk_id,
        cpl_ids:
            mapping.cpl_ids && mapping.cpl_ids.length > 0
                ? mapping.cpl_ids.map((id) => ({ id: id, value: id }))
                : [{ id: Date.now(), value: '' }],
        _method: props.pageSettings.method,
    });

    // 2. Buat ref untuk melacak render pertama
    const isInitialMount = useRef(true);

    useEffect(() => {
        // Jangan jalankan logika ini saat pertama kali dimuat
        if (isInitialMount.current) {
            isInitialMount.current = false;
            return;
        }

        // 1. Cari CPL yang dipilih
        const selectedMk = mks.find((mk) => mk.value === data.mk_id);

        // 2. Dapatkan prodi_id dari CPL tersebut
        const targetProdiId = selectedMk.prodi_id;

        // 4. Lakukan filter
        const newFilteredCpls = cpls.filter((cpl) => cpl.prodi_id === targetProdiId);

        setFilteredCpls(newFilteredCpls);
        setData('cpl_ids', [{ id: Date.now(), value: '' }]);
    }, [data.mk_id]);

    // 2. LOGIKA HANDLER untuk form dinamis
    // Menambah baris dropdown PL baru
    const addCplRow = () => {
        setData('cpl_ids', [...data.cpl_ids, { id: Date.now(), value: '' }]);
    };

    // Menghapus baris dropdown PL berdasarkan ID uniknya
    const removeCplRow = (id) => {
        setData(
            'cpl_ids',
            data.cpl_ids.filter((item) => item.id !== id),
        );
    };

    // Mengupdate nilai pada dropdown PL tertentu
    const handleCplChange = (id, newValue) => {
        const updatedCplIds = data.cpl_ids.map((item) => (item.id === id ? { ...item, value: newValue } : item));
        setData('cpl_ids', updatedCplIds);
    };

    // Handler untuk submit form
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
                            icon={IconArrowsSplit}
                        />
                        <Button variant="blue" size="xl" asChild>
                            <Link href={route('cpl-mk.index')}>
                                {' '}
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
                            <Label htmlFor="mk_id" required>
                                Pilih MK
                            </Label>
                            <Select
                                value={data.mk_id}
                                // Gunakan callback `setData` di sini
                                onValueChange={(value) => {
                                    setData((currentData) => ({
                                        ...currentData,
                                        mk_id: value,
                                    }));
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih MK" />
                                </SelectTrigger>
                                <SelectContent>
                                    {mks.map((mk) => (
                                        <SelectItem key={mk.value} value={mk.value}>
                                            {mk.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.mk_id} />
                        </div>

                        <hr className="my-6 border-dashed" />

                        {/* Area Dropdown PL Dinamis */}
                        <div className="space-y-3">
                            <Label>Petakan ke Capaian Pembelajaran Lulusan (CPL) berikut:</Label>

                            {/* Perulangan pada state `data.pl_ids` untuk merender setiap baris. Ini sudah benar. */}
                            {data.cpl_ids.map((cplItem, index) => (
                                // `key` unik untuk setiap baris, penting untuk performa React. Sudah benar.
                                <div key={cplItem.id} className="flex w-full items-center gap-x-2">
                                    <div className="w-full">
                                        <Select
                                            // Nilai Select terhubung ke `value` dari item di state. Sudah benar.
                                            value={cplItem.value}
                                            // Handler dipanggil dengan ID unik baris saat diubah. Sudah benar.
                                            onValueChange={(value) => handleCplChange(cplItem.id, value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder={`Pilih CPL #${index + 1}`} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {/* Opsi di-render dari `filteredPls`. Sudah benar. */}
                                                {filteredCpls.map((cpl) => (
                                                    // `key` dan `value` untuk setiap opsi. Sudah benar.
                                                    <SelectItem key={cpl.value} value={cpl.value}>
                                                        {cpl.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {/* Format untuk menampilkan error validasi array. Sudah benar. */}
                                        <InputError message={errors[`cpl_ids.${index}.value`]} />
                                    </div>

                                    {/* Tombol hapus hanya muncul jika ada lebih dari 1 baris. Logika yang sangat baik. */}
                                    {data.cpl_ids.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="icon"
                                            onClick={() => removeCplRow(cplItem.id)}
                                        >
                                            <IconTrash className="size-4" />
                                        </Button>
                                    )}
                                </div>
                            ))}
                        </div>

                        {/* Tombol untuk menambah dropdown PL */}
                        <Button type="button" variant="outline" size="sm" onClick={addCplRow} className="mt-2">
                            <IconPlus className="mr-2 size-4" />
                            Tambah Pilihan CPL
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

Edit.layout = (page) => <AppLayout children={page} title={page.props.pageSettings.title} />;
