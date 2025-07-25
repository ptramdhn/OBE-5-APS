import BreadcrumbHeader from '@/Components/BreadcrumbHeader';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { flashMessage } from '@/lib/utils';
import { useForm } from '@inertiajs/react';
import { IconChecks, IconPlus, IconTrash } from '@tabler/icons-react';
import { toast } from 'sonner';

export default function Edit(props) {
    // Ambil data `mapping` dan daftar `mks` dari props
    const { mapping, mks } = props;

    // Inisialisasi useForm dengan data yang sudah ada dari `mapping`
    const { data, setData, errors, post, processing, reset } = useForm({
        code: mapping.code || '',
        description: mapping.description || '',
        // Ubah array `mk_ids` dari props menjadi format state frontend
        mk_ids:
            mapping.mk_ids && mapping.mk_ids.length > 0
                ? mapping.mk_ids.map((id) => ({ id: id, value: id }))
                : [{ id: Date.now(), value: '' }],
        _method: props.pageSettings.method,
    });

    // Semua handler (add, remove, change) tetap sama, tidak perlu diubah
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
                <CardHeader>{/* ... HeaderTitle dan Tombol Kembali ... */}</CardHeader>
                <CardContent>
                    <form className="space-y-4" onSubmit={onHandleSubmit}>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="code" required>
                                Kode CPMK
                            </Label>
                            <Input id="code" value={data.code} onChange={(e) => setData('code', e.target.value)} />
                            <InputError message={errors.code} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="description" required>
                                Deskripsi CPMK
                            </Label>
                            <Textarea
                                id="description"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                            />
                            <InputError message={errors.description} />
                        </div>

                        <hr className="my-6 border-dashed" />

                        {/* Area Dropdown MK Dinamis (Sama seperti Create.jsx) */}
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
                                                {mks.map((mk) => (
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

                        {/* ... Tombol Reset dan Submit ... */}
                        <div className="mt-8 flex flex-col gap-2 lg:flex-row lg:justify-end">
                            {' '}
                            <Button type="button" variant="ghost" size="xl" onClick={() => reset()}>
                                Reset{' '}
                            </Button>{' '}
                            <Button type="submit" variant="blue" size="xl" disabled={processing}>
                                <IconChecks />
                                Simpan Perubahan{' '}
                            </Button>{' '}
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Edit.layout = (page) => <AppLayout children={page} title={page.props.pageSettings.title} />;
