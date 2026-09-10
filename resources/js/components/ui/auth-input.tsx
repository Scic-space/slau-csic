import type { ReactNode } from 'react';

const baseControlClass = 'auth-input h-11 w-full rounded-sm border bg-input pl-10 pr-3 text-sm text-foreground placeholder:text-muted-foreground outline-none transition hover:bg-input focus:bg-input disabled:cursor-not-allowed disabled:opacity-60';

export function authControlClass(hasError: boolean): string {
  return `${baseControlClass} ${hasError ? 'border-error-500 focus:border-error-500 focus:ring-3 focus:ring-error-500/10' : 'border-border focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10'}`;
}

export const authLabelClass = 'mb-1.5 block text-sm font-medium text-foreground';

export function AuthInputIcon({ children }: { children: ReactNode }) {
  return <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-muted-foreground" aria-hidden="true">{children}</span>;
}

export function AuthInputError({ id, message }: { id: string; message?: string }) {
  return message ? <p id={id} role="alert" className="mt-1.5 text-sm text-error-600 dark:text-error-400">{message}</p> : null;
}

export function AuthPasswordToggle({ isVisible, onToggle, label = 'password' }: { isVisible: boolean; onToggle: () => void; label?: string }) {
  return (
    <button type="button" onClick={onToggle} className="absolute right-2 top-1/2 -translate-y-1/2 rounded-sm p-2 text-muted-foreground transition hover:bg-card-hover hover:text-foreground" aria-label={isVisible ? `Hide ${label}` : `Show ${label}`}>
      <span className="material-symbols-outlined text-[19px]" aria-hidden="true">{isVisible ? 'visibility_off' : 'visibility'}</span>
    </button>
  );
}
