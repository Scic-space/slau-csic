import { motion, type Variants } from "framer-motion";
import { ArrowRight, Shield } from "lucide-react";
import { useEffect, useRef, useState, type ReactNode } from "react";
import { Link } from "@inertiajs/react";

import { Button } from "@/components/ui/button";

type Point = { x: number; y: number };

interface WaveConfig {
  offset: number;
  amplitude: number;
  frequency: number;
  color: string;
  opacity: number;
}

const containerVariants: Variants = {
  hidden: { opacity: 0, y: 24 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.8, staggerChildren: 0.12 },
  },
};

const itemVariants: Variants = {
  hidden: { opacity: 0, y: 24 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.6, ease: "easeOut" },
  },
};

interface GlowyWavesProps {
  children?: ReactNode;
}

export function GlowyWavesBackground({ children }: GlowyWavesProps) {
  const containerRef = useRef<HTMLDivElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const mouseRef = useRef<Point>({ x: 0, y: 0 });
  const targetMouseRef = useRef<Point>({ x: 0, y: 0 });
  const [isVisible, setIsVisible] = useState(true);
  const visibleRef = useRef(true);

  useEffect(() => {
    const container = containerRef.current;
    if (!container) return;

    const intersectionObserver = new IntersectionObserver(
      ([entry]) => { setIsVisible(entry.isIntersecting); visibleRef.current = entry.isIntersecting; },
      { threshold: 0 },
    );
    intersectionObserver.observe(container);

    const canvas = canvasRef.current;
    if (!canvas) return () => intersectionObserver.disconnect();

    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    let animationId: number;
    let time = 0;

    const getCSVar = (name: string) =>
      getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    const isDark = () => document.documentElement.classList.contains('dark');

    const computeThemeColors = () => {
      if (isDark()) {
        return {
          backgroundTop: "#0f172a",
          backgroundBottom: "#0f172a",
          wavePalette: [
            { offset: 0, amplitude: 70, frequency: 0.003, color: "rgba(99, 102, 241, 0.45)", opacity: 0.45 },
            { offset: Math.PI / 2, amplitude: 90, frequency: 0.0026, color: "rgba(129, 140, 248, 0.35)", opacity: 0.35 },
            { offset: Math.PI, amplitude: 60, frequency: 0.0034, color: "rgba(79, 70, 229, 0.3)", opacity: 0.3 },
            { offset: Math.PI * 1.5, amplitude: 80, frequency: 0.0022, color: "rgba(99, 102, 241, 0.25)", opacity: 0.25 },
            { offset: Math.PI * 2, amplitude: 55, frequency: 0.004, color: "rgba(165, 180, 252, 0.2)", opacity: 0.2 },
          ] satisfies WaveConfig[],
        };
      }
      return {
        backgroundTop: "#f9fafb",
        backgroundBottom: "#f3f4f6",
        wavePalette: [
          { offset: 0, amplitude: 70, frequency: 0.003, color: "rgba(99, 102, 241, 0.15)", opacity: 0.35 },
          { offset: Math.PI / 2, amplitude: 90, frequency: 0.0026, color: "rgba(129, 140, 248, 0.12)", opacity: 0.3 },
          { offset: Math.PI, amplitude: 60, frequency: 0.0034, color: "rgba(79, 70, 229, 0.1)", opacity: 0.25 },
          { offset: Math.PI * 1.5, amplitude: 80, frequency: 0.0022, color: "rgba(99, 102, 241, 0.08)", opacity: 0.2 },
          { offset: Math.PI * 2, amplitude: 55, frequency: 0.004, color: "rgba(165, 180, 252, 0.06)", opacity: 0.15 },
        ] satisfies WaveConfig[],
      };
    };

    let themeColors = computeThemeColors();

    const handleThemeMutation = () => { themeColors = computeThemeColors(); };
    const observer = new MutationObserver(handleThemeMutation);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ["class", "data-theme"] });

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const mouseInfluence = prefersReducedMotion ? 10 : 70;
    const influenceRadius = prefersReducedMotion ? 160 : 320;
    const smoothing = prefersReducedMotion ? 0.04 : 0.1;

    const resizeCanvas = () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    };

    const recenterMouse = () => {
      const centerPoint = { x: canvas.width / 2, y: canvas.height / 2 };
      mouseRef.current = centerPoint;
      targetMouseRef.current = centerPoint;
    };

    const handleResize = () => { resizeCanvas(); recenterMouse(); };
    const handleMouseMove = (event: MouseEvent) => { targetMouseRef.current = { x: event.clientX, y: event.clientY }; };
    const handleMouseLeave = () => { recenterMouse(); };

    resizeCanvas();
    recenterMouse();

    window.addEventListener("resize", handleResize);
    window.addEventListener("mousemove", handleMouseMove);
    window.addEventListener("mouseleave", handleMouseLeave);

    const drawWave = (wave: WaveConfig) => {
      ctx.save();
      ctx.beginPath();

      for (let x = 0; x <= canvas.width; x += 4) {
        const dx = x - mouseRef.current.x;
        const dy = canvas.height / 2 - mouseRef.current.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        const influence = Math.max(0, 1 - distance / influenceRadius);
        const mouseEffect = influence * mouseInfluence * Math.sin(time * 0.001 + x * 0.01 + wave.offset);

        const y = canvas.height / 2
          + Math.sin(x * wave.frequency + time * 0.002 + wave.offset) * wave.amplitude
          + Math.sin(x * wave.frequency * 0.4 + time * 0.003) * (wave.amplitude * 0.45)
          + mouseEffect;

        x === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
      }

      ctx.lineWidth = 2.5;
      ctx.strokeStyle = wave.color;
      ctx.globalAlpha = wave.opacity;
      ctx.shadowBlur = 35;
      ctx.shadowColor = wave.color;
      ctx.stroke();
      ctx.restore();
    };

    const animate = () => {
      if (visibleRef.current) {
        time += 1;
        mouseRef.current.x += (targetMouseRef.current.x - mouseRef.current.x) * smoothing;
        mouseRef.current.y += (targetMouseRef.current.y - mouseRef.current.y) * smoothing;

        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
        gradient.addColorStop(0, themeColors.backgroundTop);
        gradient.addColorStop(1, themeColors.backgroundBottom);
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.globalAlpha = 1;
        ctx.shadowBlur = 0;
        themeColors.wavePalette.forEach(drawWave);
      }
      animationId = window.requestAnimationFrame(animate);
    };

    animationId = window.requestAnimationFrame(animate);

    return () => {
      window.removeEventListener("resize", handleResize);
      window.removeEventListener("mouseleave", handleMouseLeave);
      cancelAnimationFrame(animationId);
      observer.disconnect();
    };
  }, []);

  return (
    <div ref={containerRef} className="relative min-h-screen bg-gray-50 dark:bg-[#0f172a]">
      <canvas ref={canvasRef} className="fixed inset-0 h-full w-full" aria-hidden="true" />
      <div className="absolute inset-0 pointer-events-none overflow-hidden">
        <div className="absolute left-1/2 top-0 h-[300px] w-[300px] sm:h-[520px] sm:w-[520px] -translate-x-1/2 rounded-full bg-indigo-500/10 blur-[140px]" />
        <div className="absolute bottom-0 right-0 h-[200px] w-[200px] sm:h-[360px] sm:w-[360px] rounded-full bg-violet-500/10 blur-[120px]" />
        <div className="absolute top-1/2 left-1/4 h-[250px] w-[250px] sm:h-[400px] sm:w-[400px] rounded-full bg-indigo-500/10 blur-[150px]" />
      </div>
      <div className="relative z-10">
        {children}
      </div>
    </div>
  );
}

interface HeroContentProps {
  stats: { members: number; events: number; projects: number };
  upcomingEvents?: { start_date: string; title: string }[];
}

export function HeroContent({ stats, upcomingEvents }: HeroContentProps) {
  return (
    <section className="relative flex min-h-screen w-full items-center justify-center px-6 pt-20 pb-24 md:px-8 lg:px-12">
      <div className="mx-auto flex w-full max-w-6xl flex-col items-center text-center">
        <motion.div variants={containerVariants} initial="hidden" animate="visible" className="w-full">
          <motion.div variants={itemVariants} className="mb-4 inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-gray-500 dark:text-white/70 backdrop-blur">
            <Shield className="h-4 w-4 text-indigo-400" />
            {upcomingEvents && upcomingEvents.length > 0
              ? `Next: ${upcomingEvents[0].title} — ${new Date(upcomingEvents[0].start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
              : `${stats.members} Members and Growing`}
          </motion.div>

          <motion.div variants={itemVariants} className="mb-6 flex justify-center">
            <img
              src="/images/club_logo.png"
              alt="SLAU-CSIC"
              className="h-auto w-64 md:w-80 lg:w-96 invert brightness-[1.2] drop-shadow-[0_0_30px_rgba(99,102,241,0.4)]"
            />
          </motion.div>

          <motion.h1 variants={itemVariants} className="mb-6 text-4xl font-bold tracking-tight text-gray-900 dark:text-white md:text-6xl lg:text-7xl">
            Where Cybersecurity Meets{" "}
            <span className="bg-gradient-to-r from-indigo-400 via-indigo-300 to-violet-300 bg-clip-text text-transparent">
              Innovation
            </span>
          </motion.h1>

          <motion.p variants={itemVariants} className="mx-auto mb-10 max-w-3xl text-lg text-gray-600 dark:text-white/60 md:text-xl">
            A student-driven community exploring cybersecurity, building real-world projects,
            competing in CTFs, and developing skills that matter.
          </motion.p>

          <motion.div variants={itemVariants} className="mb-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <Link href="/auth/register">
              <Button size="lg" className="group gap-2 rounded-full px-8 text-base uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white">
                Join the Community
                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
              </Button>
            </Link>
            <a href="#upcoming-events">
              <Button size="lg" variant="outline" className="rounded-full border-gray-300 dark:border-white/20 bg-white dark:bg-white/5 px-8 text-base text-gray-700 dark:text-white/80 backdrop-blur transition-all hover:border-gray-400 dark:hover:border-white/40 hover:bg-gray-100 dark:hover:bg-white/10">
                View Upcoming Events
              </Button>
            </a>
          </motion.div>

          <motion.div variants={itemVariants} className="grid gap-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6 backdrop-blur-sm sm:grid-cols-3 max-w-2xl mx-auto">
            {[
              { label: "Active Members", value: stats.members },
              { label: "Events Hosted", value: stats.events },
              { label: "Projects Built", value: stats.projects },
            ].map((s) => (
              <motion.div key={s.label} variants={itemVariants} className="space-y-1">
                <div className="text-xs uppercase tracking-[0.3em] text-gray-500 dark:text-white/40">{s.label}</div>
                <div className="text-3xl font-bold text-gray-900 dark:text-white">{s.value}</div>
              </motion.div>
            ))}
          </motion.div>
        </motion.div>
      </div>
    </section>
  );
}

export interface SectionProps {
  children: ReactNode;
  className?: string;
  id?: string;
}

export function WaveSection({ children, className = "", id }: SectionProps) {
  return (
    <section id={id} className={`relative border-t border-gray-200 dark:border-white/10 px-6 py-24 md:px-8 lg:px-12 ${className}`}>
      <div className="mx-auto max-w-6xl">
        {children}
      </div>
    </section>
  );
}
