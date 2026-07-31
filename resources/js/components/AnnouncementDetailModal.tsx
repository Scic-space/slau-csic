import { X, Megaphone, Trophy, AlertTriangle, ExternalLink } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';

interface AnnouncementDetailModalProps {
    announcement: {
        id: number;
        title: string;
        content: string;
        type: string;
        published_at: string | null;
    };
    onClose: () => void;
}

const typeConfig: Record<string, { label: string; icon: typeof Megaphone; color: string; accent: string }> = {
    achievement: {
        label: 'Achievement',
        icon: Trophy,
        color: 'text-amber-400',
        accent: 'border-amber-500/20 bg-amber-500/[0.08]',
    },
    urgent: {
        label: 'Important',
        icon: AlertTriangle,
        color: 'text-red-400',
        accent: 'border-red-500/20 bg-red-500/[0.08]',
    },
    default: {
        label: 'Announcement',
        icon: Megaphone,
        color: 'text-indigo-400',
        accent: 'border-indigo-500/20 bg-indigo-500/[0.08]',
    },
};

function stripHtml(html: string): string {
    return html
        .replace(/<[^>]*>/g, '\n')
        .replace(/&[^;]+;/g, ' ')
        .replace(/\n{3,}/g, '\n\n')
        .trim();
}

export default function AnnouncementDetailModal({ announcement, onClose }: AnnouncementDetailModalProps) {
    const config = typeConfig[announcement.type] || typeConfig.default;
    const Icon = config.icon;

    const formattedDate = announcement.published_at
        ? new Date(announcement.published_at).toLocaleDateString('en-US', {
              weekday: 'long',
              month: 'long',
              day: 'numeric',
              year: 'numeric',
          })
        : null;

    const bodyText = stripHtml(announcement.content);

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" onClick={onClose}>
            <div
                className="relative w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-2xl"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="h-1 w-full bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500" />

                <button onClick={onClose} className="absolute right-4 top-5 z-10 rounded-lg p-1 text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <X className="h-4 w-4" />
                </button>

                <div className="p-6">
                    <div className="flex items-center gap-3 mb-3">
                        <div className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border ${config.accent}`}>
                            <Icon className={`h-4 w-4 ${config.color}`} />
                        </div>
                        <span className={`text-[11px] font-semibold uppercase tracking-wider ${config.color}`}>
                            {config.label}
                        </span>
                    </div>

                    <h2 className="text-lg font-bold tracking-tight text-gray-900 dark:text-white pr-8">
                        {announcement.title}
                    </h2>

                    {formattedDate && (
                        <p className="mt-2 text-xs text-gray-400 dark:text-white/30">
                            {formattedDate}
                        </p>
                    )}

                    <div className="mt-5 space-y-3 text-sm leading-relaxed text-gray-600 dark:text-white/60">
                        {bodyText.split('\n\n').filter(Boolean).map((paragraph, i) => (
                            <p key={i}>{paragraph}</p>
                        ))}
                    </div>

                    <div className="mt-6">
                        <Link href="/auth/register">
                            <Button className="w-full group gap-2 rounded-full px-6 py-5 text-sm uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25">
                                Join the Club
                                <ExternalLink className="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                            </Button>
                        </Link>
                        <p className="mt-3 text-center text-xs text-gray-400 dark:text-white/30">
                            Be part of the next achievement
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
