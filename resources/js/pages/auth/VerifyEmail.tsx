import { Link, router, useForm, usePage } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { FormEventHandler, useRef } from 'react';
import { ThemeToggle } from '@/components/ui/theme-toggle';

export default function VerifyEmail() {
    const { auth, flash } = usePage().props as {
        auth: { user: { email: string } | null };
        flash: { status?: string };
    };
    const { data, setData, post, processing, errors } = useForm({ code: '' });
    const inputsRef = useRef<(HTMLInputElement | null)[]>([]);

    const handleChange = (index: number, value: string) => {
        const digit = value.replace(/\D/g, '').slice(-1);
        const chars = Array.from({ length: 6 }, (_, i) => data.code[i] ?? '');
        chars[index] = digit;
        setData('code', chars.join(''));

        if (digit && index < 5) {
            inputsRef.current[index + 1]?.focus();
        }
    };

    const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Backspace' && !data.code[index] && index > 0) {
            inputsRef.current[index - 1]?.focus();
        }
    };

    const handlePaste = (e: React.ClipboardEvent) => {
        const digits = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);

        if (digits) {
            e.preventDefault();
            setData('code', digits);
            inputsRef.current[Math.min(digits.length, 5)]?.focus();
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/auth/verify-email/verify');
    };

    const inputClass = (hasError: boolean) =>
        `h-12 min-w-0 flex-1 rounded-sm border bg-input text-center text-lg font-semibold text-foreground outline-none transition duration-200 hover:bg-input focus:bg-input focus:ring-3 sm:h-14 sm:text-xl ${
            hasError
                ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10'
                : 'border-border focus:border-brand-500 focus:ring-brand-500/10'
        }`;

    return (
        <main className="flex min-h-screen flex-col overflow-x-hidden bg-background font-sans text-foreground">
            <header className="border-b border-border bg-card/95 backdrop-blur-xl">
                <div className="flex h-16 w-full items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                    <Link href="/" className="flex min-w-0 shrink-0 items-center gap-2" aria-label="SCIC Cyber home">
                        <img src="/images/club_logo.png" alt="SLAU-CSIC" className="h-10 w-auto dark:brightness-0 dark:invert" />
                        <span className="hidden text-sm font-bold leading-tight text-foreground sm:block">SCIC <span className="block text-xs font-medium text-muted-foreground">Cyber</span></span>
                    </Link>
                    <ThemeToggle />
                </div>
            </header>

            <section className="flex flex-1 flex-col items-center justify-center px-4 py-6 sm:px-6 sm:py-8 lg:py-10">
                <div className="w-full max-w-md">
                    <Link href="/" className="mb-7 inline-flex min-h-9 items-center gap-2 rounded-sm text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                        <span className="material-symbols-outlined text-[19px]" aria-hidden="true">arrow_back</span>
                        Back to home
                    </Link>

                    <div>
                        <div className="mb-7">
                            <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-sm bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                <span className="material-symbols-outlined text-[26px]" aria-hidden="true">mark_email_read</span>
                            </div>
                            <h1 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">Verify Your Email</h1>
                            <p className="mt-3 text-sm leading-6 text-muted-foreground">
                                Enter the 6-digit code we sent to{' '}
                                <span className="break-all font-semibold text-foreground">{auth.user?.email}</span>
                            </p>
                        </div>

                        {flash.status === 'verification-code-sent' && (
                            <div role="status" className="mb-5 flex items-center gap-2.5 rounded-sm bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-300">
                                <span className="material-symbols-outlined text-[19px]" aria-hidden="true">check_circle</span>
                                A new verification code has been sent to your email.
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-6">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-foreground" htmlFor="verification-code-0">Verification code</label>
                                <div className="flex justify-center gap-2 sm:gap-3" onPaste={handlePaste}>
                                    {Array.from({ length: 6 }, (_, i) => (
                                        <input
                                            key={i}
                                            id={`verification-code-${i}`}
                                            ref={(el) => {
                                                inputsRef.current[i] = el;
                                            }}
                                            type="text"
                                            inputMode="numeric"
                                            autoComplete={i === 0 ? 'one-time-code' : 'off'}
                                            maxLength={1}
                                            value={data.code[i] ?? ''}
                                            onChange={(e) => handleChange(i, e.target.value)}
                                            onKeyDown={(e) => handleKeyDown(i, e)}
                                            className={inputClass(!!errors.code)}
                                            aria-label={`Digit ${i + 1}`}
                                            aria-invalid={!!errors.code}
                                            aria-describedby={errors.code ? 'verification-code-error' : undefined}
                                        />
                                    ))}
                                </div>
                                {errors.code && <p id="verification-code-error" role="alert" className="mt-3 text-sm text-error-600 dark:text-error-400">{errors.code}</p>}
                            </div>

                            <button
                                type="submit"
                                disabled={processing || data.code.length !== 6}
                                aria-busy={processing}
                                className="relative h-12 w-full rounded-sm bg-brand-600 text-sm font-semibold text-white transition-colors duration-200 hover:bg-[#2984D1] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span className={`flex items-center justify-center gap-2 ${processing ? 'opacity-0' : ''}`}>
                                    Verify Email
                                    <span className="material-symbols-outlined text-[19px]" aria-hidden="true">verified</span>
                                </span>
                                {processing && (
                                    <span className="absolute inset-0 flex items-center justify-center" role="status" aria-label="Verifying email">
                                        <Loader2 className="h-5 w-5 animate-spin text-white" />
                                    </span>
                                )}
                            </button>
                        </form>

                        <div className="mt-5 flex flex-wrap items-center justify-between gap-2 text-sm">
                            <button
                                type="button"
                                disabled={processing}
                                onClick={() => post('/auth/verify-email/resend')}
                                className="inline-flex min-h-10 items-center gap-2 rounded-sm py-2 font-semibold text-brand-600 transition-colors hover:text-[#2984D1] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-60 dark:text-brand-300 dark:hover:text-brand-200"
                            >
                                <span className="material-symbols-outlined text-[18px]" aria-hidden="true">refresh</span>
                                Resend code
                            </button>
                            <button
                                type="button"
                                disabled={processing}
                                onClick={() => router.post('/auth/logout')}
                                className="inline-flex min-h-10 items-center gap-2 rounded-sm py-2 font-medium text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span className="material-symbols-outlined text-[18px]" aria-hidden="true">logout</span>
                                Log out
                            </button>
                        </div>
                    </div>

                    <p className="mt-5 text-center text-xs leading-5 text-muted-foreground">
                        The code expires in 15 minutes. Didn't get it? Check spam and try resending.
                    </p>

                    <nav aria-label="Authentication help" className="mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-1 border-t border-border pt-4 sm:gap-y-2 sm:pt-5">
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
