import BreadcrumbHeader from '@/Components/BreadcrumbHeader';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { flashMessage } from '@/lib/utils';
import { useForm } from '@inertiajs/react';
import { IconChecks, IconPlus, IconTrash } from '@tabler/icons-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

export default function Edit(props) {
    // Ambil semua props yang relevan
    const { cpls, cpmks, mapping } = props;

    // --- Inisialisasi State Awal ---
    // 1. Hitung daftar CPMK yang sudah difilter saat pertama kali render
    const initialCpl = cpls.find((cpl) => cpl.value === mapping.cpl_id);
    const initialProdiId = initialCpl?.prodi_id;
    const initialFilteredCpmks = cpmks.filter((cpmk) => cpmk.prodi_id === initialProdiId);

    // 2. Gunakan hasil filter untuk state awal
    const [filteredCpmks, setFilteredCpmks] = useState(initialFilteredCpmks);

    // 3. Isi useForm dengan data dari `mapping`
    const { data, setData, errors, post, processing, reset } = useForm({
        cpl_id: mapping.cpl_id,
        cpmk_ids:
            mapping.cpmk_ids && mapping.cpmk_ids.length > 0
                ? mapping.cpmk_ids.map((id) => ({ id: id, value: id }))
                : [{ id: Date.now(), value: '' }],
        _method: props.pageSettings.method,
    });

    // Ref untuk mencegah useEffect berjalan saat pertama kali render
    const isInitialMount = useRef(true);

    // useEffect untuk memfilter ulang daftar CPMK jika pilihan CPL diubah
    useEffect(() => {
        if (isInitialMount.current) {
            isInitialMount.current = false;
            return;
        }

        const selectedCpl = cpls.find((cpl) => cpl.value === data.cpl_id);
        const targetProdiId = selectedCpl?.prodi_id;
        const newFilteredCpmks = cpmks.filter((cpmk) => cpmk.prodi_id === targetProdiId);

        setFilteredCpmks(newFilteredCpmks);
        // Reset pilihan CPMK saat CPL diubah
        setData('cpmk_ids', [{ id: Date.now(), value: '' }]);
    }, [data.cpl_id]);

    // Handler dinamis tetap sama (add, remove, change)
    const addCpmkRow = () => setData('cpmk_ids', [...data.cpmk_ids, { id: Date.now(), value: '' }]);
    const removeCpmkRow = (id) =>
        setData(
            'cpmk_ids',
            data.cpmk_ids.filter((item) => item.id !== id),
        );
    const handleCpmkChange = (id, newValue) => {
        const updatedCpmkIds = data.cpmk_ids.map((item) => (item.id === id ? { ...item, value: newValue } : item));
        setData('cpmk_ids', updatedCpmkIds);
    };

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
                <CardHeader>{/* ... Header dan Tombol Kembali ... */}</CardHeader>
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
                                                {/* Gunakan state `filteredCpmks` untuk opsi */}
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
