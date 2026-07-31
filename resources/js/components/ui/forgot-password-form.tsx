import { Link } from '@inertiajs/react';
import { Mail, ArrowRight, Loader2, CheckCircle2, KeyRound } from 'lucide-react';
import { useState } from 'react';

interface ForgotPasswordFormProps {
  onSubmit: (e: React.FormEvent) => void;
  email: string;
  onEmailChange: (value: string) => void;
  processing: boolean;
  errors: { email?: string };
  status?: string;
}

export function ForgotPasswordForm({
  onSubmit,
  email,
  onEmailChange,
  processing,
  errors,
  status,
}: ForgotPasswordFormProps) {
  const inputClass = (hasError: boolean) =>
    `h-12 w-full rounded-xl border bg-white/5 pl-11 pr-4 text-sm text-white placeholder:text-white/30 transition-all duration-200 focus:outline-none ${
      hasError
        ? 'border-red-500/50 focus:border-red-500/70 focus:ring-2 focus:ring-red-500/20'
        : 'border-white/10 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20'
    }`;

  if (status) {
    return (
      <div className="w-full max-w-sm px-4 sm:px-0">
        <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8 shadow-2xl shadow-black/20 backdrop-blur-2xl">
          <div className="mb-6 text-center">
            <img
              src="/images/club_logo.png"
              alt="SLAU-CSIC"
              className="mx-auto mb-4 h-11 w-auto invert brightness-[1.2] drop-shadow-[0_0_20px_rgba(99,102,241,0.4)]"
            />
          </div>

          <div className="space-y-4 text-center">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10">
              <CheckCircle2 className="h-7 w-7 text-emerald-400" />
            </div>
            <h2 className="text-2xl font-bold text-white">Check your email</h2>
            <p className="text-sm leading-relaxed text-white/45">
              We&apos;ve sent a password reset link to <span className="text-white/65">{email}</span>. Check your inbox and follow the instructions.
            </p>
            <p className="text-xs text-white/30">
              Didn&apos;t receive the email? Check your spam folder or try again.
            </p>
          </div>

          <div className="mt-6 pt-5">
            <Link
              href="/auth/login"
              className="flex h-12 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-sm font-medium text-white/60 transition-all hover:border-white/20 hover:bg-white/10 hover:text-white"
            >
              Back to Sign In
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full max-w-sm px-4 sm:px-0">
      <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8 shadow-2xl shadow-black/20 backdrop-blur-2xl">
        <div className="mb-8 text-center">
          <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl border border-indigo-500/20 bg-indigo-500/10">
            <KeyRound className="h-6 w-6 text-indigo-400" />
          </div>
          <h1 className="text-2xl font-bold text-white">Reset your password</h1>
          <p className="mt-1.5 text-sm text-white/45">
            Enter your email and we&apos;ll send you a reset link.
          </p>
        </div>

        <form onSubmit={onSubmit} className="space-y-5">
          <div className="space-y-1.5">
            <label className="text-sm font-medium text-white/60">Email</label>
            <div className="relative">
              <Mail className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
              <input
                type="email"
                value={email}
                onChange={(e) => onEmailChange(e.target.value)}
                className={inputClass(!!errors.email)}
                placeholder="Type your email address"
                required
                autoFocus
              />
            </div>
            {errors.email && <p className="text-sm text-red-400">{errors.email}</p>}
          </div>

          <button
            type="submit"
            disabled={processing}
            className="relative h-12 w-full rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-indigo-400 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:shadow-indigo-500/40 hover:brightness-110 active:scale-[0.98] disabled:opacity-50 disabled:active:scale-100"
          >
            <span className={`flex items-center justify-center gap-2 ${processing ? 'opacity-0' : ''}`}>
              Send Reset Link
              <ArrowRight className="h-4 w-4" />
            </span>
            {processing && (
              <span className="absolute inset-0 flex items-center justify-center">
                <Loader2 className="h-5 w-5 animate-spin text-white" />
              </span>
            )}
          </button>
        </form>

        <div className="mt-6 pt-5 text-center text-xs text-white/35">
          Remember your password?{' '}
          <Link href="/auth/login" className="font-semibold text-indigo-400 transition-colors hover:text-indigo-300">
            Sign In
          </Link>
        </div>
      </div>
    </div>
  );
}
