import { useEffect, useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { usePage } from "@inertiajs/react"
import { cn } from "@/lib/utils"
import { Menu, X } from "lucide-react"

interface NavItem {
  name: string
  url: string
}

interface NavBarProps {
  items: NavItem[]
  className?: string
}

function NavLabel({ name }: { name: string }) {
  return (
    <span className="relative grid" aria-label={name}>
      <span className="invisible col-start-1 row-start-1 uppercase" aria-hidden="true">{name}</span>
      <span className="col-start-1 row-start-1 transition-opacity duration-200 group-hover:opacity-0" aria-hidden="true">{name}</span>
      <span className="col-start-1 row-start-1 uppercase opacity-0 transition-opacity duration-200 group-hover:opacity-100" aria-hidden="true">{name}</span>
    </span>
  )
}

export function NavBar({ items, className }: NavBarProps) {
  const { url } = usePage()
  const [activeTab, setActiveTab] = useState(items[0]?.name ?? "")
  const [mobileOpen, setMobileOpen] = useState(false)

  useEffect(() => {
    const active = items.find((item) => 
      item.url === "/" ? url === "/" : url.startsWith(item.url)
    )
    if (active) {
      setActiveTab(active.name)
    }
  }, [url, items])

  useEffect(() => {
    setMobileOpen(false)
  }, [url])

  return (
    <>
      {/* Desktop pill nav */}
      <div className={cn("hidden lg:flex items-center", className)}>
        <div className="flex items-center gap-1 xl:gap-2">
          {items.map((item) => {
            const isActive = activeTab === item.name

            return (
              <a
                key={item.name}
                href={item.url}
                onClick={() => setActiveTab(item.name)}
                className={cn(
                  "group relative flex min-h-10 items-center cursor-pointer rounded-sm px-2.5 py-2 text-sm font-medium transition-colors duration-200 xl:px-3",
                  "text-foreground/80 hover:text-primary",
                  isActive && "bg-primary/10 text-primary",
                  (item.name === "Sign In" || item.name === "Join Us") && "hidden",
                )}
              >
                <NavLabel name={item.name} />
                {isActive && (
                  <motion.div
                    layoutId="lamp"
                    className="absolute inset-0 w-full bg-primary/5 rounded-full -z-10"
                    initial={false}
                    transition={{
                      type: "spring",
                      stiffness: 300,
                      damping: 30,
                    }}
                  >
                    <div className="absolute -top-2 left-1/2 -translate-x-1/2 w-8 h-1 bg-primary rounded-t-full">
                      <div className="absolute w-12 h-6 bg-primary/20 rounded-full blur-md -top-2 -left-2" />
                      <div className="absolute w-8 h-6 bg-primary/20 rounded-full blur-md -top-1" />
                      <div className="absolute w-4 h-4 bg-primary/20 rounded-full blur-sm top-0 left-2" />
                    </div>
                  </motion.div>
                )}
              </a>
            )
          })}
        </div>
      </div>

      {/* Mobile hamburger */}
      <div className="relative justify-self-end lg:hidden">
        <button
          onClick={() => setMobileOpen(!mobileOpen)}
          className="flex h-10 w-10 items-center justify-center rounded-sm border border-border bg-background text-foreground/80 transition-colors hover:text-foreground"
          aria-label={mobileOpen ? "Close menu" : "Open menu"}
        >
          {mobileOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
        </button>

        <AnimatePresence>
          {mobileOpen && (
            <>
              <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                className="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
                onClick={() => setMobileOpen(false)}
              />
              <motion.div
                initial={{ opacity: 0, y: -12, scale: 0.95 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                exit={{ opacity: 0, y: -12, scale: 0.95 }}
                transition={{ duration: 0.15, ease: "easeOut" }}
                className="fixed inset-x-4 top-16 z-50 rounded-sm border border-border bg-card p-3 shadow-theme-xl sm:left-auto sm:right-6 sm:w-72"
              >
                <nav className="flex flex-col gap-1">
                  {items.map((item) => {
                    const isActive = activeTab === item.name
                    return (
                      <a
                        key={item.name}
                        href={item.url}
                        onClick={() => {
                          setActiveTab(item.name)
                          setMobileOpen(false)
                        }}
                        className={cn(
                          "group flex min-h-11 items-center rounded-xl px-4 py-2.5 text-sm font-medium transition-colors",
                          isActive
                            ? "bg-primary/10 text-primary"
                            : "text-foreground/70 hover:text-foreground hover:bg-accent/50"
                        )}
                      >
                        <NavLabel name={item.name} />
                      </a>
                    )
                  })}
                </nav>
              </motion.div>
            </>
          )}
        </AnimatePresence>
      </div>
    </>
  )
}
