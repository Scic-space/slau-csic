import { Calendar, MapPin, Clock, X, ArrowRight, Sparkles } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';

interface EventDetailModalProps {
    event: {
        title: string;
        slug: string;
        description: string;
        type: string;
        start_date: string;
        end_date: string | null;
        location: string | null;
        skill_level: string | null;
        categories: Array<{ name: string; slug: string; color: string }>;
    };
    onClose: () => void;
}

const typeLabels: Record<string, string> = {
    workshop: 'Workshop',
    talk: 'Talk/Seminar',
    hackathon: 'Hackathon',
    bootcamp: 'Bootcamp',
    ctf: 'CTF',
    awareness_campaign: 'Awareness Campaign',
    social: 'Social',
    meeting: 'Meeting',
};

const typeTeasers: Record<string, string> = {
    workshop: 'Get hands-on with real tools and techniques used by professionals in the field.',
    talk: 'Hear from practitioners who live and breathe cybersecurity every day.',
    hackathon: 'Put your skills to the test in a fast-paced, collaborative sprint.',
    bootcamp: 'Intensive, focused training designed to take your skills to the next level.',
    ctf: 'Race against the clock to crack challenges most people never see coming.',
    awareness_campaign: 'Learn why cybersecurity matters — and how it affects you directly.',
    social: 'Connect with like-minded people in a relaxed, informal setting.',
    meeting: 'Shape the direction of the club and the events you want to see.',
};

function getTeaser(event: { type: string; title: string; description: string }): string {
    if (typeTeasers[event.type]) {
        return typeTeasers[event.type];
    }
    const plain = event.description.replace(/<[^>]*>/g, '').replace(/&[^;]+;/g, ' ').trim();
    if (plain.length > 0) {
        const sentence = plain.split(/[.!?]+/).find((s) => s.trim().length > 10);
        if (sentence) {
            return sentence.trim() + '.';
        }
    }
    return 'This is one you will not want to miss.';
}

export default function EventDetailModal({ event, onClose }: EventDetailModalProps) {
    const startDate = new Date(event.start_date);
    const endDate = event.end_date ? new Date(event.end_date) : null;

    const formatDate = (d: Date) =>
        d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });

    const formatTime = (d: Date) =>
        d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    const teaser = getTeaser(event);

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" onClick={onClose}>
            <div
                className="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-2xl"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="h-1 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500" />

                <button onClick={onClose} className="absolute right-4 top-5 z-10 rounded-lg p-1 text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <X className="h-4 w-4" />
                </button>

                <div className="p-6">
                    <span className="mb-2 inline-block rounded-full border border-indigo-500/20 bg-indigo-500/[0.08] px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-indigo-400">
                        {typeLabels[event.type] || event.type}
                    </span>

                    <h2 className="text-lg font-bold tracking-tight text-gray-900 dark:text-white pr-8">
                        {event.title}
                    </h2>

                    <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500 dark:text-white/50">
                        <span className="flex items-center gap-1.5">
                            <Calendar className="h-3.5 w-3.5 shrink-0 text-indigo-400" />
                            {formatDate(startDate)}
                        </span>
                        <span className="flex items-center gap-1.5">
                            <Clock className="h-3.5 w-3.5 shrink-0 text-indigo-400" />
                            {formatTime(startDate)}
                            {endDate && ` — ${formatTime(endDate)}`}
                        </span>
                        {event.location && (
                            <span className="flex items-center gap-1.5">
                                <MapPin className="h-3.5 w-3.5 shrink-0 text-indigo-400" />
                                {event.location}
                            </span>
                        )}
                    </div>

                    <div className="mt-5 rounded-xl border border-indigo-500/10 bg-indigo-500/[0.03] p-4">
                        <div className="flex items-start gap-2.5">
                            <Sparkles className="mt-0.5 h-4 w-4 shrink-0 text-indigo-400" />
                            <p className="text-sm leading-relaxed text-gray-600 dark:text-white/60">
                                {teaser}
                            </p>
                        </div>
                    </div>

                    <div className="mt-6">
                        <Link href="/auth/register">
                            <Button className="w-full group gap-2 rounded-full px-6 py-5 text-sm uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25">
                                Join the Club to Attend
                                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                            </Button>
                        </Link>
                        <p className="mt-3 text-center text-xs text-gray-400 dark:text-white/30">
                            Free to join · Members-only access
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
