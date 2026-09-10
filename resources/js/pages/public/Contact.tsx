import PublicLayout from '@/components/PublicLayout';
import { motion } from 'framer-motion';
import { usePage, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { useState } from 'react';
import type { PageProps as InertiaPageProps } from '@inertiajs/react';

interface PageProps extends InertiaPageProps {
    flash: {
        success?: string;
        error?: string;
    };
    errors: Record<string, string>;
}

const topics = [
    'Membership and joining',
    'Event attendance or inquiry',
    'Collaboration or partnership',
    'Speaker invitation',
];

const container = {
    hidden: { opacity: 0 },
    visible: { opacity: 1, transition: { staggerChildren: 0.08, delayChildren: 0.1 } },
};

const fadeUp = {
    hidden: { opacity: 0, y: 24 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: 'easeOut' } },
};

function Icon({ children, className = '' }: { children: string; className?: string }) {
    return <span className={`material-symbols-outlined ${className}`} aria-hidden="true">{children}</span>;
}

export default function Contact() {
    const { errors, flash } = usePage<PageProps>().props;

    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [topic, setTopic] = useState('');
    const [message, setMessage] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitting(true);
        router.post('/contact', { name, email, topic, message }, {
            onSuccess: () => {
                setName('');
                setEmail('');
                setTopic('');
                setMessage('');
            },
            onFinish: () => setSubmitting(false),
            preserveScroll: true,
        });
    };

    return (
        <PublicLayout>
            <div className="overflow-hidden bg-background text-foreground">
                {/* Hero */}
                <section className="relative flex w-full items-center justify-center bg-background px-4 pb-16 pt-20 sm:px-6 sm:pt-24 lg:px-8">
                    <motion.div initial="hidden" animate="visible" variants={container} className="mx-auto w-full max-w-7xl text-center">
                        <motion.div variants={fadeUp} className="mb-6 inline-flex items-center gap-2 rounded-sm bg-brand-50 px-3 py-2 text-sm font-semibold uppercase tracking-[0.18em] text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                            <Icon className="text-[19px]">mail</Icon>
                            Get in Touch
                        </motion.div>
                        <motion.h1 variants={fadeUp} className="text-4xl font-bold leading-[1.08] tracking-tight text-foreground md:text-5xl lg:text-6xl">
                            Let{' '}
                            <span className="text-brand-600 dark:text-brand-300">
                                start a conversation.
                            </span>
                        </motion.h1>
                        <motion.p variants={fadeUp} className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-muted-foreground">
                            Members, speakers, partners, and campus visitors — reach out and we will get back to you.
                        </motion.p>
                    </motion.div>
                </section>

                {/* Form + Info */}
                <section className="bg-background px-4 pb-20 sm:px-6 sm:pb-24 lg:px-8">
                    <div className="mx-auto max-w-7xl">
                        <div className="grid gap-10 lg:grid-cols-[1fr_0.7fr] items-start">

                            {/* Form */}
                            <motion.div
                                initial="hidden"
                                whileInView="visible"
                                viewport={{ once: true, margin: '-50px' }}
                                variants={container}
                            >
                                {flash.success && (
                                    <motion.div variants={fadeUp} className="mb-6 flex items-center gap-2 rounded-sm bg-success-50 px-4 py-3 text-sm text-success-700 shadow-theme-xs dark:bg-success-500/10 dark:text-success-400">
                                        <Icon className="text-[20px]">check_circle</Icon>
                                        {flash.success}
                                    </motion.div>
                                )}

                                {(flash.error || Object.keys(errors).length > 0) && (
                                    <motion.div variants={fadeUp} role="alert" className="mb-6 flex items-start gap-2 rounded-sm bg-error-50 px-4 py-3 text-sm text-error-700 shadow-theme-xs dark:bg-error-500/10 dark:text-error-400">
                                        <Icon className="text-[20px]">error</Icon>
                                        <div>
                                            {flash.error && <p>{flash.error}</p>}
                                            {Object.values(errors).map((error) => <p key={error}>{error}</p>)}
                                        </div>
                                    </motion.div>
                                )}

                                <motion.form
                                    variants={fadeUp}
                                    onSubmit={handleSubmit}
                                    className="space-y-5 rounded-sm bg-card/80 p-6 shadow-theme-xs backdrop-blur-sm dark:bg-card/70 md:p-8"
                                >
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label htmlFor="name" className="mb-2 block text-sm font-medium text-foreground">
                                                Full name
                                            </label>
                                            <input
                                                id="name"
                                                name="name"
                                                type="text"
                                                value={name}
                                                onChange={(e) => setName(e.target.value)}
                                                required
                                                className="w-full rounded-sm border border-border bg-background px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                                placeholder="Your name"
                                            />
                                        </div>
                                        <div>
                                            <label htmlFor="email" className="mb-2 block text-sm font-medium text-foreground">
                                                Email
                                            </label>
                                            <input
                                                id="email"
                                                name="email"
                                                type="email"
                                                value={email}
                                                onChange={(e) => setEmail(e.target.value)}
                                                required
                                                className="w-full rounded-sm border border-border bg-background px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                                placeholder="you@example.com"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label htmlFor="topic" className="mb-2 block text-sm font-medium text-foreground">
                                            What is this about?
                                        </label>
                                        <select
                                            id="topic"
                                            name="topic"
                                            value={topic}
                                            onChange={(e) => setTopic(e.target.value)}
                                            required
                                            className="w-full rounded-sm border border-border bg-background px-4 py-3 text-sm text-foreground transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                        >
                                            <option value="" className="bg-card text-muted-foreground">Select a topic</option>
                                            {topics.map((t) => (
                                                <option key={t} value={t} className="bg-card text-foreground">{t}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label htmlFor="message" className="mb-2 block text-sm font-medium text-foreground">
                                            Message
                                        </label>
                                        <textarea
                                            id="message"
                                            name="message"
                                            rows={5}
                                            value={message}
                                            onChange={(e) => setMessage(e.target.value)}
                                            required
                                            className="w-full rounded-sm border border-border bg-background px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                            placeholder="Tell us what you have in mind..."
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        disabled={submitting}
                                        className="group min-h-12 gap-2 rounded-sm bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-theme-sm hover:bg-[#2984D1] disabled:opacity-50"
                                    >
                                        {submitting ? 'Sending...' : 'Send Message'}
                                        <Icon className="text-[20px] transition-transform group-hover:translate-x-0.5">send</Icon>
                                    </Button>
                                </motion.form>
                            </motion.div>

                            {/* Sidebar */}
                            <motion.aside
                                initial="hidden"
                                whileInView="visible"
                                viewport={{ once: true, margin: '-50px' }}
                                variants={container}
                                className="space-y-5"
                            >
                                {/* Contact Info */}
                                <motion.div variants={fadeUp} className="rounded-sm bg-card/80 p-6 shadow-theme-xs backdrop-blur-sm dark:bg-card/70">
                                    <h3 className="mb-4 text-sm font-semibold uppercase tracking-[0.18em] text-foreground">
                                        Contact Information
                                    </h3>
                                    <ul className="space-y-4 text-sm text-muted-foreground">
                                        <li className="flex items-center gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                                <Icon className="text-[20px]">mail</Icon>
                                            </div>
                                            <div>
                                                <p className="mb-0.5 text-[11px] uppercase tracking-wider text-muted-foreground">Email</p>
                                                <a href="mailto:sciccyber8@gmail.com" className="text-foreground transition-colors hover:text-brand-600 dark:hover:text-brand-300">
                                                    sciccyber8@gmail.com
                                                </a>
                                            </div>
                                        </li>
                                        <li>
                                            <a
                                                href="https://wa.me/254105883177"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                aria-label="Chat with SCIC Cyber on WhatsApp"
                                                className="group inline-flex min-h-11 items-center gap-3 rounded-sm bg-success-50 px-3 py-2 font-medium text-success-700 transition-all duration-200 hover:-translate-y-0.5 hover:bg-success-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-success-600 dark:bg-success-500/10 dark:text-success-400 dark:hover:bg-success-500/15 dark:focus-visible:outline-success-400"
                                            >
                                                <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-sm bg-success-100 transition-colors group-hover:bg-success-200 dark:bg-success-500/15 dark:group-hover:bg-success-500/20">
                                                    <Icon className="text-[20px]">chat</Icon>
                                                </span>
                                                <span>WhatsApp Us</span>
                                            </a>
                                        </li>
                                        <li className="flex items-center gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                                <Icon className="text-[20px]">location_on</Icon>
                                            </div>
                                            <div>
                                                <p className="mb-0.5 text-[11px] uppercase tracking-wider text-muted-foreground">Location</p>
                                                <p className="text-foreground">St. Lawrence University, Kampala</p>
                                            </div>
                                        </li>
                                        <li className="flex items-center gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                                <Icon className="text-[20px]">schedule</Icon>
                                            </div>
                                            <div>
                                                <p className="mb-0.5 text-[11px] uppercase tracking-wider text-muted-foreground">Response Time</p>
                                                <p className="text-foreground">Usually within 24 hours</p>
                                            </div>
                                        </li>
                                    </ul>
                                </motion.div>

                                {/* Who You'll Reach */}
                                <motion.div variants={fadeUp} className="rounded-sm bg-card/80 p-6 shadow-theme-xs backdrop-blur-sm dark:bg-card/70">
                                    <h3 className="mb-4 text-sm font-semibold uppercase tracking-[0.18em] text-foreground">
                                        Who You Will Reach
                                    </h3>
                                    <ul className="space-y-4 text-sm">
                                        <li className="flex items-start gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                                <Icon className="text-[20px]">shield_person</Icon>
                                            </div>
                                            <div>
                                                <p className="font-medium text-foreground">Club Leadership</p>
                                                <p className="mt-0.5 text-xs text-muted-foreground">President, vice-president, and executive members</p>
                                            </div>
                                        </li>
                                        <li className="flex items-start gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                                <Icon className="text-[20px]">event</Icon>
                                            </div>
                                            <div>
                                                <p className="font-medium text-foreground">Event Organizers</p>
                                                <p className="mt-0.5 text-xs text-muted-foreground">Workshop leads, CTF coordinators, and speakers bureau</p>
                                            </div>
                                        </li>
                                        <li className="flex items-start gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                                                <Icon className="text-[20px]">public</Icon>
                                            </div>
                                            <div>
                                                <p className="font-medium text-foreground">Partnership Team</p>
                                                <p className="mt-0.5 text-xs text-muted-foreground">Industry collaboration, sponsorships, and campus outreach</p>
                                            </div>
                                        </li>
                                    </ul>
                                </motion.div>
                            </motion.aside>
                        </div>
                    </div>
                </section>
            </div>
        </PublicLayout>
    );
}
