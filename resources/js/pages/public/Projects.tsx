import PublicLayout from '@/components/PublicLayout';
import {
    GlowyWavesBackground,
    WaveSection,
} from '@/components/ui/glowy-waves-hero-shadcnui';
import { GlowCard } from '@/components/ui/spotlight-card';
import { motion } from 'framer-motion';
import { Code2, Shield, Target, GitBranch, Users, ExternalLink, ArrowRight } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface Member {
    id: number;
    name: string;
}

interface Project {
    id: number;
    name: string;
    description: string | null;
    type: string | null;
    status: string | null;
    repository_url: string | null;
    lead: Member | null;
    members: Member[];
}

interface DelivPillar {
    title: string;
    copy: string;
}

interface ProjectTrack {
    name: string;
    focus: string;
}

interface ProjectsProps {
    projects: Project[];
    deliveryPillars: DelivPillar[];
    projectTracks: ProjectTrack[];
}

const statusColors: Record<string, string> = {
    active: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
    completed: 'border-blue-500/20 bg-blue-500/10 text-blue-400',
    on_hold: 'border-amber-500/20 bg-amber-500/10 text-amber-400',
    planned: 'border-gray-500/20 bg-gray-500/10 text-gray-400',
};

const fadeUp = {
    hidden: { opacity: 0, y: 20 },
    visible: (i: number) => ({
        opacity: 1,
        y: 0,
        transition: { duration: 0.4, delay: i * 0.06, ease: 'easeOut' },
    }),
};

export default function Projects({ projects, deliveryPillars, projectTracks }: ProjectsProps) {
    return (
        <PublicLayout transparentNav>
            <GlowyWavesBackground>
                {/* Hero Banner */}
                <section className="relative flex w-full items-center justify-center px-6 pt-24 pb-12 md:px-8 lg:px-12">
                    <motion.div
                        initial="hidden"
                        animate="visible"
                        variants={{
                            hidden: { opacity: 0, y: 24 },
                            visible: {
                                opacity: 1,
                                y: 0,
                                transition: { duration: 0.8, staggerChildren: 0.12 },
                            },
                        }}
                        className="mx-auto max-w-4xl w-full text-center"
                    >
                        <motion.div
                            variants={{
                                hidden: { opacity: 0, y: 24 },
                                visible: { opacity: 1, y: 0, transition: { duration: 0.6 } },
                            }}
                            className="mb-4 inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur"
                        >
                            <Code2 className="h-4 w-4 text-emerald-400" />
                            Open Source
                        </motion.div>
                        <motion.h1
                            variants={{
                                hidden: { opacity: 0, y: 24 },
                                visible: { opacity: 1, y: 0, transition: { duration: 0.6 } },
                            }}
                            className="mb-4 text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-5xl lg:text-6xl"
                        >
                            Our{' '}
                            <span className="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">
                                Projects
                            </span>
                        </motion.h1>
                        <motion.p
                            variants={{
                                hidden: { opacity: 0, y: 24 },
                                visible: { opacity: 1, y: 0, transition: { duration: 0.6 } },
                            }}
                            className="mx-auto mb-8 max-w-3xl text-lg text-gray-600 dark:text-white/60"
                        >
                            Explore the projects our members are building — from security tools and CTF
                            challenges to campus solutions and open-source contributions.
                        </motion.p>
                    </motion.div>
                </section>

                {/* Gradient divider */}
                <div className="relative h-24 w-full overflow-hidden">
                    <div className="absolute inset-0 bg-gradient-to-b from-transparent via-indigo-500/5 to-transparent" />
                </div>

                {/* Delivery Pillars */}
                <WaveSection className="py-16 md:py-24">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-100px' }}
                        className="text-center mb-12"
                    >
                        <motion.div
                            variants={fadeUp}
                            custom={0}
                            className="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur mb-4"
                        >
                            <Target className="h-4 w-4 text-indigo-400" />
                            How We Build
                        </motion.div>
                        <motion.h2
                            variants={fadeUp}
                            custom={1}
                            className="text-2xl md:text-4xl font-bold text-gray-900 dark:text-white mb-3"
                        >
                            Delivery{' '}
                            <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">
                                Pillars
                            </span>
                        </motion.h2>
                    </motion.div>
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-50px' }}
                        className="grid grid-cols-1 md:grid-cols-3 gap-6"
                    >
                        {deliveryPillars.map((pillar, i) => (
                            <motion.div key={pillar.title} variants={fadeUp} custom={i}>
                                <div className="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-6 backdrop-blur-sm h-full">
                                    <h3 className="font-semibold text-gray-900 dark:text-white mb-2">{pillar.title}</h3>
                                    <p className="text-sm text-gray-500 dark:text-white/50 leading-relaxed">{pillar.copy}</p>
                                </div>
                            </motion.div>
                        ))}
                    </motion.div>
                </WaveSection>

                {/* Project Tracks */}
                <WaveSection className="py-16 md:py-24">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-100px' }}
                        className="text-center mb-12"
                    >
                        <motion.div
                            variants={fadeUp}
                            custom={0}
                            className="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur mb-4"
                        >
                            <GitBranch className="h-4 w-4 text-indigo-400" />
                            Focus Areas
                        </motion.div>
                        <motion.h2
                            variants={fadeUp}
                            custom={1}
                            className="text-2xl md:text-4xl font-bold text-gray-900 dark:text-white mb-3"
                        >
                            Project{' '}
                            <span className="bg-gradient-to-r from-indigo-400 to-violet-300 bg-clip-text text-transparent">
                                Tracks
                            </span>
                        </motion.h2>
                    </motion.div>
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-50px' }}
                        className="grid grid-cols-1 md:grid-cols-3 gap-6"
                    >
                        {projectTracks.map((track, i) => (
                            <motion.div key={track.name} variants={fadeUp} custom={i}>
                                <GlowCard glowColor="purple" customSize className="!p-0 !bg-transparent !border-0 !backdrop-blur-none !shadow-none !gap-0 h-full">
                                    <div className="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-6 backdrop-blur-sm transition-all hover:border-purple-500/30 hover:bg-gray-50 dark:hover:bg-white/[0.06] h-full">
                                        <div className="mb-3 flex h-10 w-10 items-center justify-center rounded-xl border border-purple-500/20 bg-purple-500/20">
                                            <Shield className="h-5 w-5 text-purple-400" />
                                        </div>
                                        <h3 className="font-semibold text-gray-900 dark:text-white mb-2">{track.name}</h3>
                                        <p className="text-sm text-gray-500 dark:text-white/50 leading-relaxed">{track.focus}</p>
                                    </div>
                                </GlowCard>
                            </motion.div>
                        ))}
                    </motion.div>
                </WaveSection>

                {/* Current Projects */}
                <WaveSection className="py-16 md:py-24">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-100px' }}
                        className="flex items-end justify-between mb-12"
                    >
                        <div>
                            <motion.div
                                variants={fadeUp}
                                custom={0}
                                className="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/70 backdrop-blur mb-4"
                            >
                                <Code2 className="h-4 w-4 text-emerald-400" />
                                Current
                            </motion.div>
                            <motion.h2
                                variants={fadeUp}
                                custom={1}
                                className="text-2xl md:text-4xl font-bold text-gray-900 dark:text-white"
                            >
                                Active{' '}
                                <span className="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">
                                    Projects
                                </span>{' '}
                                <span className="text-gray-500 dark:text-white/40">({projects.length})</span>
                            </motion.h2>
                        </div>
                    </motion.div>

                    {projects.length === 0 ? (
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-8 sm:p-16 text-center backdrop-blur-sm"
                        >
                            <p className="text-gray-500 dark:text-white/40">No projects yet.</p>
                        </motion.div>
                    ) : (
                        <motion.div
                            initial="hidden"
                            whileInView="visible"
                            viewport={{ once: true, margin: '-50px' }}
                            className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                        >
                            {projects.map((project, i) => (
                                <motion.div key={project.id} variants={fadeUp} custom={i}>
                                    <GlowCard glowColor="green" customSize className="!p-0 !bg-transparent !border-0 !backdrop-blur-none !shadow-none !gap-0 h-full">
                                        <div className="group rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-6 backdrop-blur-sm transition-all hover:border-emerald-500/30 hover:bg-gray-50 dark:hover:bg-white/[0.06] hover:-translate-y-0.5 h-full flex flex-col">
                                            <div className="mb-3 flex items-start justify-between gap-3">
                                                <div className="rounded-xl border border-emerald-500/20 bg-emerald-500/20 p-2.5">
                                                    <Code2 className="h-5 w-5 text-emerald-400" />
                                                </div>
                                                <div className="flex flex-wrap gap-1.5">
                                                    {project.type && (
                                                        <span className="rounded-full border border-indigo-500/20 bg-indigo-500/20 px-2.5 py-0.5 text-[11px] font-medium text-indigo-400">
                                                            {project.type}
                                                        </span>
                                                    )}
                                                    {project.status && (
                                                        <span className={`rounded-full border px-2.5 py-0.5 text-[11px] font-medium ${statusColors[project.status] || 'border-gray-500/20 bg-gray-500/10 text-gray-400'}`}>
                                                            {project.status.replace('_', ' ')}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>

                                            <h3 className="text-base font-semibold text-gray-900 dark:text-white mb-1 transition-colors group-hover:text-emerald-400">
                                                {project.name}
                                            </h3>
                                            {project.description && (
                                                <p className="text-sm text-gray-500 dark:text-white/50 leading-relaxed line-clamp-3 mb-auto">
                                                    {project.description}
                                                </p>
                                            )}

                                            <div className="mt-4 space-y-2 pt-3 border-t border-gray-200 dark:border-white/5">
                                                {project.lead && (
                                                    <div className="flex items-center gap-2 text-xs text-gray-500 dark:text-white/40">
                                                        <Users className="h-3.5 w-3.5" />
                                                        <span>Lead: {project.lead.name}</span>
                                                    </div>
                                                )}
                                                {project.members.length > 0 && (
                                                    <div className="flex items-center gap-2 text-xs text-gray-500 dark:text-white/40">
                                                        <Users className="h-3.5 w-3.5" />
                                                        <span>{project.members.length} member{project.members.length !== 1 ? 's' : ''}</span>
                                                    </div>
                                                )}
                                            </div>

                                            {project.repository_url && (
                                                <a
                                                    href={project.repository_url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-400 hover:text-indigo-300 transition-colors"
                                                >
                                                    <ExternalLink className="h-4 w-4" />
                                                    GitHub
                                                </a>
                                            )}
                                        </div>
                                    </GlowCard>
                                </motion.div>
                            ))}
                        </motion.div>
                    )}
                </WaveSection>
            </GlowyWavesBackground>
        </PublicLayout>
    );
}
