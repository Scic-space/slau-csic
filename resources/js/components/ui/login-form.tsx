import { Link } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useState } from 'react';
import { AuthInputError, AuthInputIcon, AuthPasswordToggle, authControlClass, authLabelClass } from '@/components/ui/auth-input';

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

  return (
    <div className="w-full max-w-md">
      <div>
        <div className="mb-8">
          <p className="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-brand-600 dark:text-brand-300">Member access</p>
          <h1 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">Welcome back</h1>
          <p className="mt-3 text-sm leading-6 text-muted-foreground">Sign in to continue to your SCIC Cyber account.</p>
        </div>

        {status && (
          <div role="status" className="mb-5 flex items-center gap-2.5 rounded-sm bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-300">
            <span className="material-symbols-outlined text-[19px]" aria-hidden="true">check_circle</span>
            {status}
          </div>
        )}

        <form onSubmit={onSubmit} className="space-y-6">
          <div className="space-y-2">
            <label htmlFor="email" className={authLabelClass}>Email address</label>
            <div className="relative">
              <AuthInputIcon>mail</AuthInputIcon>
              <input
                id="email"
                name="email"
                type="email"
                autoComplete="email"
                value={email}
                onChange={(e) => onEmailChange(e.target.value)}
                className={authControlClass(!!errors.email)}
                placeholder="Type your email address"
                required
                autoFocus
                aria-invalid={!!errors.email}
                aria-describedby={errors.email ? 'email-error' : undefined}
              />
            </div>
            <AuthInputError id="email-error" message={errors.email} />
          </div>

          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <label htmlFor="password" className={authLabelClass}>Password</label>
              <Link
                href="/auth/forgot-password"
                className="rounded-sm text-xs text-brand-600 transition-colors hover:text-[#2984D1] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary dark:text-brand-300 dark:hover:text-brand-200"
              >
                Forgot password?
              </Link>
            </div>
            <div className="relative">
              <AuthInputIcon>lock</AuthInputIcon>
              <input
                id="password"
                name="password"
                type={showPassword ? 'text' : 'password'}
                autoComplete="current-password"
                value={password}
                onChange={(e) => onPasswordChange(e.target.value)}
                className={`${authControlClass(!!errors.password)} pr-12`}
                placeholder="Type your password"
                required
                aria-invalid={!!errors.password}
                aria-describedby={errors.password ? 'password-error' : undefined}
              />
              <AuthPasswordToggle isVisible={showPassword} onToggle={() => setShowPassword(!showPassword)} />
            </div>
            <AuthInputError id="password-error" message={errors.password} />
          </div>

          <label className="flex cursor-pointer items-center gap-3 pt-1 group">
            <div className="relative">
              <input
                type="checkbox"
                checked={remember}
                onChange={(e) => onRememberChange(e.target.checked)}
                className="h-4 w-4 rounded-sm border-border bg-transparent text-brand-600 focus:ring-2 focus:ring-brand-500/30 focus:ring-offset-2 focus:ring-offset-background"
              />
            </div>
            <span className="text-sm text-muted-foreground transition-colors group-hover:text-foreground">Remember me</span>
          </label>

          <button
            type="submit"
            disabled={processing}
            aria-busy={processing}
            className="relative mt-1 h-12 w-full rounded-sm bg-brand-600 text-sm font-semibold text-white transition-colors duration-200 hover:bg-[#2984D1] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <span className={`flex items-center justify-center gap-2 ${processing ? 'opacity-0' : ''}`}>
              Sign In
              <span className="material-symbols-outlined text-[19px]" aria-hidden="true">login</span>
            </span>
            {processing && (
              <span className="absolute inset-0 flex items-center justify-center" role="status" aria-label="Signing in">
                <Loader2 className="h-5 w-5 animate-spin text-white" />
              </span>
            )}
          </button>
        </form>

        <div className="mt-6 text-center text-sm text-muted-foreground">
          Don&apos;t have an account?{' '}
          <Link href="/auth/register" className="rounded-sm font-semibold text-brand-600 transition-colors hover:text-[#2984D1] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary dark:text-brand-300 dark:hover:text-brand-200">
            Sign Up
          </Link>
        </div>
      </div>
    </div>
  );
}
