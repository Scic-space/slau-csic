import { Link } from '@inertiajs/react';
import type { PageProps } from '@/types';
import PublicLayout from '@/components/PublicLayout';
import { GlowyWavesBackground } from '@/components/ui/glowy-waves-hero-shadcnui';
import { motion } from 'framer-motion';
import { ArrowLeft, MessageCircle } from 'lucide-react';

interface PublicMember {
    id: number;
    name: string;
    email: string;
    role_names: string[];
    membership_status: string;
    membership_type: string;
    profile_photo_url: string | null;
    program: string | null;
    faculty: string | null;
    year_of_study: string | null;
    bio: string | null;
    headline: string | null;
    github_username: string | null;
    linkedin_url: string | null;
    discord_username: string | null;
}

interface MemberShowPage extends PageProps {
    member: PublicMember;
}

function statusBadge(status: string, type: string) {
    if (type === 'alumni') return 'bg-purple-500/20 text-purple-300';
    if (status === 'active') return 'bg-green-500/20 text-green-300';
    return 'bg-white/10 text-white/50';
}

export default function MemberShow({ auth, member }: MemberShowPage) {
    return (
        <PublicLayout transparentNav>
            <GlowyWavesBackground>
                <section className="relative flex w-full items-center justify-center px-6 pt-28 pb-16 md:px-8 lg:px-12">
                    <motion.div
                        initial={{ opacity: 0, y: 16 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4 }}
                        className="mx-auto w-full max-w-3xl"
                    >
                        <Link
                            href="/members"
                            className="mb-8 inline-flex items-center gap-1.5 text-sm text-white/60 transition-colors hover:text-white"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Back to Directory
                        </Link>

                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.5, delay: 0.1 }}
                            className="rounded-2xl border border-white/10 bg-white/[0.03] p-8 backdrop-blur-sm"
                        >
                            <div className="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                                <div className="w-24 h-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-3xl shrink-0">
                                    {member.profile_photo_url ? (
                                        <img src={member.profile_photo_url} alt="" className="w-full h-full rounded-full object-cover" />
                                    ) : (
                                        member.name.charAt(0).toUpperCase()
                                    )}
                                </div>

                                <div className="text-center sm:text-left flex-1">
                                    <h1 className="text-2xl font-bold text-white">{member.name}</h1>
                                    {member.headline && (
                                        <p className="text-lg text-white/40 mt-1">{member.headline}</p>
                                    )}
                                    <div className="flex flex-wrap justify-center sm:justify-start gap-2 mt-3">
                                        <span className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusBadge(member.membership_status, member.membership_type)}`}>
                                            {member.membership_type === 'alumni' ? 'Alumni' : member.membership_status}
                                        </span>
                                        {member.role_names.map((role) => (
                                            <span key={role} className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-500/20 text-indigo-300">
                                                {role}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {member.bio && (
                                <div className="mt-6 pt-6 border-t border-white/10">
                                    <h2 className="text-sm font-semibold text-white/50 uppercase tracking-wide">About</h2>
                                    <p className="mt-2 text-white/60 whitespace-pre-line leading-relaxed">{member.bio}</p>
                                </div>
                            )}

                            <div className="mt-6 pt-6 border-t border-white/10">
                                <h2 className="text-sm font-semibold text-white/50 uppercase tracking-wide mb-3">Details</h2>
                                <dl className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {member.program && (
                                        <div><dt className="text-xs text-white/40">Program</dt><dd className="text-white">{member.program}</dd></div>
                                    )}
                                    {member.faculty && (
                                        <div><dt className="text-xs text-white/40">Faculty</dt><dd className="text-white">{member.faculty}</dd></div>
                                    )}
                                    {member.year_of_study && (
                                        <div><dt className="text-xs text-white/40">Year of Study</dt><dd className="text-white">Year {member.year_of_study}</dd></div>
                                    )}
                                    <div><dt className="text-xs text-white/40">Email</dt><dd className="text-white">{member.email}</dd></div>
                                </dl>
                            </div>

                            {(member.github_username || member.linkedin_url || member.discord_username) && (
                                <div className="mt-6 pt-6 border-t border-white/10">
                                    <h2 className="text-sm font-semibold text-white/50 uppercase tracking-wide mb-3">Connect</h2>
                                    <div className="flex flex-wrap gap-3">
                                        {member.github_username && (
                                            <a
                                                href={`https://github.com/${member.github_username}`}
                                                target="_blank" rel="noopener noreferrer"
                                                className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white/70 transition-colors hover:bg-white/10"
                                            >
                                                <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                                GitHub
                                            </a>
                                        )}
                                        {member.linkedin_url && (
                                            <a
                                                href={member.linkedin_url}
                                                target="_blank" rel="noopener noreferrer"
                                                className="inline-flex items-center gap-2 rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-2 text-sm text-blue-300 transition-colors hover:bg-blue-500/20"
                                            >
                                                <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                                LinkedIn
                                            </a>
                                        )}
                                        {member.discord_username && (
                                            <span className="inline-flex items-center gap-2 rounded-xl border border-indigo-500/20 bg-indigo-500/10 px-4 py-2 text-sm text-indigo-300">
                                                <MessageCircle className="h-5 w-5" />
                                                {member.discord_username}
                                            </span>
                                        )}
                                    </div>
                                </div>
                            )}
                        </motion.div>
                    </motion.div>
                </section>
            </GlowyWavesBackground>
        </PublicLayout>
    );
}
