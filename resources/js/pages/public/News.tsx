import PublicLayout from '@/components/PublicLayout';
import {
    GlowyWavesBackground,
    WaveSection,
} from '@/components/ui/glowy-waves-hero-shadcnui';
import { motion } from 'framer-motion';
import { Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    Newspaper,
    ShieldAlert,
    Bug,
    FileText,
    Building2,
    Wrench,
    Play,
    ExternalLink,
    ArrowRight,
    Sparkles,
} from 'lucide-react';

interface NewsArticle {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    category: string;
    content_type: string;
    source_name: string | null;
    source_url: string | null;
    thumbnail_url: string | null;
    video_url: string | null;
    published_at: string;
    embed_url: string | null;
}

interface NewsProps {
    featured: NewsArticle | null;
    articles: NewsArticle[];
}

const categoryConfig: Record<string, { label: string; icon: React.ElementType; color: string; badge: string }> = {
    threat_intel: {
        label: 'Threat Intel',
        icon: ShieldAlert,
        color: 'text-red-400',
        badge: 'border-red-500/20 bg-red-500/10 text-red-400',
    },
    vulnerabilities: {
        label: 'Vulnerabilities',
        icon: Bug,
        color: 'text-amber-400',
        badge: 'border-amber-500/20 bg-amber-500/10 text-amber-400',
    },
    policy_compliance: {
        label: 'Policy & Compliance',
        icon: FileText,
        color: 'text-blue-400',
        badge: 'border-blue-500/20 bg-blue-500/10 text-blue-400',
    },
    industry: {
        label: 'Industry',
        icon: Building2,
        color: 'text-emerald-400',
        badge: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
    },
    tools_research: {
        label: 'Tools & Research',
        icon: Wrench,
        color: 'text-purple-400',
        badge: 'border-purple-500/20 bg-purple-500/10 text-purple-400',
    },
};

const fadeUp = {
    hidden: { opacity: 0, y: 20 },
    visible: (i: number) => ({
        opacity: 1,
        y: 0,
        transition: { duration: 0.4, delay: i * 0.06, ease: 'easeOut' },
    }),
};

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function timeAgo(dateStr: string): string {
    const now = new Date();
    const date = new Date(dateStr);
    const diffMs = now.getTime() - date.getTime();
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays}d ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)}w ago`;
    return formatDate(dateStr);
}

function VideoPlayer({ videoUrl, title }: { videoUrl: string; title: string }) {
    const isLocal = /\.(mp4|webm|ogg)$/i.test(videoUrl);

    if (isLocal) {
        return (
            <div className="relative aspect-video overflow-hidden rounded-xl border border-white/[0.06] bg-black">
                <video
                    src={videoUrl}
                    controls
                    playsInline
                    preload="metadata"
                    className="absolute inset-0 h-full w-full"
                >
                    Your browser does not support the video tag.
                </video>
            </div>
        );
    }

    return (
        <div className="relative aspect-video overflow-hidden rounded-xl border border-white/[0.06]">
            <iframe
                src={videoUrl.includes('?') ? `${videoUrl}&autoplay=1` : `${videoUrl}?autoplay=1`}
                title={title}
                className="absolute inset-0 h-full w-full"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowFullScreen
                referrerPolicy="no-referrer-when-downgrade"
            />
        </div>
    );
}

function VideoCard({ article }: { article: NewsArticle }) {
    const [playing, setPlaying] = useState(false);
    const cat = categoryConfig[article.category];
    const Icon = cat?.icon ?? Newspaper;

    if (playing && article.video_url) {
        return (
            <div className="flex h-full flex-col rounded-2xl border border-white/[0.06] bg-white/[0.02] p-6 transition-all duration-300 hover:border-white/[0.12]">
                <VideoPlayer videoUrl={article.video_url} title={article.title} />
                <div className="mt-3 flex items-center gap-2">
                    <span className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ${cat?.badge ?? 'border-gray-500/20 bg-gray-500/10 text-gray-400'}`}>
                        <Icon className="mr-1 h-3 w-3" />
                        {cat?.label ?? article.category}
                    </span>
                    <span className="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-400">
                        <Play className="h-3 w-3" /> Now Playing
                    </span>
                </div>
                <h3 className="mt-2 text-base font-semibold tracking-tight text-white line-clamp-2 break-words">
                    {article.title}
                </h3>
            </div>
        );
    }

    return (
        <button
            onClick={() => setPlaying(true)}
            className="group flex h-full flex-col rounded-2xl border border-white/[0.06] bg-white/[0.02] p-6 text-left transition-all duration-300 hover:border-white/[0.12] hover:bg-white/[0.04] hover:shadow-lg hover:shadow-black/20"
        >
            {article.thumbnail_url ? (
                <div className="relative mb-4 overflow-hidden rounded-xl">
                    <img
                        src={article.thumbnail_url}
                        alt=""
                        className="h-40 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 flex items-center justify-center bg-black/40 transition-colors group-hover:bg-black/30">
                        <div className="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm transition-transform group-hover:scale-110">
                            <Play className="h-6 w-6 text-white fill-white" />
                        </div>
                    </div>
                </div>
            ) : (
                <div className="relative mb-4 flex h-40 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-emerald-500/10 to-purple-500/10">
                    <Play className="h-12 w-12 text-emerald-400/60" />
                </div>
            )}

            <div className="flex items-center gap-2 mb-3">
                <span className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ${cat?.badge ?? 'border-gray-500/20 bg-gray-500/10 text-gray-400'}`}>
                    <Icon className="mr-1 h-3 w-3" />
                    {cat?.label ?? article.category}
                </span>
                <span className="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-400">
                    <Play className="h-3 w-3" />
                </span>
            </div>

            <h3 className="text-base font-semibold tracking-tight text-white line-clamp-2 break-words">
                {article.title}
            </h3>

            <p className="mt-2 flex-1 text-sm text-gray-400 line-clamp-3">
                {article.excerpt}
            </p>

            <div className="mt-4 flex items-center gap-3 text-xs text-gray-500">
                <span>{timeAgo(article.published_at)}</span>
                {article.source_name && (
                    <>
                        <span className="text-gray-700">&middot;</span>
                        <span className="truncate">{article.source_name}</span>
                    </>
                )}
            </div>
        </button>
    );
}

export default function News({ featured, articles }: NewsProps) {
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
                                <Newspaper className="h-4 w-4 text-indigo-400" />
                                Cybersecurity News
                            </motion.div>

                            <motion.h1
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.1 }}
                                className="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight"
                            >
                                Stay Ahead of the{' '}
                                <span className="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                                    Threat Landscape
                                </span>
                            </motion.h1>

                            <motion.p
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.2 }}
                                className="text-lg text-white/50 mb-8 max-w-2xl leading-relaxed"
                            >
                                Curated intelligence on vulnerabilities, threat actors, policy shifts,
                                and the tools shaping cybersecurity worldwide.
                            </motion.p>

                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.3 }}
                                className="flex flex-wrap gap-3"
                            >
                                {Object.entries(categoryConfig).map(([key, cat]) => {
                                    const Icon = cat.icon;
                                    return (
                                        <span
                                            key={key}
                                            className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium ${cat.badge}`}
                                        >
                                            <Icon className="h-3.5 w-3.5" />
                                            {cat.label}
                                        </span>
                                    );
                                })}
                            </motion.div>
                        </div>
                    </div>
                </div>
            </GlowyWavesBackground>

            {/* Featured article */}
            {featured && (
                <WaveSection variant="default">
                    <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.5 }}
                        >
                            {featured.content_type === 'video' && featured.video_url ? (
                                <div className="rounded-2xl border border-indigo-500/20 bg-gradient-to-br from-indigo-500/[0.07] via-purple-500/[0.04] to-transparent p-8 transition-all duration-300 sm:p-10">
                                    <div className="pointer-events-none absolute -top-24 -right-24 h-48 w-48 rounded-full bg-indigo-500/10 blur-3xl" />

                                    <div className="relative">
                                        <div className="flex items-center gap-3 mb-4">
                                            <span className="inline-flex items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-400">
                                                <Sparkles className="h-3 w-3" />
                                                Featured
                                            </span>
                                            <span className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ${categoryConfig[featured.category]?.badge ?? 'border-gray-500/20 bg-gray-500/10 text-gray-400'}`}>
                                                {categoryConfig[featured.category]?.label ?? featured.category}
                                            </span>
                                            <span className="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-400">
                                                <Play className="h-3 w-3" /> Video
                                            </span>
                                        </div>

                                        <h2 className="text-2xl font-bold tracking-tight text-white sm:text-3xl mb-4">
                                            {featured.title}
                                        </h2>

                                        <VideoPlayer videoUrl={featured.video_url} title={featured.title} />

                                        <p className="mt-4 max-w-2xl text-gray-400 leading-relaxed">
                                            {featured.excerpt}
                                        </p>

                                        <div className="mt-4 flex items-center gap-4 text-sm text-gray-500">
                                            <span>{timeAgo(featured.published_at)}</span>
                                            {featured.source_name && (
                                                <>
                                                    <span className="text-gray-700">&middot;</span>
                                                    <span className="inline-flex items-center gap-1">
                                                        <ExternalLink className="h-3 w-3" />
                                                        {featured.source_name}
                                                    </span>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ) : (
                                <Link
                                    href={`/news/${featured.slug}`}
                                    className="group block"
                                >
                                    <div className="relative overflow-hidden rounded-2xl border border-indigo-500/20 bg-gradient-to-br from-indigo-500/[0.07] via-purple-500/[0.04] to-transparent p-8 transition-all duration-300 hover:border-indigo-500/30 hover:shadow-lg hover:shadow-indigo-500/5 sm:p-10">
                                        <div className="pointer-events-none absolute -top-24 -right-24 h-48 w-48 rounded-full bg-indigo-500/10 blur-3xl" />

                                        <div className="relative">
                                            <div className="flex items-center gap-3 mb-4">
                                                <span className="inline-flex items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-400">
                                                    <Sparkles className="h-3 w-3" />
                                                    Featured
                                                </span>
                                                <span className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ${categoryConfig[featured.category]?.badge ?? 'border-gray-500/20 bg-gray-500/10 text-gray-400'}`}>
                                                    {categoryConfig[featured.category]?.label ?? featured.category}
                                                </span>
                                            </div>

                                            <h2 className="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                                {featured.title}
                                            </h2>

                                            <p className="mt-3 max-w-2xl text-gray-400 leading-relaxed">
                                                {featured.excerpt}
                                            </p>

                                            <div className="mt-6 flex items-center gap-4 text-sm text-gray-500">
                                                <span>{timeAgo(featured.published_at)}</span>
                                                {featured.source_name && (
                                                    <>
                                                        <span className="text-gray-700">&middot;</span>
                                                        <span className="inline-flex items-center gap-1">
                                                            <ExternalLink className="h-3 w-3" />
                                                            {featured.source_name}
                                                        </span>
                                                    </>
                                                )}
                                                <span className="ml-auto inline-flex items-center gap-1.5 text-indigo-400 opacity-0 transition-opacity group-hover:opacity-100">
                                                    Read full article
                                                    <ArrowRight className="h-4 w-4" />
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            )}
                        </motion.div>
                    </div>
                </WaveSection>
            )}

            {/* Article grid */}
            <WaveSection variant="default">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    {articles.length === 0 ? (
                        <div className="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-16 text-center">
                            <Newspaper className="mx-auto mb-4 h-10 w-10 text-gray-600" />
                            <p className="text-lg font-semibold text-white">No articles yet</p>
                            <p className="mt-1 text-sm text-gray-500">Check back soon for the latest cybersecurity news.</p>
                        </div>
                    ) : (
                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {articles.map((article, i) => {
                                if (article.content_type === 'video' && article.video_url) {
                                    return (
                                        <motion.div
                                            key={article.id}
                                            custom={i}
                                            initial="hidden"
                                            whileInView="visible"
                                            viewport={{ once: true }}
                                            variants={fadeUp}
                                        >
                                            <VideoCard article={article} />
                                        </motion.div>
                                    );
                                }

                                const cat = categoryConfig[article.category];
                                const Icon = cat?.icon ?? Newspaper;

                                return (
                                    <motion.div
                                        key={article.id}
                                        custom={i}
                                        initial="hidden"
                                        whileInView="visible"
                                        viewport={{ once: true }}
                                        variants={fadeUp}
                                    >
                                        <Link
                                            href={`/news/${article.slug}`}
                                            className="group block h-full"
                                        >
                                            <div className="flex h-full flex-col rounded-2xl border border-white/[0.06] bg-white/[0.02] p-6 transition-all duration-300 hover:border-white/[0.12] hover:bg-white/[0.04] hover:shadow-lg hover:shadow-black/20">
                                                {article.thumbnail_url && (
                                                    <div className="mb-4 overflow-hidden rounded-xl">
                                                        <img
                                                            src={article.thumbnail_url}
                                                            alt=""
                                                            className="h-40 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                        />
                                                    </div>
                                                )}

                                                <div className="flex items-center gap-2 mb-3">
                                                    <span className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ${cat?.badge ?? 'border-gray-500/20 bg-gray-500/10 text-gray-400'}`}>
                                                        <Icon className="mr-1 h-3 w-3" />
                                                        {cat?.label ?? article.category}
                                                    </span>
                                                </div>

                                                <h3 className="text-base font-semibold tracking-tight text-white line-clamp-2 break-words">
                                                    {article.title}
                                                </h3>

                                                <p className="mt-2 flex-1 text-sm text-gray-400 line-clamp-3">
                                                    {article.excerpt}
                                                </p>

                                                <div className="mt-4 flex items-center gap-3 text-xs text-gray-500">
                                                    <span>{timeAgo(article.published_at)}</span>
                                                    {article.source_name && (
                                                        <>
                                                            <span className="text-gray-700">&middot;</span>
                                                            <span className="truncate">{article.source_name}</span>
                                                        </>
                                                    )}
                                                </div>
                                            </div>
                                        </Link>
                                    </motion.div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </WaveSection>
        </PublicLayout>
    );
}
