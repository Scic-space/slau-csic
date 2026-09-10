import { useTheme } from '@/hooks/use-theme';

interface ThemeToggleProps {
  className?: string;
  size?: 'sm' | 'md';
}

const sizeClasses = {
  sm: 'h-8 w-8',
  md: 'h-10 w-10',
};

const iconSize = {
  sm: '16px',
  md: '20px',
};

export function ThemeToggle({ className = '', size = 'md' }: ThemeToggleProps) {
  const { theme, setTheme } = useTheme();
  const isDark = theme === 'dark';
  const label = isDark ? 'Switch to light mode' : 'Switch to dark mode';

  return (
    <button
      type="button"
      onClick={() => setTheme(isDark ? 'light' : 'dark')}
      aria-label={label}
      title={label}
      className={`inline-flex shrink-0 items-center justify-center rounded-full border border-border bg-card text-muted-foreground transition-colors duration-200 hover:bg-card-hover hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background ${sizeClasses[size]} ${className}`}
    >
      <span
        className="material-symbols-outlined leading-none"
        style={{ fontSize: iconSize[size] }}
        aria-hidden="true"
      >
        {isDark ? 'light_mode' : 'dark_mode'}
      </span>
    </button>
  );
}
