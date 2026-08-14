import PublicLayout from '@/components/PublicLayout';
import { GlowyWavesBackground } from '@/components/ui/glowy-waves-hero-shadcnui';
import { motion } from 'framer-motion';
import { usePage, router } from '@inertiajs/react';
import { Mail, MapPin, Send, CheckCircle, Clock, Shield, Globe, MessageSquare } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useState } from 'react';
import type { PageProps as InertiaPageProps } from '@inertiajs/react';

interface PageProps extends InertiaPageProps {
    flash: {
        success?: string;
        error?: string;
    };
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

export default function Contact() {
    const { flash } = usePage<PageProps>().props;

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
        <PublicLayout transparentNav>
            <GlowyWavesBackground>
                {/* Hero */}
                <section className="relative flex w-full items-center justify-center px-6 pt-28 pb-16 md:px-8 lg:px-12">
                    <motion.div initial="hidden" animate="visible" variants={container} className="mx-auto w-full max-w-6xl text-center">
                        <motion.div variants={fadeUp} className="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur mb-6">
                            <Mail className="h-4 w-4 text-indigo-400" />
                            Get in Touch
                        </motion.div>
                        <motion.h1 variants={fadeUp} className="text-4xl font-bold tracking-tight text-gray-900 dark:text-white md:text-5xl lg:text-6xl leading-[1.1]">
                            Let{' '}
                            <span className="bg-gradient-to-r from-indigo-400 via-indigo-300 to-violet-300 bg-clip-text text-transparent">
                                start a conversation.
                            </span>
                        </motion.h1>
                        <motion.p variants={fadeUp} className="mt-5 text-lg text-gray-500 dark:text-white/50 max-w-2xl mx-auto">
                            Members, speakers, partners, and campus visitors — reach out and we will get back to you.
                        </motion.p>
                    </motion.div>
                </section>

                {/* Form + Info */}
                <section className="px-6 pb-28 md:px-8 lg:px-12">
                    <div className="mx-auto max-w-6xl">
                        <div className="grid gap-10 lg:grid-cols-[1fr_0.7fr] items-start">

                            {/* Form */}
                            <motion.div
                                initial="hidden"
                                whileInView="visible"
                                viewport={{ once: true, margin: '-50px' }}
                                variants={container}
                            >
                                {flash.success && (
                                    <motion.div variants={fadeUp} className="mb-6 flex items-center gap-2 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-400">
                                        <CheckCircle className="h-4 w-4 shrink-0" />
                                        {flash.success}
                                    </motion.div>
                                )}

                                <motion.form
                                    variants={fadeUp}
                                    onSubmit={handleSubmit}
                                    className="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-6 md:p-8 backdrop-blur-sm space-y-5"
                                >
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label htmlFor="name" className="mb-2 block text-sm font-medium text-gray-700 dark:text-white/60">
                                                Full name
                                            </label>
                                            <input
                                                id="name"
                                                name="name"
                                                type="text"
                                                value={name}
                                                onChange={(e) => setName(e.target.value)}
                                                required
                                                className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.05] px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 transition-colors focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                                                placeholder="Your name"
                                            />
                                        </div>
                                        <div>
                                            <label htmlFor="email" className="mb-2 block text-sm font-medium text-gray-700 dark:text-white/60">
                                                Email
                                            </label>
                                            <input
                                                id="email"
                                                name="email"
                                                type="email"
                                                value={email}
                                                onChange={(e) => setEmail(e.target.value)}
                                                required
                                                className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.05] px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 transition-colors focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                                                placeholder="you@example.com"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label htmlFor="topic" className="mb-2 block text-sm font-medium text-gray-700 dark:text-white/60">
                                            What is this about?
                                        </label>
                                        <select
                                            id="topic"
                                            name="topic"
                                            value={topic}
                                            onChange={(e) => setTopic(e.target.value)}
                                            required
                                            className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.05] px-4 py-3 text-sm text-gray-900 dark:text-white transition-colors focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                                        >
                                            <option value="" className="bg-white dark:bg-[#0f172a] dark:text-white/50">Select a topic</option>
                                            {topics.map((t) => (
                                                <option key={t} value={t} className="bg-white dark:bg-[#0f172a] dark:text-white">{t}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label htmlFor="message" className="mb-2 block text-sm font-medium text-gray-700 dark:text-white/60">
                                            Message
                                        </label>
                                        <textarea
                                            id="message"
                                            name="message"
                                            rows={5}
                                            value={message}
                                            onChange={(e) => setMessage(e.target.value)}
                                            required
                                            className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.05] px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 transition-colors focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                                            placeholder="Tell us what you have in mind..."
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        disabled={submitting}
                                        className="group gap-2 rounded-full px-8 py-5 text-sm uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25 disabled:opacity-50"
                                    >
                                        {submitting ? 'Sending...' : 'Send Message'}
                                        <Send className="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
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
                                <motion.div variants={fadeUp} className="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-6 backdrop-blur-sm">
                                    <h3 className="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-gray-900 dark:text-white">
                                        Contact Information
                                    </h3>
                                    <ul className="space-y-4 text-sm text-gray-500 dark:text-white/50">
                                        <li className="flex items-center gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/[0.08]">
                                                <Mail className="h-4 w-4 text-indigo-400" />
                                            </div>
                                            <div>
                                                <p className="text-[11px] uppercase tracking-wider text-gray-400 dark:text-white/30 mb-0.5">Email</p>
                                                <a href="mailto:cyberclub@slau.ac.ug" className="text-gray-900 dark:text-white hover:text-indigo-400 transition-colors">
                                                    cyberclub@slau.ac.ug
                                                </a>
                                            </div>
                                        </li>
                                        <li className="flex items-center gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/[0.08]">
                                                <MapPin className="h-4 w-4 text-indigo-400" />
                                            </div>
                                            <div>
                                                <p className="text-[11px] uppercase tracking-wider text-gray-400 dark:text-white/30 mb-0.5">Location</p>
                                                <p className="text-gray-900 dark:text-white">St. Lawrence University, Kampala</p>
                                            </div>
                                        </li>
                                        <li className="flex items-center gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/[0.08]">
                                                <Clock className="h-4 w-4 text-indigo-400" />
                                            </div>
                                            <div>
                                                <p className="text-[11px] uppercase tracking-wider text-gray-400 dark:text-white/30 mb-0.5">Response Time</p>
                                                <p className="text-gray-900 dark:text-white">Usually within 24 hours</p>
                                            </div>
                                        </li>
                                    </ul>
                                </motion.div>

                                {/* Who You'll Reach */}
                                <motion.div variants={fadeUp} className="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-6 backdrop-blur-sm">
                                    <h3 className="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-gray-900 dark:text-white">
                                        Who You Will Reach
                                    </h3>
                                    <ul className="space-y-4 text-sm">
                                        <li className="flex items-start gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/[0.08]">
                                                <Shield className="h-4 w-4 text-indigo-400" />
                                            </div>
                                            <div>
                                                <p className="font-medium text-gray-900 dark:text-white">Club Leadership</p>
                                                <p className="text-xs text-gray-400 dark:text-white/30 mt-0.5">President, vice-president, and executive members</p>
                                            </div>
                                        </li>
                                        <li className="flex items-start gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/[0.08]">
                                                <MessageSquare className="h-4 w-4 text-emerald-400" />
                                            </div>
                                            <div>
                                                <p className="font-medium text-gray-900 dark:text-white">Event Organizers</p>
                                                <p className="text-xs text-gray-400 dark:text-white/30 mt-0.5">Workshop leads, CTF coordinators, and speakers bureau</p>
                                            </div>
                                        </li>
                                        <li className="flex items-start gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/[0.08]">
                                                <Globe className="h-4 w-4 text-amber-400" />
                                            </div>
                                            <div>
                                                <p className="font-medium text-gray-900 dark:text-white">Partnership Team</p>
                                                <p className="text-xs text-gray-400 dark:text-white/30 mt-0.5">Industry collaboration, sponsorships, and campus outreach</p>
                                            </div>
                                        </li>
                                    </ul>
                                </motion.div>
                            </motion.aside>
                        </div>
                    </div>
                </section>
            </GlowyWavesBackground>
        </PublicLayout>
    );
}
