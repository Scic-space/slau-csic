import { Sun, Moon, ArrowUp } from 'lucide-react';
import { useTheme } from '@/hooks/use-theme';

function handleScrollTop() {
  window.scroll({ top: 0, behavior: 'smooth' });
}

export function ThemeToggle() {
  const { theme, setTheme } = useTheme();

  return (
    <div className="flex items-center justify-center">
      <div className="flex items-center rounded-full border border-dashed border-white/20 px-1 py-1">
        

        <button
          type="button"
          onClick={handleScrollTop}
          className="rounded-full p-2 transition-colors hover:bg-white/10"
          style={{ color: '#cbd5e1' }}
        >
          <ArrowUp className="h-3 w-3" strokeWidth={1} />
          <span className="sr-only">Scroll to top</span>
        </button>

        
      </div>
    </div>
  );
}
