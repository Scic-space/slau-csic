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
      <div className={cn("hidden md:flex items-center", className)}>
        <div className="flex items-center gap-1 bg-background/5 border border-border backdrop-blur-lg py-1 px-1 rounded-full shadow-lg">
          {items.map((item) => {
            const isActive = activeTab === item.name

            return (
              <a
                key={item.name}
                href={item.url}
                onClick={() => setActiveTab(item.name)}
                className={cn(
                  "relative cursor-pointer text-sm font-semibold px-6 py-2 rounded-full transition-colors",
                  "text-foreground/80 hover:text-primary",
                  isActive && "bg-muted text-primary",
                )}
              >
                <span>{item.name}</span>
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
      <div className="relative md:hidden">
        <button
          onClick={() => setMobileOpen(!mobileOpen)}
          className="flex items-center justify-center w-10 h-10 rounded-full bg-background/5 border border-border backdrop-blur-lg text-foreground/80 hover:text-foreground transition-colors"
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
                className="absolute left-1/2 -translate-x-1/2 top-full mt-3 z-50 w-64 rounded-2xl border border-border bg-background/95 backdrop-blur-xl p-2 shadow-2xl"
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
                          "rounded-xl px-4 py-2.5 text-sm font-medium transition-colors",
                          isActive
                            ? "bg-primary/10 text-primary"
                            : "text-foreground/70 hover:text-foreground hover:bg-accent/50"
                        )}
                      >
                        {item.name}
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
