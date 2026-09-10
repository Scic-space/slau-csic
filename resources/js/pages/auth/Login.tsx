import { useForm, Link } from '@inertiajs/react';
import { LoginForm } from '@/components/ui/login-form';
import { ThemeToggle } from '@/components/ui/theme-toggle';
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
        <main className="flex min-h-screen flex-col overflow-x-hidden bg-background font-sans text-foreground">
            <header className="border-b border-border bg-card/95 backdrop-blur-xl">
                <div className="flex h-16 w-full items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                    <Link href="/" className="flex min-w-0 shrink-0 items-center gap-2" aria-label="SCIC Cyber home">
                        <img src="/images/club_logo.png" alt="SLAU-CSIC" className="h-10 w-auto dark:brightness-0 dark:invert" />
                        <span className="hidden text-sm font-bold leading-tight text-foreground sm:block">SCIC <span className="block text-xs font-medium text-muted-foreground">Cyber</span></span>
                    </Link>
                    <div className="flex shrink-0 items-center gap-2 sm:gap-3">
                        <Link href="/auth/register" className="inline-flex min-h-10 items-center rounded-sm px-3 py-2 text-sm font-semibold text-muted-foreground transition-colors duration-200 hover:bg-card-hover hover:text-foreground focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:px-4">Register</Link>
                        <ThemeToggle />
                    </div>
                </div>
            </header>

            <section className="flex flex-1 flex-col items-center justify-center px-4 py-6 sm:px-6 sm:py-8 lg:py-10">
                <div className="w-full max-w-md">
                    <Link href="/" className="mb-7 inline-flex min-h-9 items-center gap-2 rounded-sm text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                        <span className="material-symbols-outlined text-[19px]" aria-hidden="true">arrow_back</span>
                        Back to home
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

                    <nav aria-label="Authentication help" className="mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-1 border-t border-border pt-4 sm:mt-7 sm:gap-y-2 sm:pt-5">
                        <a href="mailto:sciccyber8@gmail.com" aria-label="Email SCIC Cyber for help" title="Email SCIC Cyber for help" className="inline-flex min-h-10 items-center gap-2 rounded-sm py-2 text-sm font-medium text-muted-foreground transition-colors duration-200 hover:text-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary dark:hover:text-brand-300">
                            <span className="material-symbols-outlined text-[19px]" aria-hidden="true">mail</span>
                            <span>Email Help</span>
                        </a>
                        <a href="https://wa.me/254105883177" target="_blank" rel="noopener noreferrer" aria-label="Chat with SCIC Cyber on WhatsApp" title="WhatsApp Help" className="inline-flex min-h-10 items-center gap-2 rounded-sm py-2 text-sm font-medium text-muted-foreground transition-colors duration-200 hover:text-success-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-success-600 dark:hover:text-success-400">
                            <span className="material-symbols-outlined text-[19px]" aria-hidden="true">chat</span>
                            <span>WhatsApp Help</span>
                        </a>
                    </nav>
                </div>
            </section>
        </main>
    );
}
