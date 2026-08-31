import { Link } from '@inertiajs/react';
import PublicLayout from '@/components/PublicLayout';

interface PublicEvent {
    title: string;
    slug: string;
    description: string;
    type: string;
    start_date: string;
    end_date: string | null;
    location: string | null;
    banner_image: string | null;
    display_status: 'upcoming' | 'ongoing' | 'completed';
    categories: { name: string; slug: string; color: string }[];
}

interface EventsProps {
    events: Record<'upcoming' | 'ongoing' | 'completed', PublicEvent[]>;
}

const sections = [
    { key: 'upcoming' as const, title: 'Upcoming', icon: 'event_upcoming' },
    { key: 'ongoing' as const, title: 'Ongoing', icon: 'pending_actions' },
    { key: 'completed' as const, title: 'Completed', icon: 'event_available' },
];

const typeLabels: Record<string, string> = {
    workshop: 'Workshop',
    competition: 'Competition',
    ctf: 'CTF',
    bootcamp: 'Bootcamp',
    awareness_campaign: 'Awareness Campaign',
    talk: 'Talk / Seminar',
    social: 'Social',
    hackathon: 'Hackathon',
};

function Icon({ children, className = '' }: { children: string; className?: string }) {
    return <span className={`material-symbols-outlined ${className}`} aria-hidden="true">{children}</span>;
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatTime(value: string): string {
    return new Date(value).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

export default function Events({ events }: EventsProps) {
    const visibleSections = sections.filter((section) => events[section.key].length > 0);

    return (
        <PublicLayout>
            <section className="border-b border-border bg-card py-14 sm:py-18">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <p className="text-sm font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">SCIC Cyber events</p>
                    <h1 className="mt-3 max-w-3xl text-3xl font-bold tracking-tight text-foreground sm:text-5xl">Learn, connect, and build with the community.</h1>
                    <p className="mt-5 max-w-2xl text-base leading-7 text-muted-foreground">Browse public workshops, competitions, meetups, and club activities. Sign in only when you want to register or participate.</p>
                </div>
            </section>

            <div className="bg-background py-14 sm:py-18">
                <div className="mx-auto flex max-w-7xl flex-col gap-14 px-4 sm:px-6 lg:px-8">
                    {visibleSections.map((section) => (
                        <section key={section.key} aria-labelledby={`${section.key}-heading`}>
                            <div className="flex items-center gap-3">
                                <span className="flex h-10 w-10 items-center justify-center rounded-sm bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                    <Icon className="text-[22px]">{section.icon}</Icon>
                                </span>
                                <div>
                                    <h2 id={`${section.key}-heading`} className="text-2xl font-bold text-foreground">{section.title}</h2>
                                    <p className="text-sm text-muted-foreground">{events[section.key].length} {events[section.key].length === 1 ? 'event' : 'events'}</p>
                                </div>
                            </div>

                            <div className="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                {events[section.key].map((event) => (
                                        <article key={event.slug} className="group flex min-w-0 flex-col overflow-hidden rounded-sm bg-card/80 shadow-theme-xs transition duration-200 hover:-translate-y-1 hover:bg-card hover:shadow-theme-sm dark:bg-card/70">
                                            {event.banner_image ? (
                                                <img src={`/storage/${event.banner_image}`} alt="" className="aspect-[16/8] w-full object-cover" loading="lazy" />
                                            ) : (
                                                <div className="flex aspect-[16/8] items-center justify-center bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                                    <Icon className="text-[42px]">event</Icon>
                                                </div>
                                            )}
                                            <div className="flex flex-1 flex-col p-5">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <span className="rounded-sm bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{event.categories[0]?.name ?? typeLabels[event.type] ?? event.type}</span>
                                                    <span className="text-xs font-semibold capitalize text-muted-foreground">{event.display_status}</span>
                                                </div>
                                                <h3 className="mt-4 text-lg font-semibold text-foreground">{event.title}</h3>
                                                <p className="mt-2 line-clamp-3 flex-1 text-sm leading-6 text-muted-foreground">{event.description}</p>
                                                <dl className="mt-5 flex flex-col gap-2 text-sm text-muted-foreground">
                                                    <div className="flex items-center gap-2"><Icon className="text-[18px]">calendar_month</Icon><dt className="sr-only">Date</dt><dd>{formatDate(event.start_date)}</dd></div>
                                                    <div className="flex items-center gap-2"><Icon className="text-[18px]">schedule</Icon><dt className="sr-only">Time</dt><dd>{formatTime(event.start_date)}{event.end_date ? ` – ${formatTime(event.end_date)}` : ''}</dd></div>
                                                    <div className="flex min-w-0 items-center gap-2"><Icon className="text-[18px]">location_on</Icon><dt className="sr-only">Location</dt><dd className="truncate">{event.location ?? 'Online / TBA'}</dd></div>
                                                </dl>
                                                <Link href={`/events/${event.slug}`} className="mt-5 inline-flex min-h-10 items-center gap-2 self-start rounded-sm text-sm font-semibold text-brand-600 transition-colors hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary dark:text-brand-300 dark:hover:text-brand-200">
                                                    View details <Icon className="text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</Icon>
                                                </Link>
                                            </div>
                                        </article>
                                ))}
                            </div>
                        </section>
                    ))}

                    {visibleSections.length === 0 && (
                        <div className="rounded-sm bg-card/80 px-6 py-12 text-center shadow-theme-xs dark:bg-card/70">
                            <Icon className="text-[36px] text-muted-foreground">event_busy</Icon>
                            <p className="mt-3 font-semibold text-foreground">No public events at the moment.</p>
                            <p className="mt-1 text-sm text-muted-foreground">Check back soon for upcoming club activities.</p>
                        </div>
                    )}
                </div>
            </div>
        </PublicLayout>
    );
}
