import PublicLayout from '@/components/PublicLayout';
import { GlowyWavesBackground } from '@/components/ui/glowy-waves-hero-shadcnui';
import { motion } from 'framer-motion';
import { Link } from '@inertiajs/react';
import {
    Network, ArrowRight, Users, Calendar, Award, Camera, Star,
    Briefcase, Handshake, GraduationCap, ChevronDown, Clock, MapPin,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useState } from 'react';
import EventDetailModal from '@/components/EventDetailModal';
import PartnerModal from '@/components/PartnerModal';

interface EventData {
    title: string;
    slug: string;
    description: string;
    type: string;
    start_date: string;
    end_date: string | null;
    location: string | null;
    skill_level: string | null;
    categories: Array<{ name: string; slug: string; color: string }>;
}

interface NetworkingProps {
    upcomingEvents: EventData[];
    stats: {
        active_members: number;
        events_hosted: number;
        total_attendees: number;
    };
}

const offerings = [
    {
        label: 'Industry Connections',
        title: 'Direct access to cybersecurity professionals',
        text: 'Regular talks, panels, and career fairs bring industry leaders directly to our members. Build relationships that matter.',
        icon: Handshake,
    },
    {
        label: 'Career Growth',
        title: 'From student to professional',
        text: 'Resume workshops, mock interviews, and mentorship programs prepare you for roles in cybersecurity and tech.',
        icon: Briefcase,
    },
    {
        label: 'Alumni Network',
        title: 'A community that lasts beyond graduation',
        text: 'Our alumni work across the industry. They return to mentor, hire, and collaborate with current members.',
        icon: GraduationCap,
    },
];

const journey = [
    { text: 'Attend industry talks and panels hosted by the club.', sub: 'Learn from practitioners.' },
    { text: 'Participate in career fairs and networking events.', sub: 'Meet potential employers.' },
    { text: 'Join mentorship programs and connect with alumni.', sub: 'Build lasting relationships.' },
    { text: 'Access job opportunities and industry referrals.', sub: 'Launch your career.' },
];

const galleryImages: { src: string; alt: string; caption: string; objectPosition?: string }[] = [
    { src: '/images/club/gulu/team-at-gulu.jpeg', alt: 'The team that represented us at Gulu', caption: 'Our Team at Gulu' },
    { src: '/images/club/gulu/presenting-at-gulu.jpeg', alt: 'Former club president and current general secretary presenting at Gulu', caption: 'Presenting at the Exhibition' },
    { src: '/images/club/gulu/busy-at-gulu-exhibition.jpeg', alt: 'Members busy at the NCHE exhibition booth in Gulu', caption: 'Busy at the Gulu Booth' },
    { src: '/images/club/gulu/member-with-visitor-at-gulu.jpeg', alt: 'Member with a pleased visitor at the Gulu exhibition', caption: 'Engaging Visitors' },
    { src: '/images/club/gulu/proud-member.jpeg', alt: 'A proud member of the club', caption: 'Proud to Represent', objectPosition: '50% 0%' },
    { src: '/images/club/gulu/class-attracts-class.jpeg', alt: 'Class attracts class at Gulu', caption: 'Class Attracts Class' },
    { src: '/images/club/gulu/team-at-gulu-booth.jpeg', alt: 'The team at the Gulu exhibition booth', caption: 'The Team at the Booth' },
    { src: '/images/club/gulu/president-checking-cabinet-work.jpeg', alt: 'The president in the white shirt seeing what the cabinet did', caption: 'The President Checking the Work' },
    { src: '/images/club/gulu/former-lead-developer-at-gulu.jpeg', alt: 'Former lead developer at Gulu', caption: 'Former Lead Developer' },
    { src: '/images/club/gulu/learning-from-each-other.jpeg', alt: 'Learning from each other at Gulu', caption: 'Learning From Each Other' },
    { src: '/images/club/gulu/love-during-explanations.jpeg', alt: 'The love during explanations is natural among members', caption: 'Love During Explanations' },
    { src: '/images/club/gulu/smiles-heal.jpeg', alt: 'The smiles heal', caption: 'The Smiles Heal' },
];

const faqs = [
    {
        q: 'Who can attend networking events?',
        a: 'All registered club members can attend. Some events like career fairs may also be open to non-members depending on venue capacity.',
    },
    {
        q: 'Are there opportunities to connect with alumni?',
        a: 'Yes. Our alumni network is active and regularly participates in mentorship sessions, career panels, and hiring referrals.',
    },
    {
        q: 'How do career fairs work?',
        a: 'We partner with local and regional companies to host career fairs. Companies set up booths, conduct on-the-spot interviews, and review resumes.',
    },
    {
        q: 'Can I get help with my resume?',
        a: 'Absolutely. We run regular resume workshops and offer one-on-one review sessions with industry mentors.',
    },
];

const container = {
    hidden: { opacity: 0 },
    visible: { opacity: 1, transition: { staggerChildren: 0.08, delayChildren: 0.1 } },
};

const fadeUp = {
    hidden: { opacity: 0, y: 24 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: 'easeOut' } },
};

const fadeIn = {
    hidden: { opacity: 0, y: 20 },
    visible: (i: number) => ({
        opacity: 1, y: 0, transition: { duration: 0.5, delay: i * 0.08, ease: 'easeOut' },
    }),
};

export default function About({ upcomingEvents, stats }: NetworkingProps) {
    const [selectedImage, setSelectedImage] = useState<number | null>(null);
    const [selectedEvent, setSelectedEvent] = useState<EventData | null>(null);
    const [showPartnerModal, setShowPartnerModal] = useState(false);
    const [openFaq, setOpenFaq] = useState<number | null>(null);

    return (
        <PublicLayout transparentNav>
            <GlowyWavesBackground>
                {/* ─── Hero ─── */}
                <section className="relative flex w-full items-center justify-center px-6 pt-28 pb-16 md:px-8 lg:px-12">
                    <motion.div initial="hidden" animate="visible" variants={container} className="mx-auto w-full max-w-6xl">
                        <div className="grid items-center gap-12 lg:grid-cols-[1fr_1.1fr]">
                            <div className="space-y-7">
                                <motion.div variants={fadeUp} className="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur">
                                    <Network className="h-4 w-4 text-indigo-400" />
                                    Networking & Industry
                                </motion.div>

                                <motion.h1 variants={fadeUp} className="text-4xl font-bold tracking-tight text-gray-900 dark:text-white md:text-5xl lg:text-6xl leading-[1.1]">
                                    Build connections that{' '}
                                    <span className="bg-gradient-to-r from-indigo-400 via-indigo-300 to-violet-300 bg-clip-text text-transparent">
                                        launch careers
                                    </span>
                                    .
                                </motion.h1>

                                <motion.p variants={fadeUp} className="text-lg leading-relaxed text-gray-600 dark:text-white/60">
                                    Connect with cybersecurity professionals, alumni, and recruiters. Our networking
                                    events, career fairs, and mentorship programs bridge the gap between campus and industry.
                                </motion.p>

                                <motion.div variants={fadeUp}>
                                    <Link href="/auth/register">
                                        <Button className="group gap-2 rounded-full px-6 sm:px-8 py-5 sm:py-6 text-sm sm:text-base uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25">
                                            Join the Network
                                            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                        </Button>
                                    </Link>
                                </motion.div>
                            </div>

                            <motion.div variants={fadeUp} className="relative">
                                <div className="absolute -inset-4 rounded-3xl bg-gradient-to-b from-indigo-500/20 via-transparent to-transparent blur-2xl" />
                                <div className="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                                    <img src="/images/club/gulu/club-patron-in-the-middle.jpeg" alt="Club patron with members" className="h-[300px] sm:h-[460px] w-full object-cover object-top" />
                                    <div className="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent" />
                                </div>
                            </motion.div>
                        </div>

                        {/* Stats row */}
                        <motion.div variants={fadeUp} className="mt-16 grid gap-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-6 backdrop-blur-sm sm:grid-cols-3">
                            <div className="space-y-1 text-center">
                                <div className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{stats.active_members}+</div>
                                <div className="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-white/40">Active Members</div>
                            </div>
                            <div className="space-y-1 text-center">
                                <div className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{stats.events_hosted}+</div>
                                <div className="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-white/40">Events Hosted</div>
                            </div>
                            <div className="space-y-1 text-center">
                                <div className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{stats.total_attendees}+</div>
                                <div className="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-white/40">Total Attendees</div>
                            </div>
                        </motion.div>
                    </motion.div>
                </section>

                {/* Gradient transition */}
                <div className="relative h-32 w-full overflow-hidden">
                    <div className="absolute inset-0 bg-gradient-to-b from-transparent via-indigo-500/[0.04] to-transparent" />
                </div>

                {/* ─── What We Offer ─── */}
                <section className="px-6 pb-28 md:px-8 lg:px-12">
                    <div className="mx-auto max-w-6xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="mb-12 max-w-3xl">
                            <motion.div variants={fadeUp} className="mb-4 inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur">
                                <Star className="h-4 w-4 text-indigo-400" />
                                What We Offer
                            </motion.div>
                            <motion.h2 variants={fadeUp} className="text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-4xl leading-[1.15]">
                                Industry access that goes beyond the classroom.
                            </motion.h2>
                            <motion.p variants={fadeUp} className="mt-4 text-lg text-gray-500 dark:text-white/50">
                                Three pillars define how we connect students with the cybersecurity industry.
                            </motion.p>
                        </motion.div>

                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="grid gap-6 md:grid-cols-3">
                            {offerings.map((item, i) => {
                                const Icon = item.icon;
                                return (
                                    <motion.div key={item.label} variants={fadeIn} custom={i} className="group relative overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-7 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-indigo-500/30 hover:shadow-[0_0_30px_-5px_rgba(99,102,241,0.15)]">
                                        <div className="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-indigo-500/5 blur-2xl transition-all duration-500 group-hover:bg-indigo-500/10" />
                                        <div className="relative">
                                            <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/[0.08] text-indigo-400 transition-all duration-300 group-hover:bg-indigo-500/[0.15] group-hover:shadow-[0_0_20px_-5px_rgba(99,102,241,0.3)]">
                                                <Icon className="h-6 w-6" />
                                            </div>
                                            <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-400/80">{item.label}</p>
                                            <h3 className="mb-3 text-lg font-semibold text-gray-900 dark:text-white">{item.title}</h3>
                                            <p className="text-sm leading-relaxed text-gray-500 dark:text-white/50">{item.text}</p>
                                        </div>
                                    </motion.div>
                                );
                            })}
                        </motion.div>
                    </div>
                </section>

                {/* ─── Upcoming Events ─── */}
                {upcomingEvents.length > 0 && (
                    <section className="border-t border-gray-200 dark:border-white/[0.05] px-6 py-28 md:px-8 lg:px-12">
                        <div className="mx-auto max-w-6xl">
                            <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="mb-12 max-w-3xl">
                                <motion.div variants={fadeUp} className="mb-4 inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur">
                                    <Calendar className="h-4 w-4 text-indigo-400" />
                                    Upcoming Events
                                </motion.div>
                                <motion.h2 variants={fadeUp} className="text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-4xl leading-[1.15]">
                                    Where networking happens
                                </motion.h2>
                                <motion.p variants={fadeUp} className="mt-4 text-lg text-gray-500 dark:text-white/50">
                                    Industry talks, career fairs, and panels designed to connect you with professionals.
                                </motion.p>
                            </motion.div>

                            <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="grid gap-5 sm:grid-cols-2">
                                {upcomingEvents.map((event, i) => {
                                    const d = new Date(event.start_date);
                                    return (
                                        <motion.button key={event.slug} variants={fadeIn} custom={i} onClick={() => setSelectedEvent(event)} className="group relative rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-6 text-left transition-colors duration-200 hover:border-indigo-500/30 hover:bg-indigo-500/[0.03]">
                                            <div className="mb-3 flex items-center gap-2.5">
                                                <span className="rounded-full border border-indigo-500/20 bg-indigo-500/[0.08] px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-indigo-400">
                                                    {event.type.replace(/_/g, ' ')}
                                                </span>
                                                <span className="text-xs text-gray-400 dark:text-white/30">
                                                    {d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}
                                                </span>
                                            </div>

                                            <h3 className="mb-2 text-base font-semibold text-gray-900 dark:text-white leading-snug break-words">{event.title}</h3>

                                            <div className="flex items-center gap-4 text-xs text-gray-400 dark:text-white/30">
                                                <span className="flex items-center gap-1.5">
                                                    <Clock className="h-3.5 w-3.5" />
                                                    {d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                                                </span>
                                                {event.location && (
                                                    <span className="flex items-center gap-1.5">
                                                        <MapPin className="h-3.5 w-3.5" />
                                                        {event.location}
                                                    </span>
                                                )}
                                            </div>
                                        </motion.button>
                                    );
                                })}
                            </motion.div>
                        </div>
                    </section>
                )}

                {/* ─── Photo Gallery ─── */}
                <section className="px-6 py-28 md:px-8 lg:px-12">
                    <div className="mx-auto max-w-6xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="mb-12 max-w-3xl">
                            <motion.div variants={fadeUp} className="mb-4 inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur">
                                <Camera className="h-4 w-4 text-indigo-400" />
                                Club Gallery
                            </motion.div>
                            <motion.h2 variants={fadeUp} className="text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-4xl leading-[1.15]">
                                Moments from our journey
                            </motion.h2>
                            <motion.p variants={fadeUp} className="mt-4 text-lg text-gray-500 dark:text-white/50">
                                Captured moments from our exhibitions, workshops, and community engagements.
                            </motion.p>
                        </motion.div>

                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {galleryImages.map((image, i) => (
                                <motion.div
                                    key={i}
                                    variants={fadeIn}
                                    custom={i}
                                    className="group relative cursor-pointer overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 transition-all duration-300 hover:-translate-y-1 hover:border-indigo-500/30 hover:shadow-[0_0_30px_-5px_rgba(99,102,241,0.15)]"
                                    onClick={() => setSelectedImage(i)}
                                >
                                    <img src={image.src} alt={image.alt} style={{ objectPosition: image.objectPosition ?? '50% 30%' }} className="aspect-[4/3] w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    <div className="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                                    <div className="absolute bottom-0 left-0 right-0 p-4 translate-y-full transition-transform duration-300 group-hover:translate-y-0">
                                        <p className="text-sm font-medium text-white">{image.caption}</p>
                                    </div>
                                </motion.div>
                            ))}
                        </motion.div>

                        {/* Lightbox */}
                        {selectedImage !== null && (
                            <div
                                className="fixed inset-0 z-[60] overflow-y-auto bg-black/80 backdrop-blur-sm"
                                onClick={() => setSelectedImage(null)}
                            >
                                <div className="flex min-h-full p-4 sm:p-6">
                                    <div
                                        className="relative m-auto w-full max-w-4xl"
                                        onClick={(e) => e.stopPropagation()}
                                    >
                                        <button
                                            onClick={() => setSelectedImage(null)}
                                            aria-label="Close image"
                                            className="absolute right-3 top-3 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white/80 transition-colors hover:bg-black/80 hover:text-white"
                                        >
                                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                        <img src={galleryImages[selectedImage].src} alt={galleryImages[selectedImage].alt} className="mx-auto max-h-[80vh] w-auto max-w-full rounded-2xl border border-white/10" />
                                        <p className="mt-4 text-center text-sm text-white/60">{galleryImages[selectedImage].caption}</p>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </section>

                {/* ─── Growth Path ─── */}
                <section className="border-t border-gray-200 dark:border-white/[0.05] px-6 py-28 md:px-8 lg:px-12">
                    <div className="mx-auto max-w-6xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="grid gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                            <motion.div variants={fadeUp} className="relative">
                                <div className="absolute -inset-4 rounded-3xl bg-gradient-to-br from-indigo-500/10 via-transparent to-transparent blur-2xl" />
                                <div className="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                                    <img src="/images/club/kevin-sharon.jpg" alt="Members at a career event" className="h-full w-full object-cover object-top" />
                                    <div className="absolute inset-0 bg-gradient-to-tr from-[#0f172a]/60 via-transparent to-transparent" />
                                </div>
                            </motion.div>

                            <div className="space-y-8">
                                <motion.div variants={fadeUp}>
                                    <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur">
                                        <Award className="h-4 w-4 text-indigo-400" />
                                        Your Path
                                    </div>
                                    <h2 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-4xl leading-[1.15]">
                                        From campus to career — with support at every step.
                                    </h2>
                                </motion.div>

                                <div className="relative">
                                    <div className="absolute left-[19px] top-3 bottom-3 w-px bg-gradient-to-b from-indigo-500/40 via-indigo-500/20 to-transparent" />
                                    <div className="space-y-8">
                                        {journey.map((step, i) => (
                                            <motion.div key={i} variants={fadeIn} custom={i} className="relative flex items-start gap-5">
                                                <div className="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-indigo-500/30 bg-indigo-500/[0.08] text-sm font-semibold text-indigo-400 backdrop-blur-sm transition-all duration-300 hover:bg-indigo-500/[0.15]">
                                                    {i + 1}
                                                </div>
                                                <div className="pt-1.5">
                                                    <p className="text-sm font-medium leading-relaxed text-gray-900 dark:text-white">{step.text}</p>
                                                    <p className="mt-1 text-xs text-gray-500 dark:text-white/40">{step.sub}</p>
                                                </div>
                                            </motion.div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </motion.div>
                    </div>
                </section>

                {/* ─── FAQ ─── */}
                <section className="px-6 py-28 md:px-8 lg:px-12">
                    <div className="mx-auto max-w-3xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="mb-12">
                            <motion.div variants={fadeUp} className="mb-4 inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur">
                                <Users className="h-4 w-4 text-indigo-400" />
                                Frequently Asked Questions
                            </motion.div>
                            <motion.h2 variants={fadeUp} className="text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-4xl leading-[1.15]">
                                Common questions about networking
                            </motion.h2>
                        </motion.div>

                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="space-y-3">
                            {faqs.map((faq, i) => (
                                <motion.div key={i} variants={fadeIn} custom={i} className="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] backdrop-blur-sm overflow-hidden">
                                    <button
                                        onClick={() => setOpenFaq(openFaq === i ? null : i)}
                                        className="flex w-full items-center justify-between p-6 text-left"
                                    >
                                        <span className="text-sm font-medium text-gray-900 dark:text-white pr-4">{faq.q}</span>
                                        <ChevronDown className={`h-4 w-4 shrink-0 text-gray-500 dark:text-white/40 transition-transform duration-200 ${openFaq === i ? 'rotate-180' : ''}`} />
                                    </button>
                                    {openFaq === i && (
                                        <div className="px-6 pb-6">
                                            <p className="text-sm leading-relaxed text-gray-500 dark:text-white/50">{faq.a}</p>
                                        </div>
                                    )}
                                </motion.div>
                            ))}
                        </motion.div>
                    </div>
                </section>

                {/* ─── CTA ─── */}
                <section className="border-t border-gray-200 dark:border-white/[0.05] px-6 py-28 md:px-8 lg:px-12">
                    <div className="mx-auto max-w-6xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="relative overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 bg-gradient-to-br from-indigo-500/[0.05] via-white/[0.02] to-transparent p-8 backdrop-blur-sm md:p-14">
                            <div className="pointer-events-none absolute -right-20 -top-20 h-60 w-60 rounded-full bg-indigo-500/10 blur-[100px]" />
                            <div className="pointer-events-none absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-violet-500/10 blur-[80px]" />

                            <div className="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                                <div className="max-w-2xl">
                                    <motion.div variants={fadeUp} className="mb-4 inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur">
                                        <ArrowRight className="h-4 w-4 text-indigo-400" />
                                        Get Connected
                                    </motion.div>
                                    <motion.h3 variants={fadeUp} className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white md:text-3xl leading-[1.15]">
                                        Ready to build your professional network?
                                    </motion.h3>
                                    <motion.p variants={fadeUp} className="mt-3 text-base text-gray-500 dark:text-white/50 leading-relaxed">
                                        Join the club to access industry events, mentorship programs, and a network of cybersecurity professionals.
                                    </motion.p>
                                </div>
                                <motion.div variants={fadeUp} className="flex flex-wrap gap-3 shrink-0">
                                    <Link href="/auth/register">
                                        <Button className="group gap-2 rounded-full px-6 sm:px-8 py-5 sm:py-6 text-sm sm:text-base uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25">
                                            Join the Network
                                            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                        </Button>
                                    </Link>
                                    <Button variant="outline" onClick={() => setShowPartnerModal(true)} className="rounded-full border-gray-300 dark:border-white/20 bg-gray-100 dark:bg-white/5 px-6 sm:px-8 py-5 sm:py-6 text-sm sm:text-base text-gray-700 dark:text-white/80 backdrop-blur transition-all hover:border-white/40 hover:bg-white/10">
                                            Partner with Us
                                        </Button>
                                </motion.div>
                            </div>
                        </motion.div>
                    </div>
                </section>
            </GlowyWavesBackground>

            {/* Event Detail Modal */}
            {selectedEvent && (
                <EventDetailModal event={selectedEvent} onClose={() => setSelectedEvent(null)} />
            )}

            {/* Partner Modal */}
            {showPartnerModal && (
                <PartnerModal onClose={() => setShowPartnerModal(false)} />
            )}
        </PublicLayout>
    );
}
