import PublicLayout from '@/components/PublicLayout';
import {
    GlowyWavesBackground,
    WaveSection,
} from '@/components/ui/glowy-waves-hero-shadcnui';
import { motion } from 'framer-motion';
import { Link, router } from '@inertiajs/react';
import {
    Trophy,
    Flame,
    Medal,
    Crown,
    Shield,
    Zap,
    Star,
    TrendingUp,
} from 'lucide-react';

interface Leader {
    user_id: number;
    name: string;
    headline: string | null;
    profile_photo_url: string;
    total_points: number;
    rank_tier: string;
    rank: number;
    badges: { name: string; icon: string }[];
    badge_count: number;
    events_attended: number;
    streak: number;
}

interface CurrentUserRank {
    rank: number;
    points: number;
    rank_tier: string;
    points_to_next: number | null;
    next_rank: string | null;
}

interface LeaderboardProps {
    leaders: Leader[];
    currentUserRank: CurrentUserRank | null;
    totalMembers: number;
    rankThresholds: Record<string, number>;
    period: string;
}

const tierConfig: Record<string, { label: string; color: string; badge: string; icon: React.ElementType }> = {
    platinum: { label: 'Platinum', color: 'text-purple-400', badge: 'border-purple-500/20 bg-purple-500/10 text-purple-400', icon: Crown },
    gold: { label: 'Gold', color: 'text-yellow-400', badge: 'border-yellow-500/20 bg-yellow-500/10 text-yellow-400', icon: Star },
    silver: { label: 'Silver', color: 'text-gray-300', badge: 'border-gray-400/20 bg-gray-400/10 text-gray-300', icon: Medal },
    bronze: { label: 'Bronze', color: 'text-orange-400', badge: 'border-orange-500/20 bg-orange-500/10 text-orange-400', icon: Shield },
};

const periodTabs = [
    { value: 'all-time', label: 'All Time' },
    { value: 'month', label: 'This Month' },
    { value: 'week', label: 'This Week' },
    { value: 'semester', label: 'This Semester' },
];

const fadeUp = {
    hidden: { opacity: 0, y: 20 },
    visible: (i: number) => ({
        opacity: 1,
        y: 0,
        transition: { duration: 0.4, delay: i * 0.06, ease: 'easeOut' },
    }),
};

const podiumPositions = [
    { position: 2, order: 'order-1', extra: '' },
    { position: 1, order: 'order-2', extra: 'sm:-mt-2' },
    { position: 3, order: 'order-3', extra: '' },
];

const podiumStyles: Record<number, { card: string; ring: string; crown: boolean }> = {
    1: { card: 'border-yellow-500/30 bg-yellow-500/[0.07]', ring: 'ring-yellow-400 dark:ring-yellow-500', crown: true },
    2: { card: 'border-gray-400/20 bg-white/[0.03]', ring: 'ring-gray-300 dark:ring-gray-500', crown: false },
    3: { card: 'border-orange-500/20 bg-orange-500/[0.05]', ring: 'ring-orange-300 dark:ring-orange-500', crown: false },
};

export default function Leaderboard({ leaders, currentUserRank, totalMembers, rankThresholds, period }: LeaderboardProps) {
    const podium = leaders.slice(0, 3);
    const rest = leaders.slice(3);

    function switchPeriod(value: string) {
        router.get('/leaderboard', { period: value }, { preserveState: true, replace: true });
    }

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
                                <Trophy className="h-4 w-4 text-yellow-400" />
                                Leaderboard
                            </motion.div>

                            <motion.h1
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.1 }}
                                className="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight"
                            >
                                Top{' '}
                                <span className="bg-gradient-to-r from-yellow-400 via-amber-400 to-orange-400 bg-clip-text text-transparent">
                                    Performers
                                </span>
                            </motion.h1>

                            <motion.p
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.2 }}
                                className="text-lg text-white/50 mb-8 max-w-2xl leading-relaxed"
                            >
                                Ranked by points earned across events, competitions, CTFs, and club participation.
                            </motion.p>

                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.3 }}
                                className="flex flex-wrap gap-3"
                            >
                                {Object.entries(tierConfig).map(([key, tier]) => {
                                    const Icon = tier.icon;
                                    return (
                                        <span
                                            key={key}
                                            className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium ${tier.badge}`}
                                        >
                                            <Icon className="h-3.5 w-3.5" />
                                            {tier.label}
                                        </span>
                                    );
                                })}
                            </motion.div>
                        </div>
                    </div>
                </div>
            </GlowyWavesBackground>

            {/* Your Rank */}
            {currentUserRank && (
                <WaveSection variant="default">
                    <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.5 }}
                            className="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] p-6"
                        >
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10">
                                        <Zap className="h-5 w-5 text-emerald-400" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-white">
                                            You're ranked <strong>#{currentUserRank.rank}</strong> of {totalMembers.toLocaleString()} members
                                        </p>
                                        <p className="text-xs text-gray-400">
                                            {currentUserRank.points.toLocaleString()} points
                                            {currentUserRank.points_to_next !== null && (
                                                <> &middot; {currentUserRank.points_to_next.toLocaleString()} pts to {currentUserRank.next_rank ? tierConfig[currentUserRank.next_rank]?.label ?? currentUserRank.next_rank : ''}</>
                                            )}
                                        </p>
                                    </div>
                                </div>
                                {currentUserRank.points_to_next !== null && currentUserRank.next_rank && (
                                    <div className="w-full sm:w-48">
                                        <div className="h-2 overflow-hidden rounded-full bg-emerald-500/20">
                                            <div
                                                className="h-full rounded-full bg-emerald-500 transition-all duration-500"
                                                style={{
                                                    width: `${(() => {
                                                        const nextThreshold = rankThresholds[currentUserRank.next_rank] ?? 0;
                                                        const currentThreshold = Object.values(rankThresholds).find((t) => t <= currentUserRank.points) ?? 0;
                                                        const range = nextThreshold - currentThreshold;
                                                        return range > 0 ? Math.min(100, ((currentUserRank.points - currentThreshold) / range) * 100) : 100;
                                                    })()}%`,
                                                }}
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>
                        </motion.div>
                    </div>
                </WaveSection>
            )}

            {/* Period Tabs */}
            <WaveSection variant="default">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-8 flex gap-1 rounded-xl bg-white/[0.03] p-1 border border-white/[0.06]">
                        {periodTabs.map((tab) => (
                            <button
                                key={tab.value}
                                onClick={() => switchPeriod(tab.value)}
                                className={`flex-1 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors ${
                                    period === tab.value
                                        ? 'bg-white/[0.08] text-white shadow-sm'
                                        : 'text-gray-500 hover:text-gray-300'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    {leaders.length === 0 ? (
                        <div className="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-16 text-center">
                            <Trophy className="mx-auto mb-4 h-10 w-10 text-gray-600" />
                            <p className="text-lg font-semibold text-white">No leaderboard data yet</p>
                            <p className="mt-1 text-sm text-gray-500">Start attending events and earning points to appear here.</p>
                        </div>
                    ) : (
                        <>
                            {/* Podium */}
                            {podium.length > 0 && (
                                <div className="mb-10 grid grid-cols-3 gap-3 sm:gap-4">
                                    {podiumPositions.map(({ position, order, extra }) => {
                                        const entry = podium.find((l) => l.rank === position);
                                        if (!entry) return null;

                                        const style = podiumStyles[position];
                                        const tier = tierConfig[entry.rank_tier] ?? tierConfig.bronze;

                                        return (
                                            <motion.div
                                                key={entry.user_id}
                                                custom={position === 1 ? 0 : position}
                                                initial="hidden"
                                                whileInView="visible"
                                                viewport={{ once: true }}
                                                variants={fadeUp}
                                                className={`${order} ${extra} relative rounded-2xl border ${style.card} p-4 text-center ${position === 1 ? 'sm:p-6' : 'sm:p-5'}`}
                                            >
                                                {style.crown && (
                                                    <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                                                        <Crown className="h-6 w-6 text-yellow-400" />
                                                    </div>
                                                )}

                                                <div className="mt-2 flex flex-col items-center">
                                                    <div className="relative mb-3">
                                                        <img
                                                            src={entry.profile_photo_url}
                                                            alt={entry.name}
                                                            className={`rounded-full object-cover ring-2 ${style.ring} ${
                                                                position === 1 ? 'h-16 w-16 sm:h-20 sm:w-20' : 'h-14 w-14 sm:h-16 sm:w-16'
                                                            }`}
                                                        />
                                                        <span className="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-white/[0.1] text-xs font-bold text-white backdrop-blur-sm">
                                                            {position}
                                                        </span>
                                                    </div>

                                                    <p className="max-w-[120px] truncate text-sm font-semibold text-white sm:max-w-none">
                                                        {entry.name}
                                                    </p>

                                                    {entry.headline && (
                                                        <p className="mt-0.5 hidden text-xs text-gray-500 sm:block">
                                                            {entry.headline.length > 25 ? entry.headline.slice(0, 25) + '...' : entry.headline}
                                                        </p>
                                                    )}

                                                    <span className={`mt-2 inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-medium ${tier.badge}`}>
                                                        {tier.label}
                                                    </span>

                                                    <p className="mt-2 text-xl font-bold text-white">
                                                        {entry.total_points.toLocaleString()}
                                                    </p>
                                                    <p className="text-[10px] uppercase tracking-wide text-gray-500">points</p>

                                                    {entry.badges.length > 0 && (
                                                        <div className="mt-2 flex items-center justify-center gap-0.5">
                                                            {entry.badges.map((badge, bi) => (
                                                                <span key={bi} className="text-base" title={badge.name}>
                                                                    {badge.icon}
                                                                </span>
                                                            ))}
                                                            {entry.badge_count > 3 && (
                                                                <span className="text-[10px] text-gray-500">+{entry.badge_count - 3}</span>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            </motion.div>
                                        );
                                    })}
                                </div>
                            )}

                            {/* Full Table */}
                            {rest.length > 0 && (
                                <div className="overflow-hidden rounded-2xl border border-white/[0.06] bg-white/[0.02]">
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full">
                                            <thead>
                                                <tr className="border-b border-white/[0.06]">
                                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">Rank</th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">Member</th>
                                                    <th className="hidden px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 sm:table-cell sm:px-6">Badges</th>
                                                    <th className="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">Events</th>
                                                    <th className="hidden px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 sm:table-cell sm:px-6">Streak</th>
                                                    <th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">Points</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-white/[0.04]">
                                                {rest.map((member, i) => (
                                                    <motion.tr
                                                        key={member.user_id}
                                                        custom={i}
                                                        initial="hidden"
                                                        whileInView="visible"
                                                        viewport={{ once: true }}
                                                        variants={fadeUp}
                                                        className="transition hover:bg-white/[0.03]"
                                                    >
                                                        <td className="whitespace-nowrap px-4 py-3 sm:px-6">
                                                            <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.06] text-xs font-semibold text-gray-400">
                                                                {member.rank}
                                                            </span>
                                                        </td>
                                                        <td className="whitespace-nowrap px-4 py-3 sm:px-6">
                                                            <div className="flex items-center gap-3">
                                                                <img
                                                                    src={member.profile_photo_url}
                                                                    alt=""
                                                                    className="h-9 w-9 rounded-full object-cover"
                                                                />
                                                                <div className="min-w-0">
                                                                    <p className="text-sm font-medium text-white">{member.name}</p>
                                                                    {member.headline && (
                                                                        <p className="truncate text-xs text-gray-500">
                                                                            {member.headline.length > 35 ? member.headline.slice(0, 35) + '...' : member.headline}
                                                                        </p>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="hidden whitespace-nowrap px-4 py-3 text-center sm:table-cell sm:px-6">
                                                            {member.badges.length > 0 ? (
                                                                <div className="inline-flex items-center gap-0.5">
                                                                    {member.badges.map((badge, bi) => (
                                                                        <span key={bi} className="text-sm" title={badge.name}>
                                                                            {badge.icon}
                                                                        </span>
                                                                    ))}
                                                                    {member.badge_count > 3 && (
                                                                        <span className="ml-0.5 text-[10px] text-gray-500">+{member.badge_count - 3}</span>
                                                                    )}
                                                                </div>
                                                            ) : (
                                                                <span className="text-xs text-gray-600">&mdash;</span>
                                                            )}
                                                        </td>
                                                        <td className="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-400 sm:px-6">
                                                            {member.events_attended}
                                                        </td>
                                                        <td className="hidden whitespace-nowrap px-4 py-3 text-center sm:table-cell sm:px-6">
                                                            {member.streak > 0 ? (
                                                                <span className="inline-flex items-center gap-1 text-sm text-orange-400">
                                                                    <Flame className="h-3.5 w-3.5" />
                                                                    {member.streak}
                                                                </span>
                                                            ) : (
                                                                <span className="text-sm text-gray-600">&mdash;</span>
                                                            )}
                                                        </td>
                                                        <td className="whitespace-nowrap px-4 py-3 text-right sm:px-6">
                                                            <span className="text-sm font-semibold text-white">
                                                                {member.total_points.toLocaleString()}
                                                            </span>
                                                        </td>
                                                    </motion.tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </WaveSection>
        </PublicLayout>
    );
}
