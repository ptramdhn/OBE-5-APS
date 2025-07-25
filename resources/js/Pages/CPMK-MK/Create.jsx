import BreadcrumbHeader from '@/Components/BreadcrumbHeader';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea'; // Gunakan Textarea untuk deskripsi
import AppLayout from '@/Layouts/AppLayout';
import { flashMessage } from '@/lib/utils';
import { useForm } from '@inertiajs/react';
import { IconChecks, IconPlus, IconTrash } from '@tabler/icons-react';
import { toast } from 'sonner';

export default function Create(props) {
    const { mks } = props;

    const { data, setData, errors, post, processing, reset } = useForm({
        code: '',
        description: '',
        mk_ids: [{ id: Date.now(), value: '' }],
    });

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
                <CardHeader>{/* ... HeaderTitle dan Tombol Kembali ... */}</CardHeader>
                <CardContent>
                    <form className="space-y-4" onSubmit={onHandleSubmit}>
                        {/* Input Teks untuk CPMK Baru */}
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
                                                {mks.map((mk) => (
                                                    <SelectItem key={mk.value} value={mk.value}>
                                                        {mk.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors[`mk_ids.${index}`]} />
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
