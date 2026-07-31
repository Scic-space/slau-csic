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
    BookOpen, Terminal, Users, Target, Zap,
    ArrowRight, Code, Lock, Search, Bug, Network,
    ChevronDown, ChevronUp, Star, GraduationCap,
    Calendar, MapPin, Award, Clock, TrendingUp, MessageSquareQuote,
    CheckCircle2, Footprints, Lightbulb, Shield,
} from 'lucide-react';

interface WorkshopCategory {
    name: string;
    slug: string;
    color: string;
    icon: string | null;
    events_count: number;
}

interface WorkshopInstructor {
    name: string;
}

interface Workshop {
    id: number;
    title: string;
    slug: string;
    description: string;
    start_date: string;
    end_date: string | null;
    location: string;
    skill_level: string | null;
    max_participants: number | null;
    registrations_count: number;
    registration_required: boolean;
    is_full: boolean;
    learning_objectives: string | null;
    requirements: string | null;
    categories: WorkshopCategory[];
    organizer: { name: string };
    instructors: WorkshopInstructor[];
}

interface PastHighlight {
    title: string;
    start_date: string;
    registered_count: number;
    average_rating: number;
    skill_level: string | null;
}

interface TopInstructor {
    name: string;
    workshops_count: number;
    average_rating: number;
    total_feedback: number;
}

interface Stats {
    total_workshops: number;
    total_attendees: number;
    total_feedback: number;
    average_rating: number;
}

interface WorkshopLandingProps {
    upcomingWorkshops: Workshop[];
    categories: WorkshopCategory[];
    pastHighlights: PastHighlight[];
    topInstructors: TopInstructor[];
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
        icon: Calendar,
        title: 'Browse & Register',
        description: 'Explore upcoming workshops by topic and difficulty. Reserve your spot — most are free for members.',
        color: 'text-emerald-400',
        bg: 'bg-emerald-500/20',
        border: 'border-emerald-500/20',
    },
    {
        step: 3,
        icon: Lightbulb,
        title: 'Learn & Build',
        description: 'Show up, get hands-on, and walk away with real skills. Slides, code, and resources shared after each session.',
        color: 'text-amber-400',
        bg: 'bg-amber-500/20',
        border: 'border-amber-500/20',
    },
];

const workshopBenefits = [
    { icon: TrendingUp, text: 'Build practical cybersecurity and tech skills' },
    { icon: Users, text: 'Learn from experienced peers and industry professionals' },
    { icon: Star, text: 'Hands-on labs — not just slides and theory' },
    { icon: CheckCircle2, text: 'Resources shared after every session' },
];

const faqs = [
    {
        question: 'Do I need experience to attend workshops?',
        answer: 'Not at all. Our workshops range from beginner to advanced, and each one clearly labels its skill level. Many workshops are designed specifically for newcomers.',
    },
    {
        question: 'Are workshops free?',
        answer: 'Yes. All workshops are completely free for SLAU-CSIC members. There are no hidden fees.',
    },
    {
        question: 'What do I need to bring?',
        answer: 'A laptop with internet access. We provide all the tools, materials, and resources you need. You can use any operating system.',
    },
    {
        question: 'How often are workshops held?',
        answer: 'We run workshops weekly throughout the academic year. Topics rotate across web security, networking, crypto, cloud, and emerging tech.',
    },
    {
        question: 'Can I suggest a workshop topic?',
        answer: 'Absolutely. We take member suggestions seriously. Reach out to the team or submit a proposal through the club portal.',
    },
    {
        question: 'Will I get materials after the workshop?',
        answer: 'Yes. Every workshop includes shared slides, code repositories, and reference materials so you can review and practice afterward.',
    },
];

const categoryIcons: Record<string, React.ComponentType<{ className?: string }>> = {
    web: Code,
    crypto: Lock,
    forensics: Search,
    reverse: Bug,
    networking: Network,
    training: GraduationCap,
    workshop: BookOpen,
    bootcamp: Shield,
};

function getCategoryIcon(slug: string): React.ComponentType<{ className?: string }> {
    const key = Object.keys(categoryIcons).find((k) => slug.toLowerCase().includes(k));
    return key ? categoryIcons[key] : BookOpen;
}

function getSkillLevelColor(level: string | null): string {
    switch (level?.toLowerCase()) {
        case 'beginner': return 'text-emerald-400 bg-emerald-500/15 border-emerald-500/20';
        case 'intermediate': return 'text-amber-400 bg-amber-500/15 border-amber-500/20';
        case 'advanced': return 'text-red-400 bg-red-500/15 border-red-500/20';
        default: return 'text-white/40 bg-white/5 border-white/10';
    }
}

function getSkillLevelDot(level: string | null): string {
    switch (level?.toLowerCase()) {
        case 'beginner': return 'bg-emerald-400';
        case 'intermediate': return 'bg-amber-400';
        case 'advanced': return 'bg-red-400';
        default: return 'bg-white/40';
    }
}

function formatWorkshopDate(isoString: string): string {
    const date = new Date(isoString);
    return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
}

function formatWorkshopTime(isoString: string): string {
    const date = new Date(isoString);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

export default function WorkshopLanding({ upcomingWorkshops, categories, pastHighlights, topInstructors, stats }: WorkshopLandingProps) {
    const [expandedId, setExpandedId] = useState<number | null>(null);
    const [openFaq, setOpenFaq] = useState<number | null>(null);

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
                                <BookOpen className="h-4 w-4 text-emerald-400" />
                                Hands-on Workshops
                            </motion.div>

                            <motion.h1
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.1 }}
                                className="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight"
                            >
                                Learn by{' '}
                                <span className="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">
                                    Doing
                                </span>
                            </motion.h1>

                            <motion.p
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.2 }}
                                className="text-lg text-white/50 mb-8 max-w-2xl leading-relaxed"
                            >
                                Weekly hands-on workshops covering ethical hacking, network defense,
                                cloud security, and emerging tech. No lectures — just labs, code, and real skills
                                you can apply immediately.
                            </motion.p>

                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.3 }}
                                className="flex flex-wrap gap-3"
                            >
                                <Link href="/auth/register">
                                    <Button size="lg" className="group gap-2 rounded-full px-8 text-base uppercase tracking-[0.2em] bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/25">
                                        Join the Club
                                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </Button>
                                </Link>
                                <a href="#upcoming">
                                    <Button size="lg" variant="outline" className="rounded-full border-white/20 bg-white/5 px-8 text-base text-white/80 backdrop-blur transition-all hover:border-white/40 hover:bg-white/10">
                                        View Workshops
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
                            { label: 'Workshops Held', value: stats.total_workshops, icon: BookOpen },
                            { label: 'Attendees', value: stats.total_attendees, icon: Users },
                            { label: 'Avg Rating', value: stats.average_rating > 0 ? `${stats.average_rating}/5` : 'N/A', icon: Star },
                            { label: 'Reviews', value: stats.total_feedback, icon: MessageSquareQuote },
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
                                <stat.icon className="h-5 w-5 text-emerald-400 mx-auto mb-2" />
                                <p className="text-2xl md:text-3xl font-bold text-white">{stat.value}</p>
                                <p className="text-xs text-white/40 mt-1 uppercase tracking-wider">{stat.label}</p>
                            </motion.div>
                        ))}
                    </div>
                </WaveSection>

                {/* What We Cover */}
                {categories.length > 0 && (
                    <WaveSection className="py-16 md:py-24">
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="text-center mb-12"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <Target className="h-4 w-4 text-emerald-400" />
                                What We Cover
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                                Skills That{' '}
                                <span className="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">
                                    Matter
                                </span>
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg max-w-xl mx-auto">
                                Topics chosen by the community, taught by practitioners.
                            </motion.p>
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
                                                {cat.events_count} workshop{cat.events_count !== 1 ? 's' : ''}
                                            </p>
                                        </div>
                                    </motion.div>
                                );
                            })}
                        </motion.div>
                    </WaveSection>
                )}

                {/* How It Works */}
                <WaveSection className="py-16 md:py-24">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-100px' }}
                        className="text-center mb-16"
                    >
                        <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                            <Footprints className="h-4 w-4 text-emerald-400" />
                            Getting Started
                        </motion.div>
                        <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                            How It{' '}
                            <span className="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">
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

                {/* Upcoming Workshops — Expandable Cards */}
                {upcomingWorkshops.length > 0 && (
                    <WaveSection id="upcoming" className="py-16 md:py-24">
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="text-center mb-12"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <Calendar className="h-4 w-4 text-emerald-400" />
                                Coming Up
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                                Upcoming{' '}
                                <span className="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">
                                    Workshops
                                </span>
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg max-w-xl mx-auto">
                                Click a workshop to see what you&apos;ll learn and how to prepare.
                            </motion.p>
                        </motion.div>

                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="grid grid-cols-1 gap-4 max-w-4xl mx-auto"
                        >
                            {upcomingWorkshops.map((workshop, i) => {
                                const isExpanded = expandedId === workshop.id;
                                const spotsLeft = workshop.max_participants
                                    ? workshop.max_participants - workshop.registrations_count
                                    : null;

                                return (
                                    <motion.div key={workshop.id} variants={fadeUp} custom={i}>
                                        <GlowCard
                                            glowColor="green"
                                            customSize
                                            className="!p-0 !bg-transparent !border-0 !backdrop-blur-none !shadow-none !gap-0"
                                        >
                                            <button
                                                onClick={() => setExpandedId(isExpanded ? null : workshop.id)}
                                                className="w-full text-left rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-sm transition-all hover:border-white/20 hover:bg-white/[0.06]"
                                            >
                                                <div className="p-6">
                                                    <div className="flex items-start justify-between gap-4">
                                                        <div className="flex-1 min-w-0">
                                                            <div className="flex items-center gap-3 mb-2">
                                                                <h3 className="text-base font-semibold text-white">
                                                                    {workshop.title}
                                                                </h3>
                                                                {workshop.is_full ? (
                                                                    <span className="shrink-0 inline-flex items-center rounded-full bg-red-500/20 border border-red-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-red-400">
                                                                        Full
                                                                    </span>
                                                                ) : workshop.skill_level ? (
                                                                    <span className={`shrink-0 inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider ${getSkillLevelColor(workshop.skill_level)}`}>
                                                                        {workshop.skill_level}
                                                                    </span>
                                                                ) : null}
                                                            </div>

                                                            {workshop.description && (
                                                                <p className="text-sm text-white/40 line-clamp-1 mb-3">
                                                                    {workshop.description}
                                                                </p>
                                                            )}

                                                            <div className="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-white/40">
                                                                <span className="flex items-center gap-1.5">
                                                                    <Calendar className="h-3.5 w-3.5 text-emerald-400" />
                                                                    {formatWorkshopDate(workshop.start_date)}
                                                                </span>
                                                                <span className="flex items-center gap-1.5">
                                                                    <Clock className="h-3.5 w-3.5" />
                                                                    {formatWorkshopTime(workshop.start_date)}
                                                                    {workshop.end_date && (
                                                                        <> — {formatWorkshopTime(workshop.end_date)}</>
                                                                    )}
                                                                </span>
                                                                <span className="flex items-center gap-1.5">
                                                                    <MapPin className="h-3.5 w-3.5" />
                                                                    {workshop.location}
                                                                </span>
                                                                {spotsLeft !== null && (
                                                                    <span className={`flex items-center gap-1.5 ${spotsLeft <= 3 ? 'text-amber-400' : ''}`}>
                                                                        <Users className="h-3.5 w-3.5" />
                                                                        {spotsLeft > 0 ? `${spotsLeft} spots left` : 'Full'}
                                                                    </span>
                                                                )}
                                                                {workshop.instructors.length > 0 && (
                                                                    <span className="flex items-center gap-1.5">
                                                                        <GraduationCap className="h-3.5 w-3.5 text-indigo-400" />
                                                                        {workshop.instructors.map((inst) => inst.name).join(', ')}
                                                                    </span>
                                                                )}
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
                                                                {/* Categories */}
                                                                {workshop.categories.length > 0 && (
                                                                    <div className="mb-5">
                                                                        <p className="text-[11px] font-semibold uppercase tracking-wider text-white/50 mb-3">
                                                                            Topics Covered
                                                                        </p>
                                                                        <div className="flex flex-wrap gap-2">
                                                                            {workshop.categories.map((cat) => (
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

                                                                {/* Learning Objectives */}
                                                                {workshop.learning_objectives && (
                                                                    <div className="mb-5">
                                                                        <p className="text-[11px] font-semibold uppercase tracking-wider text-white/50 mb-2">
                                                                            What You&apos;ll Learn
                                                                        </p>
                                                                        <p className="text-sm text-white/60 leading-relaxed whitespace-pre-line">
                                                                            {workshop.learning_objectives}
                                                                        </p>
                                                                    </div>
                                                                )}

                                                                {/* Requirements */}
                                                                {workshop.requirements && (
                                                                    <div className="mb-5">
                                                                        <p className="text-[11px] font-semibold uppercase tracking-wider text-white/50 mb-2">
                                                                            Requirements
                                                                        </p>
                                                                        <p className="text-sm text-white/60 leading-relaxed whitespace-pre-line">
                                                                            {workshop.requirements}
                                                                        </p>
                                                                    </div>
                                                                )}

                                                                {/* What You'll Gain */}
                                                                <div className="mb-5">
                                                                    <p className="text-[11px] font-semibold uppercase tracking-wider text-white/50 mb-3">
                                                                        Why Attend
                                                                    </p>
                                                                    <div className="grid sm:grid-cols-2 gap-3">
                                                                        {workshopBenefits.map((benefit) => (
                                                                            <div key={benefit.text} className="flex items-start gap-2.5">
                                                                                <benefit.icon className="h-4 w-4 text-emerald-400 mt-0.5 shrink-0" />
                                                                                <span className="text-sm text-white/60">{benefit.text}</span>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                </div>

                                                                {/* Info */}
                                                                <div className="pt-4 border-t border-white/5">
                                                                    <p className="text-xs text-white/30">
                                                                        {workshop.registration_required ? 'Registration required' : 'Open to all members'}
                                                                        {spotsLeft !== null && spotsLeft > 0 && ` — ${spotsLeft} spots remaining`}
                                                                    </p>
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

                {/* Past Highlights */}
                {pastHighlights.length > 0 && (
                    <WaveSection className="py-16 md:py-24">
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="text-center mb-12"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <Award className="h-4 w-4 text-amber-400" />
                                Past Workshops
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                                Recent{' '}
                                <span className="bg-gradient-to-r from-amber-400 to-orange-300 bg-clip-text text-transparent">
                                    Highlights
                                </span>
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg max-w-xl mx-auto">
                                A look at workshops we&apos;ve run and the impact they made.
                            </motion.p>
                        </motion.div>

                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto"
                        >
                            {pastHighlights.map((workshop, i) => (
                                <motion.div
                                    key={workshop.title}
                                    initial="hidden"
                                    whileInView="visible"
                                    viewport={{ once: true }}
                                    variants={fadeUp}
                                    custom={i}
                                >
                                    <div className="h-full rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-sm flex flex-col">
                                        <div className="flex items-center gap-3 mb-4">
                                            <div className="rounded-xl p-2 bg-emerald-500/10 border border-emerald-500/20">
                                                <BookOpen className="h-5 w-5 text-emerald-400" />
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <h3 className="text-sm font-semibold text-white line-clamp-2">{workshop.title}</h3>
                                                <p className="text-[11px] text-white/40">
                                                    {new Date(workshop.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-2 gap-3 mt-auto">
                                            <div className="rounded-xl border border-white/5 bg-white/[0.02] p-3 text-center">
                                                <p className="text-lg font-bold text-white">{workshop.registered_count}</p>
                                                <p className="text-[10px] text-white/40 uppercase tracking-wider">Attended</p>
                                            </div>
                                            <div className="rounded-xl border border-white/5 bg-white/[0.02] p-3 text-center">
                                                <div className="flex items-center justify-center gap-1">
                                                    <Star className="h-4 w-4 text-amber-400 fill-amber-400" />
                                                    <p className="text-lg font-bold text-white">{workshop.average_rating > 0 ? workshop.average_rating : '—'}</p>
                                                </div>
                                                <p className="text-[10px] text-white/40 uppercase tracking-wider">Rating</p>
                                            </div>
                                        </div>
                                    </div>
                                </motion.div>
                            ))}
                        </motion.div>
                    </WaveSection>
                )}

                {/* Instructors */}
                {topInstructors.length > 0 && (
                    <WaveSection className="py-16 md:py-24">
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="text-center mb-12"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <GraduationCap className="h-4 w-4 text-indigo-400" />
                                Our Instructors
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                                Learn From the{' '}
                                <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">
                                    Best
                                </span>
                            </motion.h2>
                        </motion.div>

                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="max-w-2xl mx-auto"
                        >
                            <div className="rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-sm overflow-hidden">
                                {topInstructors.map((instructor, i) => (
                                    <motion.div
                                        key={instructor.name}
                                        variants={fadeUp}
                                        custom={i}
                                        className={`flex items-center gap-4 px-6 py-4 ${
                                            i !== topInstructors.length - 1 ? 'border-b border-white/5' : ''
                                        }`}
                                    >
                                        <div className="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500/30 to-violet-500/30 border border-white/10 flex items-center justify-center shrink-0">
                                            <span className="text-sm font-bold text-white">
                                                {instructor.name.split(' ').map((n) => n[0]).join('')}
                                            </span>
                                        </div>

                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-semibold text-white truncate">{instructor.name}</p>
                                            <p className="text-xs text-white/40">
                                                {instructor.workshops_count} workshop{instructor.workshops_count !== 1 ? 's' : ''} taught
                                            </p>
                                        </div>

                                        <div className="text-right shrink-0">
                                            <div className="flex items-center gap-1 justify-end">
                                                <Star className="h-3.5 w-3.5 text-amber-400 fill-amber-400" />
                                                <p className="text-sm font-bold text-white">
                                                    {instructor.average_rating > 0 ? instructor.average_rating : '—'}
                                                </p>
                                            </div>
                                            <p className="text-[10px] text-white/30 uppercase tracking-wider">
                                                {instructor.total_feedback} review{instructor.total_feedback !== 1 ? 's' : ''}
                                            </p>
                                        </div>
                                    </motion.div>
                                ))}
                            </div>
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
                            <CheckCircle2 className="h-4 w-4 text-emerald-400" />
                            Common Questions
                        </motion.div>
                        <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-4xl font-bold text-white mb-4">
                            Frequently{' '}
                            <span className="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">
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
                        <div className="absolute inset-0 bg-gradient-to-b from-emerald-500/5 via-teal-500/5 to-transparent rounded-3xl blur-3xl" />
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-100px' }}
                            className="relative mx-auto max-w-3xl text-center rounded-3xl border border-white/10 bg-white/[0.02] px-6 py-10 sm:px-8 sm:py-16 backdrop-blur-sm"
                        >
                            <motion.div variants={fadeUp} custom={0} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur mb-6">
                                <Zap className="h-4 w-4 text-emerald-400" />
                                Ready to Learn?
                            </motion.div>
                            <motion.h2 variants={fadeUp} custom={1} className="text-3xl md:text-5xl font-bold text-white mb-4">
                                Start Your{' '}
                                <span className="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">
                                    Journey
                                </span>
                            </motion.h2>
                            <motion.p variants={fadeUp} custom={2} className="text-white/50 text-lg mb-8 max-w-xl mx-auto leading-relaxed">
                                Join SLAU-CSIC and get access to weekly hands-on workshops,
                                mentorship, and a community of builders and security enthusiasts.
                            </motion.p>
                            <motion.div variants={fadeUp} custom={3} className="flex flex-col sm:flex-row items-center justify-center gap-4">
                                <Link href="/auth/register">
                                    <Button size="lg" className="group gap-2 rounded-full px-8 text-base uppercase tracking-[0.2em] bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/25">
                                        Join SLAU-CSIC
                                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </Button>
                                </Link>
                                <Link href="/events">
                                    <Button size="lg" variant="outline" className="rounded-full border-white/20 bg-white/5 px-8 text-base text-white/80 backdrop-blur transition-all hover:border-white/40 hover:bg-white/10">
                                        View All Events
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
