import PublicLayout from '@/components/PublicLayout';
import {
    GlowyWavesBackground,
    WaveSection,
} from '@/components/ui/glowy-waves-hero-shadcnui';
import { motion } from 'framer-motion';
import { Link } from '@inertiajs/react';
import {
    Shield,
    Users,
    ArrowRight,
    Crown,
    Swords,
    BookOpen,
    Megaphone,
    Code,
    Trophy,
    GraduationCap,
} from 'lucide-react';

interface TeamMember {
    name: string;
    role: string;
    department: string;
    image: string;
    bio: string;
}

const leadership: TeamMember[] = [
    {
        name: 'Nantume Josline',
        role: 'President',
        department: 'Executive',
        image: '/images/club/team/president.jpg',
        bio: 'Leads the club\u2019s vision and strategy, coordinates with the university, and steers every major initiative.',
    },
    {
        name: 'Shid cracker',
        role: 'Vice President',
        department: 'Executive',
        image: '/images/club/team/vice-president.jpg',
        bio: 'Supports club operations, drives member engagement, and leads outreach across the campus community.',
    },
    {
        name: 'Shakirah',
        role: 'General Secretary',
        department: 'Executive',
        image: '/images/club/team/general-secretary.jpg',
        bio: 'Keeps club records, manages internal communications, and ensures every team stays in sync.',
    },
    {
        name: 'Ssali Kevin',
        role: 'Head of Projects',
        department: 'Projects',
        image: '/images/club/team/head-of-projects.jpg',
        bio: 'Plans and delivers the club\u2019s projects, from community outreach to hands-on tech initiatives.',
    },
    {
        name: 'Osiawo',
        role: 'Lead Developer',
        department: 'Development',
        image: '/images/club/team/lead-developer.jpg',
        bio: 'Leads the technical build of club systems and mentors members on real-world development skills.',
    },
    {
        name: 'Shafic',
        role: 'Treasurer',
        department: 'Finance',
        image: '/images/club/team/treasurer.jpg',
        bio: 'Manages the club\u2019s finances, tracks budgets, and handles sponsorship and funding coordination.',
    },
];

const advisors: TeamMember[] = [
    {
        name: 'Mr. Arfat',
        role: 'Club Mentor',
        department: 'Advisory',
        image: '/images/club/team/club-mentor.jpg',
        bio: 'Guides and mentors members, sharing experience that helps the team grow with every project.',
    },
    {
        name: 'Mr. Mukiibi Zakaria',
        role: 'Patron',
        department: 'Advisory',
        image: '/images/club/team/patron.jpg',
        bio: 'Champions the club\u2019s mission, supporting its activities and growth within the university.',
    },
];

const coreTeam: TeamMember[] = [
    {
        name: 'Shid cracker',
        role: 'CTF Lead',
        department: 'Competitions',
        image: '/images/club/team/vice-president.jpg',
        bio: 'Organizes CTF competitions, coaches the competition team, and manages challenge development.',
    },
    {
        name: 'Ssali Kevin',
        role: 'Workshops Coordinator',
        department: 'Education',
        image: '/images/club/team/head-of-projects.jpg',
        bio: 'Plans and delivers technical workshops on cybersecurity topics for members of all skill levels.',
    },
    {
        name: 'Nantume Josline',
        role: 'Public Relations',
        department: 'Communications',
        image: '/images/club/team/president.jpg',
        bio: 'Manages the club\u2019s social media presence, public communications, and brand identity.',
    },
    {
        name: 'Shakirah',
        role: 'Events Lead',
        department: 'Operations',
        image: '/images/club/team/general-secretary.jpg',
        bio: 'Organizes club events, meetups, and manages logistics for all in-person and virtual gatherings.',
    },
    {
        name: 'Osiawo',
        role: 'Technical Lead',
        department: 'Education',
        image: '/images/club/team/lead-developer.jpg',
        bio: 'Leads technical projects, maintains club infrastructure, and mentors members on hands-on skills.',
    },
];

const departments = [
    { name: 'Executive', icon: Crown, color: 'text-yellow-400' },
    { name: 'Competitions', icon: Swords, color: 'text-red-400' },
    { name: 'Education', icon: BookOpen, color: 'text-blue-400' },
    { name: 'Communications', icon: Megaphone, color: 'text-pink-400' },
    { name: 'Operations', icon: Shield, color: 'text-emerald-400' },
    { name: 'Finance', icon: Trophy, color: 'text-amber-400' },
];

const container = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: { staggerChildren: 0.08, delayChildren: 0.1 },
    },
};

const fadeUp = {
    hidden: { opacity: 0, y: 24 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.5, ease: 'easeOut' },
    },
};

const fadeIn = {
    hidden: { opacity: 0, y: 20 },
    visible: (i: number) => ({
        opacity: 1,
        y: 0,
        transition: { duration: 0.5, delay: i * 0.08, ease: 'easeOut' },
    }),
};

function MemberCard({ member, index }: { member: TeamMember; index: number }) {
    const dept = departments.find((d) => d.name === member.department);

    return (
        <motion.div
            custom={index}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: '-60px' }}
            variants={fadeIn}
            className="group relative overflow-hidden rounded-2xl border border-white/[0.06] bg-white/[0.02] backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-indigo-500/30 hover:shadow-[0_0_40px_-10px_rgba(99,102,241,0.15)]"
        >
            <div className="relative overflow-hidden">
                <img
                    src={member.image}
                    alt={member.name}
                    className="aspect-[3/4] w-full object-cover object-top transition-transform duration-700 group-hover:scale-105"
                />
            </div>

            <div className="p-5">
                <div className="mb-2 flex items-center gap-2">
                    {dept && (
                        <span className={`inline-flex items-center gap-1 text-[11px] font-medium ${dept.color}`}>
                            <dept.icon className="h-3 w-3" />
                            {dept.name}
                        </span>
                    )}
                </div>
                <h3 className="text-lg font-bold tracking-tight text-white">{member.name}</h3>
                <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-indigo-400">
                    {member.role}
                </p>
                <p className="text-sm leading-relaxed text-gray-500">{member.bio}</p>
            </div>
        </motion.div>
    );
}

export default function Team() {
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
                                <Users className="h-4 w-4 text-indigo-400" />
                                Our Team
                            </motion.div>

                            <motion.h1
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.1 }}
                                className="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight"
                            >
                                The people{' '}
                                <span className="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                                    running the club
                                </span>
                            </motion.h1>

                            <motion.p
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.2 }}
                                className="text-lg text-white/50 mb-8 max-w-2xl leading-relaxed"
                            >
                                A dedicated team of students managing everything from competitions and
                                workshops to outreach and operations.
                            </motion.p>

                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.3 }}
                                className="flex flex-wrap gap-3"
                            >
                                {departments.map((dept) => {
                                    const Icon = dept.icon;
                                    return (
                                        <span
                                            key={dept.name}
                                            className={`inline-flex items-center gap-1.5 rounded-full border border-white/[0.06] bg-white/[0.03] px-3 py-1.5 text-xs font-medium ${dept.color}`}
                                        >
                                            <Icon className="h-3.5 w-3.5" />
                                            {dept.name}
                                        </span>
                                    );
                                })}
                            </motion.div>
                        </div>
                    </div>
                </div>
            </GlowyWavesBackground>

            {/* Leadership */}
            <WaveSection variant="default">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-80px' }}
                        variants={container}
                        className="mb-10"
                    >
                        <motion.div variants={fadeUp} className="mb-2 inline-flex items-center gap-2 rounded-full border border-yellow-500/20 bg-yellow-500/10 px-3 py-1 text-xs font-medium text-yellow-400">
                            <Crown className="h-3.5 w-3.5" />
                            Executive Team
                        </motion.div>
                        <motion.h2 variants={fadeUp} className="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Club Leadership
                        </motion.h2>
                    </motion.div>

                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {leadership.map((member, i) => (
                            <MemberCard key={member.name} member={member} index={i} />
                        ))}
                    </div>
                </div>
            </WaveSection>

            {/* Advisors */}
            <WaveSection variant="default">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-80px' }}
                        variants={container}
                        className="mb-10"
                    >
                        <motion.div variants={fadeUp} className="mb-2 inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">
                            <GraduationCap className="h-3.5 w-3.5" />
                            Advisory
                        </motion.div>
                        <motion.h2 variants={fadeUp} className="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Our Guides
                        </motion.h2>
                        <motion.p variants={fadeUp} className="mt-2 max-w-2xl text-sm leading-relaxed text-gray-500">
                            The experience and support behind the club\u2019s growth.
                        </motion.p>
                    </motion.div>

                    <div className="grid gap-6 sm:grid-cols-2">
                        {advisors.map((member, i) => (
                            <MemberCard key={member.name} member={member} index={i} />
                        ))}
                    </div>
                </div>
            </WaveSection>

            {/* Core Team */}
            <WaveSection variant="default">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-80px' }}
                        variants={container}
                        className="mb-10"
                    >
                        <motion.div variants={fadeUp} className="mb-2 inline-flex items-center gap-2 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-400">
                            <Code className="h-3.5 w-3.5" />
                            Core Team
                        </motion.div>
                        <motion.h2 variants={fadeUp} className="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Department Leads
                        </motion.h2>
                    </motion.div>

                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {coreTeam.map((member, i) => (
                            <MemberCard key={member.name} member={member} index={i} />
                        ))}
                    </div>
                </div>
            </WaveSection>

            {/* Join CTA */}
            <WaveSection variant="default">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.5 }}
                        className="relative overflow-hidden rounded-2xl border border-indigo-500/20 bg-gradient-to-br from-indigo-500/[0.07] via-purple-500/[0.04] to-transparent p-8 sm:p-12 text-center"
                    >
                        <div className="pointer-events-none absolute -right-20 -top-20 h-48 w-48 rounded-full bg-indigo-500/10 blur-3xl" />

                        <Users className="mx-auto mb-4 h-8 w-8 text-indigo-400" />
                        <h2 className="text-2xl font-bold tracking-tight text-white sm:text-3xl mb-3">
                            Want to join the team?
                        </h2>
                        <p className="mx-auto mb-8 max-w-lg text-gray-400 leading-relaxed">
                            We&apos;re always looking for dedicated members to take on leadership roles.
                            Get involved, contribute to the club, and grow your skills.
                        </p>
                        <div className="flex flex-wrap justify-center gap-3">
                            <Link
                                href="/auth/register"
                                className="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/25 transition-colors hover:bg-indigo-500"
                            >
                                Join SLAU CSIC
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                            <Link
                                href="/contact"
                                className="inline-flex items-center gap-2 rounded-full border border-white/[0.1] bg-white/[0.03] px-6 py-3 text-sm font-medium text-white/80 transition-all hover:border-white/[0.2] hover:bg-white/[0.06]"
                            >
                                Contact Us
                            </Link>
                        </div>
                    </motion.div>
                </div>
            </WaveSection>
        </PublicLayout>
    );
}
