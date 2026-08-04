import { Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, Loader2, LogOut, MailCheck, RefreshCw, ShieldCheck } from 'lucide-react';
import { GlowyWavesBackground } from '@/components/ui/glowy-waves-hero-shadcnui';
import { FormEventHandler, useRef } from 'react';

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
        `h-14 w-11 rounded-xl border bg-white/5 text-center text-xl font-semibold text-white placeholder:text-white/20 transition-all duration-200 focus:outline-none focus:ring-2 sm:h-16 sm:w-13 ${
            hasError
                ? 'border-red-500/50 focus:border-red-500/70 focus:ring-red-500/20'
                : 'border-white/10 focus:border-indigo-500/50 focus:ring-indigo-500/20'
        }`;

    return (
        <GlowyWavesBackground>
            <section className="relative flex min-h-screen w-full flex-col items-center justify-center px-6 py-12">
                <Link
                    href="/"
                    className="absolute left-6 top-6 z-20 inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/40 backdrop-blur transition-colors hover:border-white/20 hover:bg-white/10 hover:text-white/70"
                >
                    <ArrowLeft className="h-3.5 w-3.5" />
                    Home
                </Link>

                <div className="w-full max-w-md px-4 sm:px-0">
                    <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8 shadow-2xl shadow-black/20 backdrop-blur-2xl">
                        <div className="mb-7 text-center">
                            <div className="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-500 shadow-lg shadow-indigo-500/25">
                                <ShieldCheck className="h-7 w-7 text-white" />
                            </div>
                            <h1 className="text-2xl font-bold text-white">Verify Your Email</h1>
                            <p className="mt-1.5 text-sm text-white/45">
                                Enter the 6-digit code we sent to{' '}
                                <span className="font-medium text-indigo-300">{auth.user?.email}</span>
                            </p>
                        </div>

                        {flash.status === 'verification-code-sent' && (
                            <div className="mb-5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                                A new verification code has been sent to your email.
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-6">
                            <div>
                                <div className="flex justify-center gap-2 sm:gap-3" onPaste={handlePaste}>
                                    {Array.from({ length: 6 }, (_, i) => (
                                        <input
                                            key={i}
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
                                        />
                                    ))}
                                </div>
                                {errors.code && <p className="mt-3 text-center text-sm text-red-400">{errors.code}</p>}
                            </div>

                            <button
                                type="submit"
                                disabled={processing || data.code.length !== 6}
                                className="relative flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-indigo-400 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:shadow-indigo-500/40 hover:brightness-110 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100"
                            >
                                {processing ? (
                                    <Loader2 className="h-5 w-5 animate-spin" />
                                ) : (
                                    <>
                                        Verify Email
                                        <MailCheck className="h-4 w-4" />
                                    </>
                                )}
                            </button>
                        </form>

                        <div className="mt-6 flex items-center justify-between text-xs">
                            <button
                                type="button"
                                disabled={processing}
                                onClick={() => post('/auth/verify-email/resend')}
                                className="inline-flex items-center gap-1.5 font-medium text-indigo-400 transition-colors hover:text-indigo-300 disabled:opacity-50"
                            >
                                <RefreshCw className="h-3.5 w-3.5" />
                                Resend code
                            </button>
                            <button
                                type="button"
                                disabled={processing}
                                onClick={() => router.post('/auth/logout')}
                                className="inline-flex items-center gap-1.5 font-medium text-white/35 transition-colors hover:text-white/60 disabled:opacity-50"
                            >
                                <LogOut className="h-3.5 w-3.5" />
                                Log out
                            </button>
                        </div>
                    </div>

                    <p className="mt-8 text-center text-xs text-white/20">
                        The code expires in 15 minutes. Didn't get it? Check spam and try resending.
                    </p>
                </div>
            </section>
        </GlowyWavesBackground>
    );
}
