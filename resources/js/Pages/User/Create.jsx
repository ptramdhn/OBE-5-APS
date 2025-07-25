import BreadcrumbHeader from '@/Components/BreadcrumbHeader';
import HeaderTitle from '@/Components/HeaderTitle';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { Link, router, useForm } from '@inertiajs/react';
import { IconArrowBack, IconChecks, IconUsers } from '@tabler/icons-react';
import { toast } from 'sonner';

export default function Create(props) {
    const { data, setData, errors, post, processing, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: props.state.role || '',
        prodi_id: null,
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
                            icon={IconUsers}
                        />
                        <Button variant="blue" size="xl" asChild>
                            <Link href={route('users.index')}>
                                <IconArrowBack className="size-4" />
                                Kembali
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <form className="space-y-4" onSubmit={onHandleSubmit}>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="name">Nama</Label>
                            <Input
                                type="text"
                                name="name"
                                id="name"
                                placeholder="Masukkan nama"
                                value={data.name}
                                onChange={onHandleChange}
                            />
                            {errors.name && <InputError message={errors.name} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                type="email"
                                name="email"
                                id="email"
                                placeholder="Masukkan email"
                                value={data.email}
                                onChange={onHandleChange}
                            />
                            {errors.email && <InputError message={errors.email} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="role">Peran</Label>
                            <Select
                                value={data.role} // Gunakan value dari state useForm
                                onValueChange={(value) => {
                                    // Gunakan callback `setData` untuk melakukan beberapa aksi
                                    setData((currentData) => {
                                        // Reset prodi_id setiap kali role berubah
                                        return { ...currentData, role: value, prodi_id: null };
                                    });

                                    // Picu partial reload untuk mendapatkan 'programStudies' baru
                                    router.reload({
                                        method: 'get',
                                        data: { role: value }, // Kirim role_id yang baru
                                        only: ['programStudies'], // Hanya minta prop 'programStudies'
                                        preserveState: true,
                                        preserveScroll: true,
                                    });
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih peran pengguna" />
                                </SelectTrigger>
                                <SelectContent>
                                    {props.roles.map((role) => (
                                        <SelectItem key={role.value} value={role.value}>
                                            {role.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.role && <InputError message={errors.role} />}
                        </div>

                        {/* Logika ini sudah benar dan tidak perlu diubah */}
                        {props.programStudies.length > 0 && (
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="prodi_id">Program Studi</Label>
                                <Select value={data.prodi_id} onValueChange={(value) => setData('prodi_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih program studi" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {props.programStudies.map((programStudy) => (
                                            <SelectItem key={programStudy.value} value={programStudy.value}>
                                                {programStudy.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.prodi_id && <InputError message={errors.prodi_id} />}
                            </div>
                        )}

                        <div className="flex flex-col gap-2">
                            <Label htmlFor="password">Password</Label>
                            <Input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="Masukkan password"
                                value={data.password}
                                onChange={onHandleChange}
                            />
                            {errors.password && <InputError message={errors.password} />}
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="password_confirmation">Konfirmasi Password</Label>
                            <Input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                placeholder="Masukkan password"
                                value={data.password_confirmation}
                                onChange={onHandleChange}
                            />
                            {errors.password_confirmation && <InputError message={errors.password_confirmation} />}
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

Create.layout = (page) => <AppLayout children={page} title={page.props.pageSettings.title} />;
