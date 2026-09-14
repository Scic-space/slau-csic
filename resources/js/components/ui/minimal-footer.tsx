import React from "react";
import { usePage } from "@inertiajs/react";
import { Heart, Mail, MapPin, ArrowUp } from "lucide-react";
import { ThemeToggle } from "@/components/ui/theme-toggle";

const GithubIcon = ({ className }: { className?: string }) => (
  <svg viewBox="0 0 24 24" fill="currentColor" className={className}>
    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
  </svg>
);

const TikTokIcon = ({ className }: { className?: string }) => (
  <svg viewBox="0 0 24 24" fill="currentColor" className={className} aria-hidden="true">
    <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 1 1-2-2.75V9.4a6.34 6.34 0 1 0 5.45 6.27V8.74a8.16 8.16 0 0 0 4.77 1.52V6.82c-.34 0-.67-.04-1-.13Z" />
  </svg>
);

const footerLinks = {
  engage: [
    { name: "CTF Arena", href: "/ctf-arena" },
    { name: "Leaderboard", href: "/leaderboard" },
  ],
  learn: [
    { name: "Workshops", href: "/workshops" },
    { name: "News", href: "/news" },
    { name: "Projects", href: "/projects" },
  ],
  club: [
    { name: "About Us", href: "/about" },
    { name: "Our Team", href: "/team" },
    { name: "Contact", href: "/contact" },
  ],
};

const socialLinks = [
  { icon: GithubIcon, label: "GitHub", href: "https://github.com/Scic-space" },
  { icon: TikTokIcon, label: "Visit SCIC Cyber on TikTok", href: "https://tiktok.com/@slaucyberclub" },
];

export function MinimalFooter() {
  const { auth } = usePage<{ auth?: { user?: { id: number } | null } }>().props;
  const baseUrl = auth?.user ? "/dashboard" : "/";
  const year = new Date().getFullYear();

  return (
    <footer className="relative border-t border-border bg-card">
      {/* Subtle top glow */}
      <div className="pointer-events-none absolute inset-x-0 -top-px h-px bg-gradient-to-r from-transparent via-indigo-500/40 to-transparent" />

      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {/* Main footer content */}
        <div className="grid grid-cols-1 gap-10 py-12 sm:py-16 md:grid-cols-2 lg:grid-cols-5 lg:gap-8">
          {/* Brand */}
          <div className="lg:col-span-2">
            <a href={baseUrl} className="inline-flex items-center space-x-2.5">
              <img
                src="/images/club_logo.png"
                alt="SCIC"
                className="h-9 w-auto dark:brightness-0 dark:invert"
              />
              <span className="text-lg font-bold text-foreground tracking-tight">SCIC</span>
            </a>
            <p className="mt-4 max-w-xs text-sm leading-relaxed text-muted-foreground">
              Cybersecurity &amp; Innovations Club at St. Lawrence University — building skills,
              solving challenges, and shaping East Africa&apos;s tech identity.
            </p>

            {/* Contact */}
            <ul className="mt-6 space-y-3">
              <li>
                <a
                  href="mailto:sciccyber8@gmail.com"
                  className="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-indigo-600 dark:hover:text-indigo-400"
                >
                  <Mail className="h-4 w-4 text-indigo-500/70 dark:text-indigo-400/60" />
                  sciccyber8@gmail.com
                </a>
              </li>
              <li className="flex items-center gap-2 text-sm text-muted-foreground">
                <MapPin className="h-4 w-4 text-indigo-500/70 dark:text-indigo-400/60" />
                St. Lawrence University, Uganda
              </li>
            </ul>

            {/* Social */}
            <div className="mt-6 flex items-center gap-3">
              {socialLinks.map(({ icon: Icon, label, href }) => (
                <a
                  key={label}
                  href={href}
                  aria-label={label}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-transparent text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-500/30 hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400"
                >
                  <Icon className="h-4 w-4" />
                </a>
              ))}
            </div>
          </div>

          {/* Link columns */}
          {Object.entries(footerLinks).map(([category, links]) => (
            <div key={category}>
              <h4 className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                {category}
              </h4>
              <ul className="mt-4 space-y-2.5">
                {links.map((link) => (
                  <li key={link.name}>
                    <a
                      href={link.href}
                      className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                      {link.name}
                    </a>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        {/* Bottom bar */}
        <div className="flex flex-col items-center justify-between gap-4 border-t border-border py-6 sm:flex-row">
          <p className="flex flex-wrap items-center justify-center gap-1.5 text-xs text-muted-foreground sm:justify-start">
            &copy; {year} SLAU-CSIC. All rights reserved — sciccyber8@gmail.com
            <Heart className="ml-1 h-3 w-3 text-red-500/70 dark:text-red-400/60" />
          </p>

          <div className="flex items-center gap-3">
            <ThemeToggle size="sm" />
            <button
              onClick={() => window.scrollTo({ top: 0, behavior: "smooth" })}
              className="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-transparent text-muted-foreground transition-all hover:border-primary/40 hover:text-foreground"
              aria-label="Scroll to top"
            >
              <ArrowUp className="h-3.5 w-3.5" />
            </button>
          </div>
        </div>
      </div>
    </footer>
  );
}
