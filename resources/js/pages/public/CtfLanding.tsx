import { useState } from 'react';
import { Link } from '@inertiajs/react';
import PublicLayout from '@/components/PublicLayout';
import {
    GlowyWavesBackground,
    WaveSection,
} from '@/components/ui/glowy-waves-hero-shadcnui';
import { Button } from '@/components/ui/button';
import { GlowCard } from '@/components/ui/spotlight-card';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Shield, Terminal, Trophy, Users, Target, Zap,
    ArrowRight, Code, Lock, Search, Bug, Network,
    Crown, Flame, ChevronDown, ChevronUp, CheckCircle2, Star, TrendingUp,
    MessageSquareQuote, Footprints, Flag, Award, History,
} from 'lucide-react';

interface CompetitionCategory {
    name: string;
    color: string;
}

interface Competition {
    id: number;
    title: string;
    slug: string;
    description: string;
    start_date: string;
    end_date: string | null;
    max_score: number;
    allow_teams: boolean;
    max_team_size: number | null;
    challenges_count: number;
    teams_count: number;
    is_active: boolean;
    categories: CompetitionCategory[];
    difficulty_range: string[];
}

interface Category {
    name: string;
    slug: string;
    color: string;
    icon: string | null;
    challenges_count: number;
}

interface TopPlayer {
    name: string;
    rank: string;
    total_points: number;
    solved: number;
}

interface Testimonial {
    quote: string;
    name: string;
    role: string;
    rank: string;
}

interface PastSeason {
    title: string;
    end_date: string;
    challenges_count: number;
    teams_count: number;
    total_solves: number;
    unique_solvers: number;
}

interface Stats {
    total_competitions: number;
    total_challenges: number;
    total_solves: number;
    total_participants: number;
}

interface CtfLandingProps {
    competitions: Competition[];
    categories: Category[];
    topPlayers: TopPlayer[];
    testimonials: Testimonial[];
    pastSeasons: PastSeason[];
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

const competitionBenefits = [
    { icon: TrendingUp, text: 'Build real-world cybersecurity skills employers look for' },
    { icon: Star, text: 'Earn ranks and recognition on the club leaderboard' },
    { icon: Users, text: 'Collaborate with talented peers in team challenges' },
    { icon: CheckCircle2, text: 'Add verified CTF achievements to your portfolio' },
];

const howItWorks = [
    {
        step: 1,
        icon: Footprints,
        title: 'Join the Club',
        description: 'Register as a SLAU-CSIC member. No experience required — just curiosity and a willingness to learn.',
        color: 'text-indigo-400',
        bg: 'bg-indigo-500/20',
        border: 'border-indigo-500/20',
    },
    {
        step: 2,
        icon: Flag,
        title: 'Compete',
        description: 'Enter seasonal CTF competitions. Solve challenges solo or with a team across multiple difficulty levels.',
        color: 'text-emerald-400',
        bg: 'bg-emerald-500/20',
        border: 'border-emerald-500/20',
    },
    {
        step: 3,
        icon: Award,
        title: 'Earn Your Rank',
        description: 'Score points, climb the leaderboard, and earn recognition. Top players become club champions.',
        color: 'text-amber-400',
        bg: 'bg-amber-500/20',
        border: 'border-amber-500/20',
    },
];

const fallbackTestimonials: Testimonial[] = [
    {
        quote: 'I joined SLAU-CSIC with zero cybersecurity knowledge. After my first CTF season, I solved 12 challenges and ranked in the top 10. The mentorship here is unreal.',
        name: 'Sarah Nakamya',
        role: '3rd Year, Computer Science',
        rank: 'gold',
    },
    {
        quote: 'CTF competitions taught me more about web security than any textbook. I now freelance as a penetration tester — and it all started with a club CTF.',
        name: 'David Okello',
        role: '4th Year, Information Technology',
        rank: 'platinum',
    },
    {
        quote: 'The team challenges built my collaboration skills. We competed as a team of 4 and placed 2nd nationally. That experience landed me my first internship.',
        name: 'Grace Achieng',
        role: '2nd Year, Software Engineering',
        rank: 'silver',
    },
];

const faqs = [
    {
        question: 'Do I need experience to join CTF competitions?',
        answer: 'Not at all. Our competitions include beginner-friendly challenges specifically designed for newcomers. Many of our top players started with zero experience and learned through participating.',
    },
    {
        question: 'Is it free to participate?',
        answer: 'Yes. CTF competitions are completely free for all SLAU-CSIC members. There are no hidden fees or requirements beyond your club membership.',
    },
    {
        question: 'What do I need to participate?',
        answer: 'A laptop, an internet connection, and a curious mind. We provide all the challenges, tools, and resources you need. You can use any operating system — Windows, macOS, or Linux.',
    },
    {
        question: 'How often do competitions happen?',
        answer: 'We run seasonal CTF competitions throughout the academic year — typically 3 to 4 major seasons. We also host practice sessions and mini-CTFs every month.',
    },
    {
        question: 'Can I compete as part of a team?',
        answer: 'Yes. Most competitions support teams. You can form a team with fellow members or compete solo. Team sizes typically range from 2 to 5 members.',
    },
    {
        question: 'What skills do I gain from CTF?',
        answer: 'Web exploitation, cryptography, reverse engineering, forensics, networking, and OSINT. These are the exact skills cybersecurity employers look for — and you prove them through competition results.',
    },
];

const categoryIcons: Record<string, React.ComponentType<{ className?: string }>> = {
    web: Code,
    crypto: Lock,
    forensics: Search,
    reverse: Bug,
    networking: Network,
    osint: Target,
};

function getCategoryIcon(slug: string): React.ComponentType<{ className?: string }> {
    const key = Object.keys(categoryIcons).find((k) => slug.toLowerCase().includes(k));
    return key ? categoryIcons[key] : Terminal;
}

function getRankIcon(rank: string): React.ComponentType<{ className?: string }> {
    switch (rank) {
        case 'platinum': return Crown;
        case 'gold': return Trophy;
        case 'silver': return Flame;
        default: return Shield;
    }
}

function getRankColor(rank: string): string {
    switch (rank) {
        case 'platinum': return 'text-cyan-400';
        case 'gold': return 'text-amber-400';
        case 'silver': return 'text-gray-300';
        default: return 'text-orange-400';
    }
}

function getDifficultyLabel(range: string[]): string {
    if (range.length === 0) return 'All levels';
    if (range.length === 1) return range[0];
    return `${range[0]} to ${range[range.length - 1]}`;
}

export default function CtfLanding({ competitions, categories, topPlayers, testimonials, pastSeasons, stats }: CtfLandingProps) {
    const [expandedId, setExpandedId] = useState<number | null>(null);
    const [openFaq, setOpenFaq] = useState<number | null>(null);

    const displayTestimonials = testimonials.length > 0 ? testimonials : fallbackTestimonials;

    return (
        <PublicLayout transparentNav>
            <GlowyWavesBackground>
                {/* Hero */}
                <div className="relative pt-32 pb-16 md:pt-44 md:pb-24">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="max-w-3xl">
                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5 }}
                                className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6"
                            >
                                <Terminal className="h-4 w-4 text-indigo-400" />
                                CTF Arena
                            </motion.div>

                            <motion.h1
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.1 }}
                                className="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight"
                            >
                                Capture the{' '}
                                <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">
                                    Flag
                                </span>
                            </motion.h1>

                            <motion.p
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.2 }}
                                className="text-lg text-white/50 mb-8 max-w-2xl leading-relaxed"
                            >
                                Test your skills against real-world security challenges. From web exploitation
                                to cryptography, forensics to reverse engineering — our seasonal CTF competitions
                                push you to think like an attacker and defend like a pro.
                            </motion.p>

                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.3 }}
                                className="flex flex-wrap gap-3"
                            >
                                <Link href="/auth/register">
                                    <Button size="lg" className="group gap-2 rounded-full px-8 text-base uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25">
                                        Join the Arena
                                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </Button>
                                </Link>
                                <a href="#competitions">
                                    <Button size="lg" variant="outline" className="rounded-full border-white/20 bg-white/5 px-8 text-base text-white/80 backdrop-blur transition-all hover:border-white/40 hover:bg-white/10">
                                        View Competitions
                                    </Button>
                                </a>
                            </motion.div>
                        </div>
                    </div>
                </div>

                {/* Stats Bar */}
                <WaveSection className="py-8">
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        {[
                            { label: 'Competitions', value: stats.total_competitions, icon: Trophy },
                            { label: 'Challenges', value: stats.total_challenges, icon: Target },
                            { label: 'Flags Captured', value: stats.total_solves, icon: Zap },
                            { label: 'Active Players', value: stats.total_participants, icon: Users },
                        ].map((stat, i) => (
                            <motion.div
                                key={stat.label}
                                initial="hidden"
                                whileInView="visible"
                                viewport={{ once: true }}
                                variants={fadeUp}
                                custom={i}
                                className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-sm text-center"
                            >
                                <stat.icon className="h-5 w-5 text-indigo-400 mx-auto mb-2" />
                                <p className="text-2xl md:text-3xl font-bold text-white">{stat.value}</p>
                                <p className="text-xs text-white/40 mt-1 uppercase tracking-wider">{stat.label}</p>
                            </motion.div>
                        ))}
                    </div>
                </WaveSection>

                {/* What is CTF */}
                <WaveSection className="py-16 md:py-24">
                    <div className="max-w-3xl mx-auto text-center">
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <Shield className="h-4 w-4 text-indigo-400" />
                                Learn the Game
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                                What is{' '}
                                <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">
                                    CTF
                                </span>?
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 leading-relaxed text-lg mb-6">
                                Capture The Flag (CTF) is a cybersecurity competition where participants solve
                                security challenges to find hidden &ldquo;flags&rdquo; — secret strings that prove you&apos;ve
                                cracked the challenge. It&apos;s the ultimate hands-on learning experience.
                            </motion.p>
                            <motion.p variants={fadeUp} custom={3} className="text-white/50 leading-relaxed text-lg mb-8">
                                Our competitions span multiple categories — from breaking into web applications
                                to decoding encrypted messages. Whether you&apos;re a beginner or an expert,
                                there&apos;s a challenge waiting for you.
                            </motion.p>

                            <motion.div variants={fadeUp} custom={4} className="flex flex-wrap items-center justify-center gap-6 text-sm text-white/40">
                                <div className="flex items-center gap-2">
                                    <div className="h-2 w-2 rounded-full bg-emerald-400" />
                                    <span>Beginner-friendly</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <div className="h-2 w-2 rounded-full bg-amber-400" />
                                    <span>Team or solo</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <div className="h-2 w-2 rounded-full bg-indigo-400" />
                                    <span>Real-world scenarios</span>
                                </div>
                            </motion.div>
                        </motion.div>
                    </div>
                </WaveSection>

                {/* How It Works */}
                <WaveSection className="py-16 md:py-24">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-100px' }}
                        className="text-center mb-16"
                    >
                        <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                            <Footprints className="h-4 w-4 text-indigo-400" />
                            Getting Started
                        </motion.div>
                        <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                            How It{' '}
                            <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">
                                Works
                            </span>
                        </motion.h2>
                    </motion.div>

                    <div className="relative max-w-4xl mx-auto">
                        <div className="hidden md:block absolute top-16 left-[calc(16.67%+1rem)] right-[calc(16.67%+1rem)] h-px bg-gradient-to-r from-indigo-500/30 via-emerald-500/30 to-amber-500/30" />

                        <div className="grid md:grid-cols-3 gap-8">
                            {howItWorks.map((item, i) => (
                                <motion.div
                                    key={item.step}
                                    initial="hidden"
                                    whileInView="visible"
                                    viewport={{ once: true }}
                                    variants={fadeUp}
                                    custom={i}
                                    className="relative text-center"
                                >
                                    <div className="relative mx-auto mb-6">
                                        <div className={`mx-auto w-16 h-16 rounded-2xl ${item.bg} ${item.border} border flex items-center justify-center`}>
                                            <item.icon className={`h-7 w-7 ${item.color}`} />
                                        </div>
                                        <div className="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-[#0a1320] border border-white/10 flex items-center justify-center">
                                            <span className={`text-xs font-bold ${item.color}`}>{item.step}</span>
                                        </div>
                                    </div>

                                    <h3 className="text-lg font-semibold text-white mb-2">{item.title}</h3>
                                    <p className="text-sm text-white/40 leading-relaxed max-w-xs mx-auto">{item.description}</p>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </WaveSection>

                {/* Categories */}
                {categories.length > 0 && (
                    <WaveSection className="py-16 md:py-24">
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="text-center mb-12"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <Target className="h-4 w-4 text-indigo-400" />
                                Challenge Categories
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                                Pick Your{' '}
                                <span className="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">
                                    Battlefield
                                </span>
                            </motion.h2>
                        </motion.div>

                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                        >
                            {categories.map((cat, i) => {
                                const Icon = getCategoryIcon(cat.slug);
                                return (
                                    <motion.div key={cat.slug} variants={fadeUp} custom={i}>
                                        <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-sm text-center hover:border-white/20 transition-all">
                                            <div
                                                className="rounded-xl p-3 inline-flex mb-3"
                                                style={{ backgroundColor: cat.color + '20' }}
                                            >
                                                <Icon className="h-6 w-6" style={{ color: cat.color }} />
                                            </div>
                                            <h3 className="text-sm font-semibold text-white mb-1">{cat.name}</h3>
                                            <p className="text-xs text-white/40">
                                                {cat.challenges_count} challenge{cat.challenges_count !== 1 ? 's' : ''}
                                            </p>
                                        </div>
                                    </motion.div>
                                );
                            })}
                        </motion.div>
                    </WaveSection>
                )}

                {/* Competitions — Expandable Cards */}
                {competitions.length > 0 && (
                    <WaveSection id="competitions" className="py-16 md:py-24">
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="text-center mb-12"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <Trophy className="h-4 w-4 text-amber-400" />
                                Active & Upcoming
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                                Current{' '}
                                <span className="bg-gradient-to-r from-amber-400 to-orange-300 bg-clip-text text-transparent">
                                    Competitions
                                </span>
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg max-w-xl mx-auto">
                                Click a competition to see what you&apos;ll learn and how it benefits your career.
                            </motion.p>
                        </motion.div>

                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="grid grid-cols-1 gap-4 max-w-4xl mx-auto"
                        >
                            {competitions.map((comp, i) => {
                                const isExpanded = expandedId === comp.id;
                                return (
                                    <motion.div key={comp.id} variants={fadeUp} custom={i}>
                                        <GlowCard
                                            glowColor={comp.is_active ? 'green' : 'blue'}
                                            customSize
                                            className="!p-0 !bg-transparent !border-0 !backdrop-blur-none !shadow-none !gap-0"
                                        >
                                            <button
                                                onClick={() => setExpandedId(isExpanded ? null : comp.id)}
                                                className="w-full text-left rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-sm transition-all hover:border-white/20 hover:bg-white/[0.06]"
                                            >
                                                <div className="p-6">
                                                    <div className="flex items-start justify-between gap-4">
                                                        <div className="flex-1 min-w-0">
                                                            <div className="flex items-center gap-3 mb-2">
                                                                <h3 className="text-base font-semibold text-white">
                                                                    {comp.title}
                                                                </h3>
                                                                {comp.is_active ? (
                                                                    <span className="shrink-0 inline-flex items-center rounded-full bg-emerald-500/20 border border-emerald-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">
                                                                        Live Now
                                                                    </span>
                                                                ) : (
                                                                    <span className="shrink-0 inline-flex items-center rounded-full bg-white/5 border border-white/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-white/50">
                                                                        Upcoming
                                                                    </span>
                                                                )}
                                                            </div>

                                                            {comp.description && (
                                                                <p className="text-sm text-white/40 line-clamp-1 mb-3">
                                                                    {comp.description}
                                                                </p>
                                                            )}

                                                            <div className="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-white/40">
                                                                <span className="flex items-center gap-1.5">
                                                                    <Target className="h-3.5 w-3.5" />
                                                                    {comp.challenges_count} challenges
                                                                </span>
                                                                <span className="flex items-center gap-1.5">
                                                                    <Trophy className="h-3.5 w-3.5 text-amber-400" />
                                                                    {comp.max_score} pts
                                                                </span>
                                                                <span className="flex items-center gap-1.5">
                                                                    <Users className="h-3.5 w-3.5" />
                                                                    {comp.teams_count} teams
                                                                </span>
                                                                {comp.allow_teams && (
                                                                    <span className="flex items-center gap-1.5">
                                                                        <Users className="h-3.5 w-3.5 text-indigo-400" />
                                                                        Teams of {comp.max_team_size}
                                                                    </span>
                                                                )}
                                                                <span className="text-white/30">
                                                                    {new Date(comp.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                                                                    {comp.end_date && (
                                                                        <> — {new Date(comp.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</>
                                                                    )}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <ChevronDown className={`h-5 w-5 text-white/30 shrink-0 transition-transform duration-300 ${isExpanded ? 'rotate-180' : ''}`} />
                                                    </div>
                                                </div>
                                            </button>

                                            <AnimatePresence>
                                                {isExpanded && (
                                                    <motion.div
                                                        initial={{ height: 0, opacity: 0 }}
                                                        animate={{ height: 'auto', opacity: 1 }}
                                                        exit={{ height: 0, opacity: 0 }}
                                                        transition={{ duration: 0.3, ease: 'easeInOut' }}
                                                        className="overflow-hidden"
                                                    >
                                                        <div className="px-6 pb-6 -mt-2">
                                                            <div className="rounded-xl border border-white/10 bg-white/[0.02] p-6">
                                                                {comp.categories.length > 0 && (
                                                                    <div className="mb-5">
                                                                        <p className="text-[11px] font-semibold uppercase tracking-wider text-white/50 mb-3">
                                                                            Challenge Categories
                                                                        </p>
                                                                        <div className="flex flex-wrap gap-2">
                                                                            {comp.categories.map((cat) => (
                                                                                <span
                                                                                    key={cat.name}
                                                                                    className="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium border"
                                                                                    style={{
                                                                                        backgroundColor: cat.color + '15',
                                                                                        borderColor: cat.color + '30',
                                                                                        color: cat.color,
                                                                                    }}
                                                                                >
                                                                                    {cat.name}
                                                                                </span>
                                                                            ))}
                                                                        </div>
                                                                    </div>
                                                                )}

                                                                {comp.difficulty_range.length > 0 && (
                                                                    <div className="mb-5">
                                                                        <p className="text-[11px] font-semibold uppercase tracking-wider text-white/50 mb-2">
                                                                            Difficulty Range
                                                                        </p>
                                                                        <p className="text-sm text-white/70 capitalize">
                                                                            {getDifficultyLabel(comp.difficulty_range)}
                                                                        </p>
                                                                    </div>
                                                                )}

                                                                <div className="mb-5">
                                                                    <p className="text-[11px] font-semibold uppercase tracking-wider text-white/50 mb-3">
                                                                        What You&apos;ll Gain
                                                                    </p>
                                                                    <div className="grid sm:grid-cols-2 gap-3">
                                                                        {competitionBenefits.map((benefit) => (
                                                                            <div key={benefit.text} className="flex items-start gap-2.5">
                                                                                <benefit.icon className="h-4 w-4 text-indigo-400 mt-0.5 shrink-0" />
                                                                                <span className="text-sm text-white/60">{benefit.text}</span>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                </div>

                                                                <div className="flex items-center justify-between pt-4 border-t border-white/5">
                                                                    <p className="text-xs text-white/30">
                                                                        Members-only competition. Join to participate.
                                                                    </p>
                                                                    <Link href="/auth/register">
                                                                        <Button
                                                                            size="sm"
                                                                            className="gap-2 rounded-full px-5 text-xs uppercase tracking-wider bg-indigo-600 hover:bg-indigo-500 text-white"
                                                                        >
                                                                            Join Club to Participate
                                                                            <ArrowRight className="h-3.5 w-3.5" />
                                                                        </Button>
                                                                    </Link>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </motion.div>
                                                )}
                                            </AnimatePresence>
                                        </GlowCard>
                                    </motion.div>
                                );
                            })}
                        </motion.div>
                    </WaveSection>
                )}

                {/* Past Season Highlights */}
                {pastSeasons.length > 0 && (
                    <WaveSection className="py-16 md:py-24">
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="text-center mb-12"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <History className="h-4 w-4 text-amber-400" />
                                Past Seasons
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                                Season{' '}
                                <span className="bg-gradient-to-r from-amber-400 to-orange-300 bg-clip-text text-transparent">
                                    Highlights
                                </span>
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg max-w-xl mx-auto">
                                A look at our previous competitions and the impact they made.
                            </motion.p>
                        </motion.div>

                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto"
                        >
                            {pastSeasons.map((season, i) => (
                                <motion.div
                                    key={season.title}
                                    initial="hidden"
                                    whileInView="visible"
                                    viewport={{ once: true }}
                                    variants={fadeUp}
                                    custom={i}
                                >
                                    <div className="h-full rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-sm flex flex-col">
                                        <div className="flex items-center gap-3 mb-4">
                                            <div className="rounded-xl p-2 bg-amber-500/10 border border-amber-500/20">
                                                <Trophy className="h-5 w-5 text-amber-400" />
                                            </div>
                                            <div>
                                                <h3 className="text-sm font-semibold text-white">{season.title}</h3>
                                                <p className="text-[11px] text-white/40">
                                                    Ended {new Date(season.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-2 gap-3 mt-auto">
                                            <div className="rounded-xl border border-white/5 bg-white/[0.02] p-3 text-center">
                                                <p className="text-lg font-bold text-white">{season.challenges_count}</p>
                                                <p className="text-[10px] text-white/40 uppercase tracking-wider">Challenges</p>
                                            </div>
                                            <div className="rounded-xl border border-white/5 bg-white/[0.02] p-3 text-center">
                                                <p className="text-lg font-bold text-white">{season.teams_count}</p>
                                                <p className="text-[10px] text-white/40 uppercase tracking-wider">Teams</p>
                                            </div>
                                            <div className="rounded-xl border border-white/5 bg-white/[0.02] p-3 text-center">
                                                <p className="text-lg font-bold text-white">{season.total_solves}</p>
                                                <p className="text-[10px] text-white/40 uppercase tracking-wider">Total Solves</p>
                                            </div>
                                            <div className="rounded-xl border border-white/5 bg-white/[0.02] p-3 text-center">
                                                <p className="text-lg font-bold text-white">{season.unique_solvers}</p>
                                                <p className="text-[10px] text-white/40 uppercase tracking-wider">Unique Solvers</p>
                                            </div>
                                        </div>
                                    </div>
                                </motion.div>
                            ))}
                        </motion.div>
                    </WaveSection>
                )}

                {/* Testimonials */}
                <WaveSection className="py-16 md:py-24">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-100px' }}
                        className="text-center mb-12"
                    >
                        <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                            <MessageSquareQuote className="h-4 w-4 text-indigo-400" />
                            Member Stories
                        </motion.div>
                        <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                            Hear From{' '}
                            <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">
                                Our Players
                            </span>
                        </motion.h2>
                        <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg max-w-xl mx-auto">
                            Real experiences from members who started just like you.
                        </motion.p>
                    </motion.div>

                    <div className="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                        {displayTestimonials.map((t, i) => {
                            const tRankColor = getRankColor(t.rank);
                            return (
                                <motion.div
                                    key={t.name}
                                    initial="hidden"
                                    whileInView="visible"
                                    viewport={{ once: true }}
                                    variants={fadeUp}
                                    custom={i}
                                >
                                    <div className="h-full rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-sm flex flex-col">
                                        <div className="mb-4">
                                            <svg className="h-8 w-8 text-indigo-500/30" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                            </svg>
                                        </div>

                                        <p className="text-sm text-white/60 leading-relaxed mb-6 flex-1">
                                            &ldquo;{t.quote}&rdquo;
                                        </p>

                                        <div className="flex items-center gap-3 pt-4 border-t border-white/5">
                                            <div className="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500/30 to-violet-500/30 border border-white/10 flex items-center justify-center">
                                                <span className="text-sm font-bold text-white">
                                                    {t.name.split(' ').map((n) => n[0]).join('')}
                                                </span>
                                            </div>
                                            <div>
                                                <p className="text-sm font-semibold text-white">{t.name}</p>
                                                <div className="flex items-center gap-2">
                                                    <p className="text-xs text-white/40">{t.role}</p>
                                                    <span className={`text-[10px] font-semibold uppercase tracking-wider ${tRankColor}`}>
                                                        {t.rank}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </motion.div>
                            );
                        })}
                    </div>

                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true }}
                        variants={fadeUp}
                        custom={0}
                        className="mt-8 text-center"
                    >
                        <Link
                            href="/ctf-arena/testimonial"
                            className="group inline-flex items-center gap-2 text-sm font-medium text-indigo-400 hover:text-indigo-300 transition-colors"
                        >
                            Share your experience
                            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </Link>
                    </motion.div>
                </WaveSection>
                {topPlayers.length > 0 && (
                    <WaveSection className="py-16 md:py-24">
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="text-center mb-12"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <Crown className="h-4 w-4 text-amber-400" />
                                Top Players
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                                Hall of{' '}
                                <span className="bg-gradient-to-r from-amber-400 to-orange-300 bg-clip-text text-transparent">
                                    Champions
                                </span>
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg max-w-xl mx-auto">
                                The best flag hunters in our community. Think you can top the board?
                            </motion.p>
                        </motion.div>

                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="max-w-2xl mx-auto"
                        >
                            <div className="rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-sm overflow-hidden">
                                {topPlayers.map((player, i) => {
                                    const RankIcon = getRankIcon(player.rank);
                                    const rankColor = getRankColor(player.rank);
                                    return (
                                        <motion.div
                                            key={player.name}
                                            variants={fadeUp}
                                            custom={i}
                                            className={`flex items-center gap-4 px-6 py-4 ${
                                                i !== topPlayers.length - 1 ? 'border-b border-white/5' : ''
                                            }`}
                                        >
                                            <span className={`text-lg font-bold w-6 text-center ${
                                                i === 0 ? 'text-amber-400' :
                                                i === 1 ? 'text-gray-300' :
                                                i === 2 ? 'text-orange-400' : 'text-white/30'
                                            }`}>
                                                {i + 1}
                                            </span>

                                            <div className="rounded-xl p-2 bg-white/5 border border-white/10">
                                                <RankIcon className={`h-5 w-5 ${rankColor}`} />
                                            </div>

                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-semibold text-white truncate">{player.name}</p>
                                                <p className="text-xs text-white/40 capitalize">{player.rank}</p>
                                            </div>

                                            <div className="text-right">
                                                <p className={`text-sm font-bold ${rankColor}`}>{player.total_points}</p>
                                                <p className="text-[10px] text-white/30 uppercase tracking-wider">
                                                    {player.solved} solved
                                                </p>
                                            </div>
                                        </motion.div>
                                    );
                                })}
                            </div>

                            <motion.div
                                initial="hidden"
                                whileInView="visible"
                                viewport={{ once: true }}
                                variants={fadeUp}
                                custom={0}
                                className="mt-6 text-center"
                            >
                                <Link
                                    href="/leaderboard"
                                    className="group inline-flex items-center gap-2 text-sm font-medium text-indigo-400 hover:text-indigo-300 transition-colors"
                                >
                                    View full leaderboard
                                    <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </Link>
                            </motion.div>
                        </motion.div>
                    </WaveSection>
                )}

                {/* FAQ */}
                <WaveSection className="py-16 md:py-24">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-100px' }}
                        className="text-center mb-12"
                    >
                        <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                            <CheckCircle2 className="h-4 w-4 text-indigo-400" />
                            Common Questions
                        </motion.div>
                        <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                            Frequently{' '}
                            <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">
                                Asked
                            </span>
                        </motion.h2>
                    </motion.div>

                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-50px' }}
                        className="max-w-2xl mx-auto space-y-3"
                    >
                        {faqs.map((faq, i) => {
                            const isOpen = openFaq === i;
                            return (
                                <motion.div
                                    key={faq.question}
                                    variants={fadeUp}
                                    custom={i}
                                    className="rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-sm overflow-hidden"
                                >
                                    <button
                                        onClick={() => setOpenFaq(isOpen ? null : i)}
                                        className="w-full flex items-center justify-between gap-4 p-5 text-left"
                                    >
                                        <span className="text-sm font-medium text-white">{faq.question}</span>
                                        {isOpen ? (
                                            <ChevronUp className="h-4 w-4 text-white/40 shrink-0" />
                                        ) : (
                                            <ChevronDown className="h-4 w-4 text-white/40 shrink-0" />
                                        )}
                                    </button>
                                    <AnimatePresence>
                                        {isOpen && (
                                            <motion.div
                                                initial={{ height: 0, opacity: 0 }}
                                                animate={{ height: 'auto', opacity: 1 }}
                                                exit={{ height: 0, opacity: 0 }}
                                                transition={{ duration: 0.25, ease: 'easeInOut' }}
                                                className="overflow-hidden"
                                            >
                                                <div className="px-5 pb-5 -mt-1">
                                                    <p className="text-sm text-white/40 leading-relaxed">{faq.answer}</p>
                                                </div>
                                            </motion.div>
                                        )}
                                    </AnimatePresence>
                                </motion.div>
                            );
                        })}
                    </motion.div>
                </WaveSection>

                {/* CTA */}
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
                                <Zap className="h-4 w-4 text-indigo-400" />
                                Ready?
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-5xl font-bold text-white mb-4">
                                Think You Can{' '}
                                <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">
                                    Capture It
                                </span>?
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg mb-8 max-w-xl mx-auto leading-relaxed">
                                Join SLAU-CSIC and compete in our next CTF season. No experience needed —
                                just curiosity, determination, and a willingness to learn.
                            </motion.p>
                            <motion.div variants={fadeUp} custom={3} className="flex flex-col sm:flex-row items-center justify-center gap-4">
                                <Link href="/auth/register">
                                    <Button size="lg" className="group gap-2 rounded-full px-8 text-base uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25">
                                        Join SLAU-CSIC
                                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </Button>
                                </Link>
                                <Link href="/leaderboard">
                                    <Button size="lg" variant="outline" className="rounded-full border-white/20 bg-white/5 px-8 text-base text-white/80 backdrop-blur transition-all hover:border-white/40 hover:bg-white/10">
                                        View Leaderboard
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
