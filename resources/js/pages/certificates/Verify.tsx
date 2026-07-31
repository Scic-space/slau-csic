import PublicLayout from '@/components/PublicLayout';
import { motion } from 'framer-motion';
import { Shield, CheckCircle, XCircle, Award, Calendar, Hash, User, BookOpen } from 'lucide-react';

interface CertificateData {
    holder_name: string;
    exam_title: string;
    score: number;
    passing_score: number;
    issued_at: string;
    certificate_id: string;
    verification_code: string;
    is_valid: boolean;
}

interface VerifyProps {
    status: 'found' | 'not_found';
    certificate: CertificateData | null;
}

const container = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: { staggerChildren: 0.06, delayChildren: 0.1 },
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

export default function Verify({ status, certificate }: VerifyProps) {
    return (
        <PublicLayout transparentNav title="Certificate Verification">
            <section className="relative flex min-h-screen w-full items-center justify-center px-6 pt-28 pb-16 md:px-8 lg:px-12">
                <div className="absolute inset-0 bg-gradient-to-b from-gray-100 via-gray-50 to-gray-100 dark:from-gray-950 dark:via-gray-950 dark:to-gray-900" />
                <div className="pointer-events-none absolute left-1/2 top-1/4 -translate-x-1/2 -translate-y-1/2 h-[300px] w-[300px] sm:h-[600px] sm:w-[600px] rounded-full bg-indigo-500/[0.04] blur-[120px]" />

                <motion.div
                    initial="hidden"
                    animate="visible"
                    variants={container}
                    className="relative z-10 mx-auto w-full max-w-2xl"
                >
                    <motion.div variants={fadeUp} className="mb-8 text-center">
                        <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-500 dark:text-white/70 backdrop-blur">
                            <Shield className="h-4 w-4 text-indigo-400" />
                            Certificate Verification
                        </div>
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-4xl">
                            Verify a Certificate
                        </h1>
                        <p className="mt-3 text-base text-gray-500 dark:text-white/50">
                            Enter a verification code to confirm the authenticity of a SLAU CSIC certificate.
                        </p>
                    </motion.div>

                    {status === 'not_found' && (
                        <motion.div
                            variants={fadeUp}
                            className="rounded-2xl border border-red-500/20 bg-red-500/[0.05] p-8 text-center backdrop-blur-sm"
                        >
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border border-red-500/20 bg-red-500/[0.08]">
                                <XCircle className="h-8 w-8 text-red-400" />
                            </div>
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-white">Certificate Not Found</h2>
                            <p className="mt-2 text-sm text-gray-500 dark:text-white/50">
                                No certificate matches this verification code. Please check the code and try again.
                            </p>
                        </motion.div>
                    )}

                    {status === 'found' && certificate && (
                        <motion.div
                            variants={fadeUp}
                            className="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] backdrop-blur-sm"
                        >
                            <div className={`flex items-center justify-center gap-3 px-6 py-4 ${
                                certificate.is_valid
                                    ? 'bg-emerald-500/[0.08] border-b border-emerald-500/20'
                                    : 'bg-red-500/[0.08] border-b border-red-500/20'
                            }`}>
                                {certificate.is_valid ? (
                                    <>
                                        <CheckCircle className="h-5 w-5 text-emerald-400" />
                                        <span className="text-sm font-semibold text-emerald-400">Valid Certificate</span>
                                    </>
                                ) : (
                                    <>
                                        <XCircle className="h-5 w-5 text-red-400" />
                                        <span className="text-sm font-semibold text-red-400">Certificate Revoked</span>
                                    </>
                                )}
                            </div>

                            <div className="p-8">
                                <div className="mb-8 text-center">
                                    <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full border-2 border-indigo-500/30 bg-indigo-500/[0.08]">
                                        <Award className="h-7 w-7 text-indigo-400" />
                                    </div>
                                    <p className="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-white/40">Certificate of Completion</p>
                                    <h2 className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{certificate.exam_title}</h2>
                                </div>

                                <div className="space-y-4">
                                    <div className="flex items-center gap-4 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/[0.02] px-5 py-4">
                                        <div className="flex h-10 w-10 items-center justify-center rounded-lg border border-indigo-500/20 bg-indigo-500/[0.08] text-indigo-400">
                                            <User className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p className="text-xs text-gray-500 dark:text-white/40">Awarded To</p>
                                            <p className="text-sm font-medium text-gray-900 dark:text-white">{certificate.holder_name}</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-4 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/[0.02] px-5 py-4">
                                        <div className="flex h-10 w-10 items-center justify-center rounded-lg border border-indigo-500/20 bg-indigo-500/[0.08] text-indigo-400">
                                            <BookOpen className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p className="text-xs text-gray-500 dark:text-white/40">Examination</p>
                                            <p className="text-sm font-medium text-gray-900 dark:text-white">{certificate.exam_title}</p>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div className="flex items-center gap-4 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/[0.02] px-5 py-4">
                                            <div className="flex h-10 w-10 items-center justify-center rounded-lg border border-indigo-500/20 bg-indigo-500/[0.08] text-indigo-400">
                                                <CheckCircle className="h-5 w-5" />
                                            </div>
                                            <div>
                                                <p className="text-xs text-gray-500 dark:text-white/40">Score</p>
                                                <p className="text-sm font-medium text-gray-900 dark:text-white">{certificate.score}%</p>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-4 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/[0.02] px-5 py-4">
                                            <div className="flex h-10 w-10 items-center justify-center rounded-lg border border-indigo-500/20 bg-indigo-500/[0.08] text-indigo-400">
                                                <Calendar className="h-5 w-5" />
                                            </div>
                                            <div>
                                                <p className="text-xs text-gray-500 dark:text-white/40">Issued</p>
                                                <p className="text-sm font-medium text-gray-900 dark:text-white">{certificate.issued_at}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-8 border-t border-gray-100 dark:border-white/5 pt-6 text-center">
                                    <div className="flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-white/30">
                                        <Hash className="h-3 w-3" />
                                        <span>Certificate ID: {certificate.certificate_id}</span>
                                    </div>
                                    <p className="mt-1 text-[10px] text-gray-400 dark:text-white/20">
                                        Verification Code: {certificate.verification_code}
                                    </p>
                                </div>
                            </div>
                        </motion.div>
                    )}
                </motion.div>
            </section>
        </PublicLayout>
    );
}
