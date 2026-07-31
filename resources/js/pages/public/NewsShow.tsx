import PublicLayout from '@/components/PublicLayout';
import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    ArrowLeft,
    Newspaper,
    ShieldAlert,
    Bug,
    FileText,
    Building2,
    Wrench,
    Play,
    ExternalLink,
    Clock,
    User,
} from 'lucide-react';

interface Article {
    title: string;
    slug: string;
    excerpt: string;
    content: string;
    category: string;
    content_type: string;
    source_name: string | null;
    source_url: string | null;
    thumbnail_url: string | null;
    video_url: string | null;
    published_at: string;
    author: string;
    embed_url: string | null;
}

interface RelatedArticle {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    category: string;
    content_type: string;
    source_name: string | null;
    thumbnail_url: string | null;
    published_at: string;
}

interface NewsShowProps {
    article: Article;
    related: RelatedArticle[];
}

const categoryConfig: Record<string, { label: string; icon: React.ElementType; badge: string }> = {
    threat_intel: {
        label: 'Threat Intelligence',
        icon: ShieldAlert,
        badge: 'border-red-500/20 bg-red-500/10 text-red-400',
    },
    vulnerabilities: {
        label: 'Vulnerabilities',
        icon: Bug,
        badge: 'border-amber-500/20 bg-amber-500/10 text-amber-400',
    },
    policy_compliance: {
        label: 'Policy & Compliance',
        icon: FileText,
        badge: 'border-blue-500/20 bg-blue-500/10 text-blue-400',
    },
    industry: {
        label: 'Industry',
        icon: Building2,
        badge: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
    },
    tools_research: {
        label: 'Tools & Research',
        icon: Wrench,
        badge: 'border-purple-500/20 bg-purple-500/10 text-purple-400',
    },
};

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}

function VideoPlayer({ videoUrl, title, thumbnailUrl }: { videoUrl: string; title: string; thumbnailUrl?: string | null }) {
    const [playing, setPlaying] = useState(false);
    const isLocal = /\.(mp4|webm|ogg)$/i.test(videoUrl);

    if (playing) {
        return (
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.2 }}
                className="mb-10"
            >
                <div className="relative aspect-video overflow-hidden rounded-2xl border border-gray-200/50 dark:border-white/[0.06] bg-black">
                    {isLocal ? (
                        <video
                            src={videoUrl}
                            controls
                            playsInline
                            autoPlay
                            preload="metadata"
                            className="absolute inset-0 h-full w-full"
                        >
                            Your browser does not support the video tag.
                        </video>
                    ) : (
                        <iframe
                            src={videoUrl.includes('?') ? `${videoUrl}&autoplay=1` : `${videoUrl}?autoplay=1`}
                            title={title}
                            className="absolute inset-0 h-full w-full"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowFullScreen
                            referrerPolicy="no-referrer-when-downgrade"
                        />
                    )}
                </div>
            </motion.div>
        );
    }

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
            className="mb-10"
        >
            <button
                type="button"
                onClick={() => setPlaying(true)}
                className="group relative aspect-video w-full overflow-hidden rounded-2xl border border-gray-200/50 dark:border-white/[0.06] bg-black"
            >
                {thumbnailUrl ? (
                    <img
                        src={thumbnailUrl}
                        alt={title}
                        className="absolute inset-0 h-full w-full object-cover opacity-80 transition-opacity group-hover:opacity-100"
                    />
                ) : (
                    <div className="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-purple-500/20" />
                )}
                <div className="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors group-hover:bg-black/20">
                    <div className="flex h-20 w-20 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm transition-all group-hover:scale-110 group-hover:bg-white/30">
                        <Play className="h-9 w-9 text-white fill-white" />
                    </div>
                </div>
            </button>
        </motion.div>
    );
}

export default function NewsShow({ article, related }: NewsShowProps) {
    const cat = categoryConfig[article.category];
    const Icon = cat?.icon ?? Newspaper;

    return (
        <PublicLayout>
            <div className="min-h-screen bg-gradient-to-b from-gray-50 via-white to-gray-50 dark:from-[#0a0a0f] dark:via-[#07070b] dark:to-[#0a0a0f]">
                {/* Hero */}
                <div className="relative overflow-hidden border-b border-gray-200/50 dark:border-white/5">
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-20%,rgba(120,119,198,0.08),transparent)] dark:bg-[radial-gradient(ellipse_80%_50%_at_50%_-20%,rgba(120,119,198,0.15),transparent)]" />
                    <div className="relative mx-auto max-w-3xl px-4 pt-24 pb-12 sm:px-6 lg:px-8">
                        <Link
                            href="/news"
                            className="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Back to News
                        </Link>

                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.5 }}
                        >
                            <div className="flex items-center gap-3 mb-4">
                                <span className={`inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium ${cat?.badge ?? 'border-gray-500/20 bg-gray-500/10 text-gray-400'}`}>
                                    <Icon className="mr-1 h-3.5 w-3.5" />
                                    {cat?.label ?? article.category}
                                </span>
                                {article.content_type === 'video' && (
                                    <span className="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">
                                        <Play className="h-3.5 w-3.5" /> Video
                                    </span>
                                )}
                            </div>

                            <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                                {article.title}
                            </h1>

                            <div className="mt-4 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                <span className="inline-flex items-center gap-1.5">
                                    <User className="h-4 w-4" />
                                    {article.author}
                                </span>
                                <span className="text-gray-300 dark:text-gray-700">&middot;</span>
                                <span className="inline-flex items-center gap-1.5">
                                    <Clock className="h-4 w-4" />
                                    {formatDate(article.published_at)}
                                </span>
                                {article.source_name && (
                                    <>
                                        <span className="text-gray-300 dark:text-gray-700">&middot;</span>
                                        {article.source_url ? (
                                            <a
                                                href={article.source_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-400 dark:text-indigo-400"
                                            >
                                                <ExternalLink className="h-3.5 w-3.5" />
                                                {article.source_name}
                                            </a>
                                        ) : (
                                            <span>{article.source_name}</span>
                                        )}
                                    </>
                                )}
                            </div>
                        </motion.div>
                    </div>
                </div>

                {/* Content */}
                <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
                    {/* Video embed */}
                    {article.content_type === 'video' && article.video_url && (
                        <VideoPlayer videoUrl={article.video_url} title={article.title} thumbnailUrl={article.thumbnail_url} />
                    )}

                    {/* Featured image (non-video) */}
                    {article.content_type !== 'video' && article.thumbnail_url && (
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.5, delay: 0.2 }}
                            className="mb-10 overflow-hidden rounded-2xl border border-gray-200/50 dark:border-white/[0.06]"
                        >
                            <img
                                src={article.thumbnail_url}
                                alt=""
                                className="h-auto w-full object-cover"
                            />
                        </motion.div>
                    )}

                    {/* Article body */}
                    <motion.article
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ duration: 0.5, delay: 0.3 }}
                        className="prose prose-gray dark:prose-invert max-w-none prose-headings:font-semibold prose-a:text-indigo-600 dark:prose-a:text-indigo-400 prose-img:rounded-2xl"
                        dangerouslySetInnerHTML={{ __html: article.content }}
                    />

                    {/* Source link at bottom */}
                    {article.source_url && (
                        <div className="mt-10 rounded-2xl border border-gray-200/50 bg-gray-50/50 p-5 dark:border-white/[0.06] dark:bg-white/[0.01]">
                            <p className="text-sm text-gray-500 dark:text-gray-500">
                                Originally published{' '}
                                {article.source_name && <>by <strong>{article.source_name}</strong>{' '}</>}
                                at{' '}
                                <a
                                    href={article.source_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-indigo-500 hover:text-indigo-400 dark:text-indigo-400"
                                >
                                    {article.source_url}
                                    <ExternalLink className="ml-1 inline h-3 w-3" />
                                </a>
                            </p>
                        </div>
                    )}

                    {/* Related articles */}
                    {related.length > 0 && (
                        <div className="mt-14">
                            <div className="h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent dark:via-white/[0.06] mb-10" />
                            <h2 className="text-xl font-bold tracking-tight text-gray-900 dark:text-white mb-6">
                                Related in {cat?.label ?? 'this category'}
                            </h2>
                            <div className="grid gap-4 sm:grid-cols-3">
                                {related.map((item) => {
                                    const relCat = categoryConfig[item.category];
                                    const RelIcon = relCat?.icon ?? Newspaper;

                                    return (
                                        <Link
                                            key={item.id}
                                            href={`/news/${item.slug}`}
                                            className="group block rounded-2xl border border-white/[0.06] bg-white/[0.02] p-5 transition-all duration-300 hover:border-white/[0.12] hover:bg-white/[0.04]"
                                        >
                                            <span className={`inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium ${relCat?.badge ?? 'border-gray-500/20 bg-gray-500/10 text-gray-400'}`}>
                                                <RelIcon className="mr-1 h-3 w-3" />
                                                {relCat?.label ?? item.category}
                                            </span>
                                            <h3 className="mt-2 text-sm font-semibold tracking-tight text-white line-clamp-2">
                                                {item.title}
                                            </h3>
                                            <p className="mt-1 text-xs text-gray-500">
                                                {formatDate(item.published_at)}
                                            </p>
                                        </Link>
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </PublicLayout>
    );
}
