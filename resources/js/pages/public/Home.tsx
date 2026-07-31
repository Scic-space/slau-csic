import { Link } from '@inertiajs/react';
import PublicLayout from '@/components/PublicLayout';
import {
    GlowyWavesBackground,
    HeroContent,
    WaveSection,
} from '@/components/ui/glowy-waves-hero-shadcnui';
import { Button } from '@/components/ui/button';
import { PillarCard } from '@/components/ui/pillar-card';
import { GlowCard } from '@/components/ui/spotlight-card';
import { motion } from 'framer-motion';
import {
    ArrowRight,
    Shield,
    Terminal,
    BookOpen,
    Trophy,
    Code2,
    Network,
    Zap,
    Rocket,
} from 'lucide-react';
import { useState } from 'react';
import EventDetailModal from '@/components/EventDetailModal';
import AnnouncementDetailModal from '@/components/AnnouncementDetailModal';

interface EventItem {
    id: number;
    title: string;
    slug: string;
    description: string;
    type: string;
    start_date: string;
    end_date: string | null;
    location: string | null;
    skill_level: string | null;
    is_recurring: boolean;
    categories: { name: string; slug: string; color: string }[];
}

interface AnnouncementItem {
    id: number;
    title: string;
    content: string;
    type: string;
    published_at: string | null;
}

interface Stats {
    projects: number;
    members: number;
    events: number;
}

interface HomeProps {
    upcomingEvents: EventItem[];
    announcements: AnnouncementItem[];
    stats: Stats;
}

const fadeUp = {
    hidden: { opacity: 0, y: 20 },
    visible: (i: number) => ({
        opacity: 1,
        y: 0,
        transition: { duration: 0.4, delay: i * 0.06, ease: 'easeOut' },
    }),
};

export default function Home({ upcomingEvents, announcements, stats }: HomeProps) {
    const [selectedEvent, setSelectedEvent] = useState<EventItem | null>(null);
    const [selectedAnnouncement, setSelectedAnnouncement] = useState<AnnouncementItem | null>(null);
    const pillars: {
        icon: React.ComponentType<{ className?: string }>;
        title: string;
        description: string;
        tags: string[];
        frequency: string;
        statValue?: string;
        statLabel?: string;
        accentColor: string;
        accentBg: string;
        accentBorder: string;
        glowColor: 'blue' | 'purple' | 'green' | 'orange';
        href: string;
    }[] = [
        {
            icon: Terminal, title: 'CTF Competitions',
            description: 'Hone your skills in capture-the-flag challenges spanning web exploitation, cryptography, reverse engineering, and forensics. Compete, learn, and earn your rank.',
            tags: ['Security', 'CTF'], frequency: 'Seasonal',
            statValue: String(stats.events), statLabel: 'active challenges this season',
            accentColor: 'text-indigo-400', accentBg: 'bg-indigo-500/20', accentBorder: 'border-indigo-500/20',
            glowColor: 'blue', href: '/ctf-arena',
        },
        {
            icon: BookOpen, title: 'Hands-on Workshops',
            description: 'Learn by doing. Our weekly workshops cover ethical hacking, network defense, coding, and emerging tech — no experience required.',
            tags: ['Learning', 'Mentorship'], frequency: 'Weekly',
            accentColor: 'text-emerald-400', accentBg: 'bg-emerald-500/20', accentBorder: 'border-emerald-500/20',
            glowColor: 'green', href: '/workshops',
        },
        {
            icon: Code2, title: 'Community Projects',
            description: 'Collaborate on open-source tools, security utilities, and impactful campus solutions. Ship code that matters.',
            tags: ['Collaboration', 'Impact'], frequency: 'Ongoing',
            statValue: String(stats.projects), statLabel: 'active open-source projects',
            accentColor: 'text-purple-400', accentBg: 'bg-purple-500/20', accentBorder: 'border-purple-500/20',
            glowColor: 'purple', href: '/projects',
        },
        {
            icon: Network, title: 'Networking & Industry',
            description: 'Connect with cybersecurity professionals, alumni, and recruiters. Build relationships that launch careers.',
            tags: ['Community', 'Growth'], frequency: 'Monthly',
            statValue: String(stats.members), statLabel: 'members in our network',
            accentColor: 'text-sky-400', accentBg: 'bg-sky-500/20', accentBorder: 'border-sky-500/20',
            glowColor: 'orange', href: '/about',
        },
    ];

    return (
        <PublicLayout transparentNav>
            <GlowyWavesBackground>
                {/* Section 1: Hero */}
                <div>
                    <HeroContent stats={stats} upcomingEvents={upcomingEvents} />
                </div>

                {/* Gradient divider */}
                <div className="relative h-32 w-full overflow-hidden">
                    <div className="absolute inset-0 bg-gradient-to-b from-transparent via-indigo-500/5 to-transparent" />
                </div>

                {/* Section 2: What We Do — Pillars */}
                <WaveSection className="py-16 md:py-32">
                    <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-100px' }} className="text-center mb-16">
                        <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                            <Shield className="h-4 w-4 text-indigo-400" />
                            The Arena
                        </motion.div>
                        <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-5xl font-bold text-white mb-4">
                            What We <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">Build</span> Together
                        </motion.h2>
                        <motion.p variants={fadeUp} custom={2} className="text-white/50 max-w-2xl mx-auto text-lg">
                            Four ways we level up.
                        </motion.p>
                    </motion.div>
                    <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-50px' }} className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {pillars.map((pillar, i) => (
                            <motion.div key={pillar.title} variants={fadeUp} custom={i}>
                                <PillarCard {...pillar} />
                            </motion.div>
                        ))}
                    </motion.div>
                </WaveSection>

                {/* Section 3: Upcoming Events */}
                {upcomingEvents.length > 0 && (
                    <WaveSection id="upcoming-events" className="py-16 md:py-32">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-100px' }} className="flex items-end justify-between mb-12">
                            <div>
                                <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-4">
                                    <Trophy className="h-4 w-4 text-indigo-400" />
                                    What&apos;s Happening
                                </motion.div>
                                <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-5xl font-bold text-white mb-2">
                                    Upcoming <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">Events</span>
                                </motion.h2>
                                <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg">
                                    Workshops, competitions, and meetups — mark your calendar.
                                </motion.p>
                            </div>
                        </motion.div>
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="grid grid-cols-1 gap-6 md:grid-cols-3"
                        >
                            {upcomingEvents.map((event, i) => (
                                <motion.div key={event.id} variants={fadeUp} custom={i}>
                                    <GlowCard glowColor="blue" customSize className="!p-0 !bg-transparent !border-0 !backdrop-blur-none !shadow-none !gap-0 h-full">
                                        <button
                                            onClick={() => setSelectedEvent(event)}
                                            className="group block w-full rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-sm transition-all hover:border-indigo-500/30 hover:bg-white/[0.06] text-left cursor-pointer"
                                        >
                                            <div className="mb-4 flex items-center gap-3">
                                                <div className="rounded-xl bg-indigo-500/20 px-4 py-2 text-center leading-tight border border-indigo-500/20">
                                                    <p className="text-xs font-bold text-indigo-400 uppercase tracking-wider">
                                                        {new Date(event.start_date).toLocaleDateString('en-US', { month: 'short' })}
                                                    </p>
                                                    <p className="text-xl font-bold text-white">
                                                        {new Date(event.start_date).getDate()}
                                                    </p>
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <h3 className="line-clamp-2 text-sm font-semibold text-white transition-colors group-hover:text-indigo-400">
                                                        {event.title}
                                                    </h3>
                                                    <p className="text-xs text-white/40">
                                                        {new Date(event.start_date).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                                                    </p>
                                                </div>
                                            </div>
                                        <div className="mb-3 flex flex-wrap gap-1.5">
                                            {event.categories.map((cat) => (
                                                <span
                                                    key={cat.name}
                                                    className="rounded-full px-2.5 py-0.5 text-[11px] font-medium"
                                                    style={{ backgroundColor: cat.color + '25', color: cat.color }}
                                                >
                                                    {cat.name}
                                                </span>
                                            ))}
                                            {event.is_recurring && (
                                                <span className="rounded-full px-2.5 py-0.5 text-[11px] font-medium border-purple-500/20 bg-purple-500/10 text-purple-400">
                                                    Recurring
                                                </span>
                                            )}
                                        </div>
                                        {event.location && (
                                            <div className="flex items-center gap-1.5 text-xs text-white/40">
                                                <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span className="truncate">{event.location}</span>
                                            </div>
                                        )}
                                    </button>
                                    </GlowCard>
                                </motion.div>
                            ))}
                        </motion.div>
                    </WaveSection>
                )}

                {/* Section 4: Latest News */}
                {announcements.length > 0 && (
                    <WaveSection className="py-16 md:py-32">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-100px' }} className="mb-12">
                            <div>
                                <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-4">
                                    <Zap className="h-4 w-4 text-indigo-400" />
                                    Latest
                                </motion.div>
                                <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-5xl font-bold text-white mb-2">
                                    The <span className="bg-gradient-to-r from-amber-400 to-orange-300 bg-clip-text text-transparent">Pulse</span>
                                </motion.h2>
                                <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg">
                                    Recent achievements, updates, and announcements from the club.
                                </motion.p>
                            </div>
                        </motion.div>
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="grid grid-cols-1 gap-6 md:grid-cols-2"
                        >
                            {announcements.map((a, i) => (
                                <motion.div key={a.id} variants={fadeUp} custom={i}>
                                    <GlowCard glowColor={i === 0 ? 'purple' : 'blue'} customSize className="!p-0 !bg-transparent !border-0 !backdrop-blur-none !shadow-none !gap-0 h-full">
                                        <button
                                            onClick={() => setSelectedAnnouncement(a)}
                                            className="group block w-full rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-sm transition-all hover:border-indigo-500/30 hover:bg-white/[0.06] text-left cursor-pointer"
                                        >
                                        <div className="flex items-start gap-4">
                                            <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border backdrop-blur-sm ${
                                                a.type === 'urgent'
                                                    ? 'bg-red-500/20 text-red-400 border-red-500/20'
                                                    : a.type === 'achievement'
                                                        ? 'bg-amber-500/20 text-amber-400 border-amber-500/20'
                                                        : 'bg-indigo-500/20 text-indigo-400 border-indigo-500/20'
                                            }`}>
                                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d={a.type === 'urgent' ? 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : a.type === 'achievement' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'} />
                                                </svg>
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <h3 className="font-semibold text-white transition-colors group-hover:text-indigo-400">
                                                    {a.title}
                                                </h3>
                                                <p className="mt-1 text-sm text-white/50 line-clamp-2">{a.content}</p>
                                                {a.published_at && (
                                                    <p className="mt-2 text-xs text-white/40">
                                                        {new Date(a.published_at).toLocaleDateString('en-US', {
                                                            month: 'short', day: 'numeric', year: 'numeric',
                                                        })}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </button>
                                    </GlowCard>
                                </motion.div>
                            ))}
                        </motion.div>
                    </WaveSection>
                )}

                {selectedEvent && (
                    <EventDetailModal event={selectedEvent} onClose={() => setSelectedEvent(null)} />
                )}

                {selectedAnnouncement && (
                    <AnnouncementDetailModal announcement={selectedAnnouncement} onClose={() => setSelectedAnnouncement(null)} />
                )}

                {/* Section 5: CTA — Join */}
                <WaveSection className="pb-24 pt-16 md:pb-40 md:pt-32">
                    <div className="relative">
                        <div className="absolute inset-0 bg-gradient-to-b from-indigo-500/5 via-purple-500/5 to-transparent rounded-3xl blur-3xl" />
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="relative mx-auto max-w-3xl text-center rounded-3xl border border-white/10 bg-white/[0.02] px-6 py-10 sm:px-8 sm:py-16 backdrop-blur-sm"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <Rocket className="h-4 w-4 text-indigo-400" />
                                Get Started
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-5xl font-bold text-white mb-4">
                                Ready to <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">Hack</span> the Future?
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg mb-8 max-w-xl mx-auto leading-relaxed">
                                No dues. No prerequisites. Just curiosity and a willingness to learn.
                                Whether you&apos;ve never written a line of code or you&apos;re already breaking into
                                boxes — there&apos;s a place for you here.
                            </motion.p>
                            <motion.div variants={fadeUp} custom={3} className="flex flex-col sm:flex-row items-center justify-center gap-4">
                                <Link href="/auth/register">
                                    <Button size="lg" className="group gap-2 rounded-full px-8 text-base uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25">
                                        Join SLAU-CSIC
                                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </Button>
                                </Link>
                                <Link href="/contact">
                                    <Button size="lg" variant="outline" className="rounded-full border-white/20 bg-white/5 px-8 text-base text-white/80 backdrop-blur transition-all hover:border-white/40 hover:bg-white/10">
                                        Ask a Question
                                    </Button>
                                </Link>
                            </motion.div>
                        </motion.div>
                    </div>
                </WaveSection>
            </GlowyWavesBackground>
        </PublicLayout>
    );
}
