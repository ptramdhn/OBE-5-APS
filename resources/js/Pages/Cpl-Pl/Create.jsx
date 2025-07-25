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

// Komponen ini menerima props: cpls (semua CPL), pls (semua PL), dan props standar lainnya
export default function Create(props) {
    const { cpls, pls } = props;

    const [filteredPls, setFilteredPls] = useState(pls);

    // 1. STRUKTUR STATE: `pl_ids` adalah array untuk menampung dropdown dinamis
    const { data, setData, errors, post, processing, reset } = useForm({
        cpl_id: '',
        pl_ids: [{ id: Date.now(), value: '' }], // Mulai dengan satu dropdown PL
    });

    useEffect(() => {
        const selectedCpl = cpls.find((cpl) => cpl.value === data.cpl_id);

        const targetProdiId = selectedCpl?.prodi_id;

        const newFilteredPls = pls.filter((pl) => pl.prodi_id === targetProdiId);

        setFilteredPls(newFilteredPls);
        setData('pl_ids', [{ id: Date.now(), value: '' }]);
    }, [data.cpl_id]);

    // 2. LOGIKA HANDLER untuk form dinamis
    // Menambah baris dropdown PL baru
    const addPlRow = () => {
        setData('pl_ids', [...data.pl_ids, { id: Date.now(), value: '' }]);
    };

    // Menghapus baris dropdown PL berdasarkan ID uniknya
    const removePlRow = (id) => {
        setData(
            'pl_ids',
            data.pl_ids.filter((item) => item.id !== id),
        );
    };

    // Mengupdate nilai pada dropdown PL tertentu
    const handlePlChange = (id, newValue) => {
        const updatedPlIds = data.pl_ids.map((item) => (item.id === id ? { ...item, value: newValue } : item));
        setData('pl_ids', updatedPlIds);
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

    console.log(cpls);

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
                            <Link href={route('cpl-profiles.index')}>
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
                            <Label htmlFor="cpl_id" required>
                                Pilih CPL
                            </Label>
                            <Select
                                // Hubungkan nilai utama Select dengan state form
                                value={data.cpl_id}
                                onValueChange={(value) => {
                                    // Logika untuk me-reset PL saat CPL berubah
                                    setData((currentData) => ({
                                        ...currentData,
                                        cpl_id: value,
                                        pl_ids: [{ id: Date.now(), value: '' }],
                                    }));
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih CPL yang akan dipetakan" />
                                </SelectTrigger>
                                <SelectContent>
                                    {/* Pastikan props.cpls ada dan merupakan array */}
                                    {props.cpls &&
                                        props.cpls.map((cpl) => (
                                            // `key` dan `value` adalah properti WAJIB
                                            <SelectItem key={cpl.value} value={cpl.value}>
                                                {/* `cpl.label` adalah teks yang dilihat oleh pengguna */}
                                                {cpl.label}
                                            </SelectItem>
                                        ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.cpl_id} />
                        </div>

                        <hr className="my-6 border-dashed" />

                        {/* Area Dropdown PL Dinamis */}
                        <div className="space-y-3">
                            <Label>Petakan ke Profil Lulusan (PL) berikut:</Label>

                            {/* Perulangan pada state `data.pl_ids` untuk merender setiap baris. Ini sudah benar. */}
                            {data.pl_ids.map((plItem, index) => (
                                // `key` unik untuk setiap baris, penting untuk performa React. Sudah benar.
                                <div key={plItem.id} className="flex w-full items-center gap-x-2">
                                    <div className="w-full">
                                        <Select
                                            // Nilai Select terhubung ke `value` dari item di state. Sudah benar.
                                            value={plItem.value}
                                            // Handler dipanggil dengan ID unik baris saat diubah. Sudah benar.
                                            onValueChange={(value) => handlePlChange(plItem.id, value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder={`Pilih PL #${index + 1}`} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {/* Opsi di-render dari `filteredPls`. Sudah benar. */}
                                                {filteredPls.map((pl) => (
                                                    // `key` dan `value` untuk setiap opsi. Sudah benar.
                                                    <SelectItem key={pl.value} value={pl.value}>
                                                        {pl.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {/* Format untuk menampilkan error validasi array. Sudah benar. */}
                                        <InputError message={errors[`pl_ids.${index}.value`]} />
                                    </div>

                                    {/* Tombol hapus hanya muncul jika ada lebih dari 1 baris. Logika yang sangat baik. */}
                                    {data.pl_ids.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="icon"
                                            onClick={() => removePlRow(plItem.id)}
                                        >
                                            <IconTrash className="size-4" />
                                        </Button>
                                    )}
                                </div>
                            ))}
                        </div>

                        {/* Tombol untuk menambah dropdown PL */}
                        <Button type="button" variant="outline" size="sm" onClick={addPlRow} className="mt-2">
                            <IconPlus className="mr-2 size-4" />
                            Tambah Pilihan PL
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
