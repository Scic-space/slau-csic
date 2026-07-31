import { useForm, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { GlowyWavesBackground } from '@/components/ui/glowy-waves-hero-shadcnui';
import { LoginForm } from '@/components/ui/login-form';
import { FormEventHandler } from 'react';

interface LoginProps {
    status?: string;
}

export default function Login({ status }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/auth/login', {
            onSuccess: () => reset('password'),
        });
    };

    return (
        <GlowyWavesBackground>
            <section className="relative flex h-screen w-full flex-col items-center justify-center px-6">
                <Link
                    href="/"
                    className="absolute left-6 top-6 z-20 inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/40 backdrop-blur transition-colors hover:border-white/20 hover:bg-white/10 hover:text-white/70"
                >
                    <ArrowLeft className="h-3.5 w-3.5" />
                    Home
                </Link>

                <LoginForm
                    onSubmit={submit}
                    email={data.email}
                    password={data.password}
                    remember={data.remember}
                    onEmailChange={(value) => setData('email', value)}
                    onPasswordChange={(value) => setData('password', value)}
                    onRememberChange={(value) => setData('remember', value)}
                    processing={processing}
                    errors={errors}
                    status={status}
                />

                <p className="mt-8 text-xs text-white/20">
                    SLAU Cybersecurity &amp; Innovations Club
                </p>
            </section>
        </GlowyWavesBackground>
    );
}
