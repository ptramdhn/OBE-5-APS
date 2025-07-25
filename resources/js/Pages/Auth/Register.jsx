import ApplicationLogo from '@/Components/ApplicationLogo';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import GuestLayout from '@/Layouts/GuestLayout';
import { Link, useForm } from '@inertiajs/react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const onHandleSubmit = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <div className="flex flex-col gap-6">
            <Card className="overflow-hidden">
                <CardContent className="grid p-0 md:grid-cols-2">
                    <form onSubmit={onHandleSubmit} className="p-6 md:p-8">
                        <div className="flex flex-col gap-6">
                            <div className="flex flex-col items-center text-center">
                                <ApplicationLogo />
                                <h1 className="mt-6 text-2xl font-bold leading-relaxed">Selamat Datang</h1>
                                <p className="text-sm text-muted-foreground">
                                    Mulai daftar sekarang untuk mengelola keuangan anda
                                </p>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nama</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value={data.name}
                                    placeholder="Aditya"
                                    autoComplete="name"
                                    onChange={(e) => setData('name', e.target.value)}
                                    autoFocus
                                />
                                {errors.name && <InputError message={errors.name} />}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    placeholder="aditya@cuan.test"
                                    autoComplete="username"
                                    onChange={(e) => setData('email', e.target.value)}
                                />
                                {errors.email && <InputError message={errors.email} />}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    placeholder="*******"
                                    autoComplete="new-password"
                                    onChange={(e) => setData('password', e.target.value)}
                                />
                                {errors.password && <InputError message={errors.password} />}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">Confirmation Password</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    value={data.password_confirmation}
                                    placeholder="*******"
                                    autoComplete="new-password"
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                />
                                {errors.password_confirmation && <InputError message={errors.password_confirmation} />}
                            </div>
                            <Button variant="emerald" type="submit" className="w-full" disabled={processing}>
                                Daftar
                            </Button>
                            <div className="text-center text-sm">
                                Sudah memiliki akun?
                                <Link href={route('login')} className="underline underline-offset-4">
                                    Masuk
                                </Link>
                            </div>
                        </div>
                    </form>
                    <div className="relative hidden bg-muted md:block">
                        <img
                            src="/images/bg.jpg"
                            alt="Images"
                            className="absolute inset-0 h-full w-full object-cover dark:brightness-[0.2] dark:grayscale"
                        />
                    </div>
                </CardContent>
            </Card>
            <div className="text-balance text-center text-xs text-muted-foreground [&_a]:underline [&_a]:underline-offset-4 hover:[&_a]:text-primary">
                Dengan mengklik lanjutkan, Anda menyetujui <Link href="#">Persyaratan Layanan</Link> dan{' '}
                <Link href="#">Kebijakan Privasi Kami</Link>
            </div>
        </div>
    );
}

Register.layout = (page) => <GuestLayout title="Daftar" children={page} />;
