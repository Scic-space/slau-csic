import { useForm, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { GlowyWavesBackground } from '@/components/ui/glowy-waves-hero-shadcnui';
import { ForgotPasswordForm } from '@/components/ui/forgot-password-form';
import { FormEventHandler } from 'react';

interface ForgotPasswordProps {
    status?: string;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/auth/forgot-password');
    };

    return (
        <GlowyWavesBackground>
            <section className="relative flex h-screen w-full flex-col items-center justify-center px-6">
                <Link
                    href="/auth/login"
                    className="absolute left-6 top-6 z-20 inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/40 backdrop-blur transition-colors hover:border-white/20 hover:bg-white/10 hover:text-white/70"
                >
                    <ArrowLeft className="h-3.5 w-3.5" />
                    Back to Sign In
                </Link>

                <ForgotPasswordForm
                    onSubmit={submit}
                    email={data.email}
                    onEmailChange={(value) => setData('email', value)}
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
