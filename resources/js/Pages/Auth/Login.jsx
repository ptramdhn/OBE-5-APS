import ApplicationLogo from '@/Components/ApplicationLogo';
import InputError from '@/Components/InputError';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import GuestLayout from '@/Layouts/GuestLayout';
import { useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const onHandleSubmit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
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
                                <p className="text-sm text-muted-foreground">Masuk ke Sistem OBE UIN Jakarta</p>
                                {status && (
                                    <Alert variant="success" className="my-2">
                                        <AlertDescription>{status}</AlertDescription>
                                    </Alert>
                                )}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    placeholder="Masukkan email"
                                    autoComplete="username"
                                    onChange={(e) => setData('email', e.target.value)}
                                    autoFocus
                                />
                                {errors.email && <InputError message={errors.email} />}
                            </div>
                            <div className="grid gap-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password">Password</Label>
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    placeholder="*******"
                                    autoComplete="current-password"
                                    onChange={(e) => setData('password', e.target.value)}
                                />
                                {errors.password && <InputError message={errors.password} />}
                            </div>
                            <Button variant="blue" type="submit" className="w-full" disabled={processing}>
                                Login
                            </Button>
                            {/* <div className="text-center text-sm">
                                Tidak memiliki akun?
                                <Link href={route('register')} className="underline underline-offset-4">
                                    Daftar
                                </Link>
                            </div> */}
                        </div>
                    </form>
                    <div className="relative hidden bg-muted md:block">
                        <img
                            src="/images/asset-gedung-17.jpg"
                            alt="Images"
                            className="absolute inset-0 h-full w-full object-cover dark:brightness-[0.2] dark:grayscale"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

Login.layout = (page) => <GuestLayout title="login" children={page} />;
