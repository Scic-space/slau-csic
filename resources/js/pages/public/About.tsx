import PublicLayout from '@/components/PublicLayout';
import { motion } from 'framer-motion';
import { Link } from '@inertiajs/react';
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
        icon: 'handshake',
    },
    {
        label: 'Career Growth',
        title: 'From student to professional',
        text: 'Resume workshops, mock interviews, and mentorship programs prepare you for roles in cybersecurity and tech.',
        icon: 'business_center',
    },
    {
        label: 'Alumni Network',
        title: 'A community that lasts beyond graduation',
        text: 'Our alumni work across the industry. They return to mentor, hire, and collaborate with current members.',
        icon: 'school',
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

function Icon({ children, className = '' }: { children: string; className?: string }) {
    return <span className={`material-symbols-outlined ${className}`} aria-hidden="true">{children}</span>;
}

export default function About({ upcomingEvents, stats }: NetworkingProps) {
    const [selectedImage, setSelectedImage] = useState<number | null>(null);
    const [selectedEvent, setSelectedEvent] = useState<EventData | null>(null);
    const [showPartnerModal, setShowPartnerModal] = useState(false);
    const [openFaq, setOpenFaq] = useState<number | null>(null);

    return (
        <PublicLayout>
            <div className="overflow-hidden bg-background text-foreground">
                {/* ─── Hero ─── */}
                <section className="relative flex w-full items-center justify-center bg-background px-4 pb-20 pt-36 sm:px-6 sm:pb-24 lg:px-8">
                    <motion.div initial="hidden" animate="visible" variants={container} className="mx-auto w-full max-w-7xl">
                        <div className="grid items-center gap-12 lg:grid-cols-[1fr_1.1fr]">
                            <div className="space-y-7">
                                <motion.div variants={fadeUp} className="inline-flex items-center gap-2 rounded-sm bg-brand-50 px-3 py-2 text-sm font-semibold uppercase tracking-[0.18em] text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                    <Icon className="text-[19px]">hub</Icon>
                                    Networking & Industry
                                </motion.div>

                                <motion.h1 variants={fadeUp} className="text-4xl font-bold leading-[1.08] tracking-tight text-foreground md:text-5xl lg:text-6xl">
                                    Build connections that{' '}
                                    <span className="text-brand-600 dark:text-brand-300">
                                        launch careers
                                    </span>
                                    .
                                </motion.h1>

                                <motion.p variants={fadeUp} className="text-lg leading-8 text-muted-foreground">
                                    Connect with cybersecurity professionals, alumni, and recruiters. Our networking
                                    events, career fairs, and mentorship programs bridge the gap between campus and industry.
                                </motion.p>

                                <motion.div variants={fadeUp}>
                                    <Link href="/auth/register">
                                        <Button className="group min-h-12 gap-2 rounded-sm bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-theme-sm hover:bg-[#2984D1]">
                                            Join the Network
                                            <Icon className="text-[20px] transition-transform group-hover:translate-x-1">arrow_forward</Icon>
                                        </Button>
                                    </Link>
                                </motion.div>
                            </div>

                            <motion.div variants={fadeUp} className="relative">
                                <div className="absolute -inset-4 rounded-full bg-brand-500/15 blur-3xl" />
                                <div className="relative overflow-hidden rounded-sm bg-card/80 shadow-theme-md dark:bg-card/70">
                                    <img src="/images/club/gulu/club-patron-in-the-middle.jpeg" alt="Club patron with members" className="h-[300px] w-full object-cover object-top sm:h-[460px]" />
                                    <div className="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent" />
                                </div>
                            </motion.div>
                        </div>

                        {/* Stats row */}
                        <motion.div variants={fadeUp} className="mt-16 grid gap-4 rounded-sm bg-card/80 p-6 shadow-theme-xs backdrop-blur-sm dark:bg-card/70 sm:grid-cols-3">
                            <div className="space-y-1 text-center">
                                <div className="text-2xl font-bold tracking-tight text-foreground">{stats.active_members}+</div>
                                <div className="text-xs uppercase tracking-[0.18em] text-muted-foreground">Active Members</div>
                            </div>
                            <div className="space-y-1 text-center">
                                <div className="text-2xl font-bold tracking-tight text-foreground">{stats.events_hosted}+</div>
                                <div className="text-xs uppercase tracking-[0.18em] text-muted-foreground">Events Hosted</div>
                            </div>
                            <div className="space-y-1 text-center">
                                <div className="text-2xl font-bold tracking-tight text-foreground">{stats.total_attendees}+</div>
                                <div className="text-xs uppercase tracking-[0.18em] text-muted-foreground">Total Attendees</div>
                            </div>
                        </motion.div>
                    </motion.div>
                </section>

                {/* Gradient transition */}
                <div className="relative h-12 w-full overflow-hidden bg-background sm:h-16">
                    <div className="absolute inset-0 bg-gradient-to-b from-transparent via-indigo-500/[0.04] to-transparent" />
                </div>

                {/* ─── What We Offer ─── */}
                <section className="bg-background px-4 pb-20 sm:px-6 sm:pb-24 lg:px-8">
                    <div className="mx-auto max-w-7xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="mb-12 max-w-3xl">
                            <motion.div variants={fadeUp} className="mb-3 inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">
                                <Icon className="text-[20px]">star</Icon>
                                What We Offer
                            </motion.div>
                            <motion.h2 variants={fadeUp} className="text-3xl font-bold leading-[1.15] tracking-tight text-foreground md:text-4xl">
                                Industry access that goes beyond the classroom.
                            </motion.h2>
                            <motion.p variants={fadeUp} className="mt-4 text-lg leading-7 text-muted-foreground">
                                Three pillars define how we connect students with the cybersecurity industry.
                            </motion.p>
                        </motion.div>

                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="grid gap-6 md:grid-cols-3">
                            {offerings.map((item, i) => {
                                return (
                                    <motion.div key={item.label} variants={fadeIn} custom={i} className="group relative overflow-hidden rounded-sm bg-card/80 p-7 shadow-theme-xs backdrop-blur-sm transition-all duration-200 hover:-translate-y-1 hover:bg-card hover:shadow-theme-sm dark:bg-card/70">
                                        <div className="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-indigo-500/5 blur-2xl transition-all duration-500 group-hover:bg-indigo-500/10" />
                                        <div className="relative">
                                            <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-sm bg-brand-50 text-brand-600 transition-colors duration-200 group-hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:group-hover:bg-brand-500/15">
                                                <Icon className="text-[24px]">{item.icon}</Icon>
                                            </div>
                                            <p className="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">{item.label}</p>
                                            <h3 className="mb-3 text-lg font-semibold text-foreground">{item.title}</h3>
                                            <p className="text-sm leading-6 text-muted-foreground">{item.text}</p>
                                        </div>
                                    </motion.div>
                                );
                            })}
                        </motion.div>
                    </div>
                </section>

                {/* ─── Upcoming Events ─── */}
                {upcomingEvents.length > 0 && (
                    <section className="bg-card px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
                        <div className="mx-auto max-w-7xl">
                            <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="mb-12 max-w-3xl">
                                <motion.div variants={fadeUp} className="mb-3 inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">
                                    <Icon className="text-[20px]">event</Icon>
                                    Upcoming Events
                                </motion.div>
                                <motion.h2 variants={fadeUp} className="text-3xl font-bold leading-[1.15] tracking-tight text-foreground md:text-4xl">
                                    Where networking happens
                                </motion.h2>
                                <motion.p variants={fadeUp} className="mt-4 text-lg leading-7 text-muted-foreground">
                                    Industry talks, career fairs, and panels designed to connect you with professionals.
                                </motion.p>
                            </motion.div>

                            <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="grid gap-5 sm:grid-cols-2">
                                {upcomingEvents.map((event, i) => {
                                    const d = new Date(event.start_date);
                                    return (
                                        <motion.button key={event.slug} variants={fadeIn} custom={i} onClick={() => setSelectedEvent(event)} className="group relative rounded-sm bg-background/75 p-6 text-left shadow-theme-xs transition-all duration-200 hover:-translate-y-0.5 hover:bg-background hover:shadow-theme-sm dark:bg-background/55">
                                            <div className="mb-3 flex items-center gap-2.5">
                                                <span className="rounded-full bg-brand-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                                    {event.type.replace(/_/g, ' ')}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}
                                                </span>
                                            </div>

                                            <h3 className="mb-2 break-words text-base font-semibold leading-snug text-foreground">{event.title}</h3>

                                            <div className="flex items-center gap-4 text-xs text-muted-foreground">
                                                <span className="flex items-center gap-1.5">
                                                    <Icon className="text-[18px]">schedule</Icon>
                                                    {d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                                                </span>
                                                {event.location && (
                                                    <span className="flex items-center gap-1.5">
                                                        <Icon className="text-[18px]">location_on</Icon>
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
                <section className="bg-background px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
                    <div className="mx-auto max-w-7xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="mb-12 max-w-3xl">
                            <motion.div variants={fadeUp} className="mb-3 inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">
                                <Icon className="text-[20px]">photo_library</Icon>
                                Club Gallery
                            </motion.div>
                            <motion.h2 variants={fadeUp} className="text-3xl font-bold leading-[1.15] tracking-tight text-foreground md:text-4xl">
                                Moments from our journey
                            </motion.h2>
                            <motion.p variants={fadeUp} className="mt-4 text-lg leading-7 text-muted-foreground">
                                Captured moments from our exhibitions, workshops, and community engagements.
                            </motion.p>
                        </motion.div>

                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {galleryImages.map((image, i) => (
                                <motion.div
                                    key={i}
                                    variants={fadeIn}
                                    custom={i}
                                    className="group relative cursor-pointer overflow-hidden rounded-sm bg-card/80 shadow-theme-xs transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-sm dark:bg-card/70"
                                    onClick={() => setSelectedImage(i)}
                                >
                                    <img src={image.src} alt={image.alt} style={{ objectPosition: image.objectPosition ?? '50% 30%' }} className="aspect-[4/3] w-full object-cover transition-transform duration-500 group-hover:scale-[1.02]" />
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
                                        <img src={galleryImages[selectedImage].src} alt={galleryImages[selectedImage].alt} className="mx-auto max-h-[80vh] w-auto max-w-full rounded-sm shadow-theme-lg" />
                                        <p className="mt-4 text-center text-sm text-white/60">{galleryImages[selectedImage].caption}</p>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </section>

                {/* ─── Growth Path ─── */}
                <section className="bg-card px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
                    <div className="mx-auto max-w-7xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="grid gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                            <motion.div variants={fadeUp} className="relative mx-auto w-full max-w-xl lg:h-[440px]">
                                <div className="absolute -inset-4 rounded-full bg-brand-500/10 blur-3xl" />
                                <div className="relative flex h-full max-h-[440px] items-center justify-center overflow-hidden rounded-sm bg-background/70 shadow-theme-sm dark:bg-background/55">
                                    <img src="/images/club/kevin-sharon.jpg" alt="Members at a career event" className="max-h-[440px] w-full object-contain object-top" />
                                    <div className="absolute inset-0 bg-gradient-to-tr from-[#0f172a]/60 via-transparent to-transparent" />
                                </div>
                            </motion.div>

                            <div className="space-y-8">
                                <motion.div variants={fadeUp}>
                                    <div className="mb-3 inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">
                                        <Icon className="text-[20px]">workspace_premium</Icon>
                                        Your Path
                                    </div>
                                    <h2 className="text-3xl font-bold leading-[1.15] tracking-tight text-foreground md:text-4xl">
                                        From campus to career — with support at every step.
                                    </h2>
                                </motion.div>

                                <div className="relative">
                                    <div className="absolute left-[19px] top-3 bottom-3 w-px bg-gradient-to-b from-indigo-500/40 via-indigo-500/20 to-transparent" />
                                    <div className="space-y-8">
                                        {journey.map((step, i) => (
                                            <motion.div key={i} variants={fadeIn} custom={i} className="relative flex items-start gap-5">
                                                <div className="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-600 shadow-theme-xs backdrop-blur-sm transition-colors duration-200 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/15">
                                                    {i + 1}
                                                </div>
                                                <div className="pt-1.5">
                                                    <p className="text-sm font-medium leading-relaxed text-foreground">{step.text}</p>
                                                    <p className="mt-1 text-xs text-muted-foreground">{step.sub}</p>
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
                <section className="bg-background px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
                    <div className="mx-auto max-w-3xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="mb-12">
                            <motion.div variants={fadeUp} className="mb-3 inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">
                                <Icon className="text-[20px]">quiz</Icon>
                                Frequently Asked Questions
                            </motion.div>
                            <motion.h2 variants={fadeUp} className="text-3xl font-bold leading-[1.15] tracking-tight text-foreground md:text-4xl">
                                Common questions about networking
                            </motion.h2>
                        </motion.div>

                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="space-y-3">
                            {faqs.map((faq, i) => (
                                <motion.div key={i} variants={fadeIn} custom={i} className="overflow-hidden rounded-sm bg-card/80 shadow-theme-xs backdrop-blur-sm dark:bg-card/70">
                                    <button
                                        onClick={() => setOpenFaq(openFaq === i ? null : i)}
                                        className="flex w-full items-center justify-between p-6 text-left"
                                    >
                                        <span className="pr-4 text-sm font-medium text-foreground">{faq.q}</span>
                                        <Icon className={`shrink-0 text-[20px] text-muted-foreground transition-transform duration-200 ${openFaq === i ? 'rotate-180' : ''}`}>expand_more</Icon>
                                    </button>
                                    {openFaq === i && (
                                        <div className="px-6 pb-6">
                                            <p className="text-sm leading-6 text-muted-foreground">{faq.a}</p>
                                        </div>
                                    )}
                                </motion.div>
                            ))}
                        </motion.div>
                    </div>
                </section>

                {/* ─── CTA ─── */}
                <section className="bg-background px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
                    <div className="mx-auto max-w-7xl">
                        <motion.div initial="hidden" whileInView="visible" viewport={{ once: true, margin: '-80px' }} variants={container} className="relative overflow-hidden rounded-sm bg-brand-600 p-8 text-white shadow-theme-lg md:p-14">
                            <div className="pointer-events-none absolute -right-20 -top-20 h-60 w-60 rounded-full bg-indigo-500/10 blur-[100px]" />
                            <div className="pointer-events-none absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-violet-500/10 blur-[80px]" />

                            <div className="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                                <div className="max-w-2xl">
                                    <motion.div variants={fadeUp} className="mb-3 inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.18em] text-brand-100">
                                        <Icon className="text-[20px]">arrow_forward</Icon>
                                        Get Connected
                                    </motion.div>
                                    <motion.h3 variants={fadeUp} className="text-2xl font-bold leading-[1.15] tracking-tight text-white md:text-3xl">
                                        Ready to build your professional network?
                                    </motion.h3>
                                    <motion.p variants={fadeUp} className="mt-3 text-base leading-7 text-brand-100">
                                        Join the club to access industry events, mentorship programs, and a network of cybersecurity professionals.
                                    </motion.p>
                                </div>
                                <motion.div variants={fadeUp} className="flex flex-wrap gap-3 shrink-0">
                                    <Link href="/auth/register">
                                        <Button className="group min-h-12 gap-2 rounded-sm bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow-theme-sm hover:bg-brand-50">
                                            Join the Network
                                            <Icon className="text-[20px] transition-transform group-hover:translate-x-1">arrow_forward</Icon>
                                        </Button>
                                    </Link>
                                    <Button variant="outline" onClick={() => setShowPartnerModal(true)} className="min-h-12 rounded-sm border-white/30 bg-transparent px-6 py-3 text-sm font-semibold text-white backdrop-blur transition-colors hover:bg-white/10 hover:text-white">
                                            Partner with Us
                                        </Button>
                                </motion.div>
                            </div>
                        </motion.div>
                    </div>
                </section>
            </div>

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
