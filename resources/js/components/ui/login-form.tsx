import { Link } from '@inertiajs/react';
import { Eye, EyeOff, Mail, Lock, ArrowRight, Loader2, CheckCircle2 } from 'lucide-react';
import { useState } from 'react';

interface LoginFormProps {
  onSubmit: (e: React.FormEvent) => void;
  email: string;
  password: string;
  remember: boolean;
  onEmailChange: (value: string) => void;
  onPasswordChange: (value: string) => void;
  onRememberChange: (value: boolean) => void;
  processing: boolean;
  errors: { email?: string; password?: string };
  status?: string;
}

export function LoginForm({
  onSubmit,
  email,
  password,
  remember,
  onEmailChange,
  onPasswordChange,
  onRememberChange,
  processing,
  errors,
  status,
}: LoginFormProps) {
  const [showPassword, setShowPassword] = useState(false);

  const inputClass = (hasError: boolean) =>
    `h-12 w-full rounded-xl border bg-white/5 pl-11 pr-12 text-sm text-white placeholder:text-white/30 transition-all duration-200 focus:outline-none ${
      hasError
        ? 'border-red-500/50 focus:border-red-500/70 focus:ring-2 focus:ring-red-500/20'
        : 'border-white/10 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20'
    }`;

  return (
    <div className="w-full max-w-sm px-4 sm:px-0">
      <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8 shadow-2xl shadow-black/20 backdrop-blur-2xl">
        <div className="mb-8 text-center">
          <img
            src="/images/club_logo.png"
            alt="SLAU-CSIC"
            className="mx-auto mb-4 h-11 w-auto invert brightness-[1.2] drop-shadow-[0_0_20px_rgba(99,102,241,0.4)]"
          />
          <h2 className="text-2xl font-bold text-white">Welcome Back</h2>
          <p className="mt-1.5 text-sm text-white/45">Sign in to your account</p>
        </div>

        {status && (
          <div className="mb-5 flex items-center gap-2.5 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-300">
            <CheckCircle2 className="h-4 w-4 shrink-0" />
            {status}
          </div>
        )}

        <form onSubmit={onSubmit} className="space-y-4">
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

          <div className="space-y-1.5">
            <div className="flex items-center justify-between">
              <label className="text-sm font-medium text-white/60">Password</label>
              <Link
                href="/auth/forgot-password"
                className="text-xs text-indigo-400/80 transition-colors hover:text-indigo-300"
              >
                Forgot password?
              </Link>
            </div>
            <div className="relative">
              <Lock className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
              <input
                type={showPassword ? 'text' : 'password'}
                value={password}
                onChange={(e) => onPasswordChange(e.target.value)}
                className={inputClass(!!errors.password)}
                placeholder="Type your password"
                required
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-2 text-white/25 transition-colors hover:bg-white/5 hover:text-white/50"
              >
                {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
              </button>
            </div>
            {errors.password && <p className="text-sm text-red-400">{errors.password}</p>}
          </div>

          <label className="flex cursor-pointer items-center gap-3 pt-1 group">
            <div className="relative">
              <input
                type="checkbox"
                checked={remember}
                onChange={(e) => onRememberChange(e.target.checked)}
                className="peer sr-only"
              />
              <div className="h-4 w-4 rounded border border-white/20 bg-white/5 transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-500">
                {remember && <CheckCircle2 className="h-4 w-4 text-white" />}
              </div>
            </div>
            <span className="text-sm text-white/35 transition-colors group-hover:text-white/50">Remember me</span>
          </label>

          <button
            type="submit"
            disabled={processing}
            className="relative mt-2 h-12 w-full rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-indigo-400 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:shadow-indigo-500/40 hover:brightness-110 active:scale-[0.98] disabled:opacity-50 disabled:active:scale-100"
          >
            <span className={`flex items-center justify-center gap-2 ${processing ? 'opacity-0' : ''}`}>
              Sign In
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
          Don&apos;t have an account?{' '}
          <Link href="/auth/register" className="font-semibold text-indigo-400 transition-colors hover:text-indigo-300">
            Sign Up
          </Link>
        </div>
      </div>
    </div>
  );
}
