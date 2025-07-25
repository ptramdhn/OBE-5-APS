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

// Komponen ini menerima props: cpls (semua CPL), bks (semua BK), dan props standar lainnya
export default function Create(props) {
    const { cpls, bks } = props;

    const [filteredBks, setFilteredBks] = useState(bks);

    // 1. STRUKTUR STATE: `bk_ids` adalah array untuk menampung dropdown dinamis
    const { data, setData, errors, post, processing, reset } = useForm({
        cpl_id: '',
        bk_ids: [{ id: Date.now(), value: '' }], // Mulai dengan satu dropdown BK
    });

    useEffect(() => {
        const selectedCpl = cpls.find((cpl) => cpl.value === data.cpl_id);

        const targetProdiId = selectedCpl?.prodi_id;

        const newFilteredBks = bks.filter((bk) => bk.prodi_id === targetProdiId);

        setFilteredBks(newFilteredBks);
        setData('bk_ids', [{ id: Date.now(), value: '' }]);
    }, [data.cpl_id]);

    // 2. LOGIKA HANDLER untuk form dinamis
    // Menambah baris dropdown BK baru
    const addBkRow = () => {
        setData('bk_ids', [...data.bk_ids, { id: Date.now(), value: '' }]);
    };

    // Menghapus baris dropdown BK berdasarkan ID uniknya
    const removeBkRow = (id) => {
        setData(
            'bk_ids',
            data.bk_ids.filter((item) => item.id !== id),
        );
    };

    // Mengupdate nilai pada dropdown BK tertentu
    const handleBkChange = (id, newValue) => {
        const updatedBkIds = data.bk_ids.map((item) => (item.id === id ? { ...item, value: newValue } : item));
        setData('bk_ids', updatedBkIds);
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

    // console.log(cpls);

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
                            <Link href={route('cpl-bk.index')}>
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
                                        bk_ids: [{ id: Date.now(), value: '' }],
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
                            <Label>Petakan ke Bahan Kajian (BK) berikut:</Label>

                            {/* Perulangan pada state `data.bk_ids` untuk merender setiap baris. Ini sudah benar. */}
                            {data.bk_ids.map((bkItem, index) => (
                                // `key` unik untuk setiap baris, penting untuk performa React. Sudah benar.
                                <div key={bkItem.id} className="flex w-full items-center gap-x-2">
                                    <div className="w-full">
                                        <Select
                                            // Nilai Select terhubung ke `value` dari item di state. Sudah benar.
                                            value={bkItem.value}
                                            // Handler dipanggil dengan ID unik baris saat diubah. Sudah benar.
                                            onValueChange={(value) => handleBkChange(bkItem.id, value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder={`Pilih BK #${index + 1}`} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {/* Opsi di-render dari `filteredBks`. Sudah benar. */}
                                                {filteredBks.map((bk) => (
                                                    // `key` dan `value` untuk setiap opsi. Sudah benar.
                                                    <SelectItem key={bk.value} value={bk.value}>
                                                        {bk.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {/* Format untuk menampilkan error validasi array. Sudah benar. */}
                                        <InputError message={errors[`bk_ids.${index}.value`]} />
                                    </div>

                                    {/* Tombol hapus hanya muncul jika ada lebih dari 1 baris. Logika yang sangat baik. */}
                                    {data.bk_ids.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="icon"
                                            onClick={() => removeBkRow(bkItem.id)}
                                        >
                                            <IconTrash className="size-4" />
                                        </Button>
                                    )}
                                </div>
                            ))}
                        </div>

                        {/* Tombol untuk menambah dropdown BK */}
                        <Button type="button" variant="outline" size="sm" onClick={addBkRow} className="mt-2">
                            <IconPlus className="mr-2 size-4" />
                            Tambah Pilihan BK
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
