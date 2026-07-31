import React from 'react';
import { ArrowRight } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { GlowCard } from '@/components/ui/spotlight-card';

interface PillarCardProps {
    icon: React.ComponentType<{ className?: string }>;
    title: string;
    description: string;
    tags: string[];
    frequency: string;
    statValue?: string;
    statLabel?: string;
    accentColor: string;
    accentBg: string;
    accentBorder: string;
    glowColor: 'blue' | 'purple' | 'green' | 'orange';
    href: string;
}

function PillarCard({
    icon: Icon,
    title,
    description,
    tags,
    frequency,
    statValue,
    statLabel,
    accentColor,
    accentBg,
    accentBorder,
    glowColor,
    href,
}: PillarCardProps) {
    return (
        <GlowCard glowColor={glowColor} customSize className="!p-0 !bg-transparent !border-0 !backdrop-blur-none !shadow-none !gap-0 h-full rounded-2xl">
            <Card className="h-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] backdrop-blur-sm rounded-2xl overflow-hidden flex flex-col">
                <CardContent className="flex-1 flex flex-col gap-0 pt-5">
                    <div className="flex items-start justify-between mb-3">
                        <div className={`rounded-xl p-2.5 ${accentBg} ${accentBorder} border`}>
                            <Icon className={`h-5 w-5 ${accentColor}`} />
                        </div>
                        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider ${accentBg} ${accentColor} ${accentBorder} border`}>
                            {frequency}
                        </span>
                    </div>

                    <h3 className="text-base font-semibold text-gray-900 dark:text-white mb-1 leading-snug">{title}</h3>
                    <p className="text-sm text-gray-500 dark:text-white/50 leading-relaxed line-clamp-3">{description}</p>

                    <div className="flex flex-wrap gap-1.5 mt-3">
                        {tags.map((tag) => (
                            <span
                                key={tag}
                                className="rounded-full px-2.5 py-0.5 text-[11px] font-medium bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-500 dark:text-white/50"
                            >
                                #{tag}
                            </span>
                        ))}
                    </div>

                    {statValue && statLabel && (
                        <div className="flex items-center gap-2.5 mt-3 pt-3 border-t border-gray-200 dark:border-white/5">
                            <span className={`text-xl font-bold tracking-tight ${accentColor}`}>{statValue}</span>
                            <span className="text-[11px] text-gray-500 dark:text-white/40 leading-tight">{statLabel}</span>
                        </div>
                    )}
                </CardContent>

                <CardFooter className="border-0 pt-1 pb-4">
                    <Button
                        variant="ghost"
                        className={`group gap-2 px-0 hover:bg-transparent ${accentColor}`}
                        asChild
                    >
                        <Link href={href}>
                            Explore
                            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </Link>
                    </Button>
                </CardFooter>
            </Card>
        </GlowCard>
    );
}

export { PillarCard }
