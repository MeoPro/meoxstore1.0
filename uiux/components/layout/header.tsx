"use client";

import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { setSidebarOpen, toggleCollapse } from "@/store/slices/sidebar-slice";
import { cn } from "@/lib/utils";
import {
  Bell,
  Maximize2,
  Menu,
  Moon,
  PanelLeftClose,
  PanelLeftOpen,
  Search,
  Sun,
} from "lucide-react";
import { UserMenu } from "./user-menu";
import { useTheme } from "next-themes";
import { useEffect, useState } from "react";

export function Header() {
  const dispatch = useAppDispatch();
  const { isCollapsed } = useAppSelector((state) => state.sidebar);
  const { resolvedTheme, setTheme } = useTheme();
  const [searchOpen, setSearchOpen] = useState(false);

  useEffect(() => {
    const onKeyDown = (e: KeyboardEvent) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k") {
        e.preventDefault();
        setSearchOpen(true);
      }
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, []);

  const actionButton =
    "flex h-9 w-9 items-center justify-center rounded-lg border border-border/80 bg-background text-muted-foreground shadow-card transition-colors hover:border-primary/30 hover:bg-primary/5 hover:text-primary";

  return (
    <header className="sticky top-0 z-30 border-b border-border/80 bg-card/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-card/85 lg:px-5">
      <div className="flex h-[68px] items-center gap-3">
        <button
          onClick={() => dispatch(setSidebarOpen(true))}
          className={cn(actionButton, "lg:hidden")}
          aria-label="Open sidebar"
        >
          <Menu className="h-5 w-5" />
        </button>

        <button
          onClick={() => dispatch(toggleCollapse())}
          className={cn(actionButton, "hidden lg:flex")}
          aria-label={isCollapsed ? "Expand sidebar" : "Collapse sidebar"}
        >
          {isCollapsed ? (
            <PanelLeftOpen className="h-4 w-4" />
          ) : (
            <PanelLeftClose className="h-4 w-4" />
          )}
        </button>

        {/* Search */}
        <div className="flex-1">
          {searchOpen ? (
            <div className="flex h-10 max-w-xl items-center gap-2 rounded-xl border border-primary/30 bg-background px-3 shadow-card ring-4 ring-primary/10">
              <Search className="h-4 w-4 text-muted-foreground" />
              <input
                type="text"
                placeholder="Search dashboards, apps, components..."
                className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                autoFocus
                onBlur={() => setSearchOpen(false)}
                onKeyDown={(e) => e.key === "Escape" && setSearchOpen(false)}
              />
            </div>
          ) : (
            <button
              onClick={() => setSearchOpen(true)}
              className="flex h-10 w-full max-w-xl items-center gap-2 rounded-xl border border-border/80 bg-background px-3 text-sm text-muted-foreground shadow-card transition-colors hover:border-primary/30 hover:text-foreground"
            >
              <Search className="h-4 w-4" />
              <span className="hidden flex-1 text-left sm:inline">
                Search dashboards, apps, components...
              </span>
              <kbd className="ml-auto hidden rounded-md border border-border bg-muted px-1.5 py-0.5 text-2xs font-semibold sm:inline">
                Ctrl K
              </kbd>
            </button>
          )}
        </div>

        {/* Actions */}
        <div className="flex items-center gap-2">
          <button
            onClick={() => {
              if (!document.fullscreenElement) document.documentElement.requestFullscreen();
              else document.exitFullscreen();
            }}
            className={cn(actionButton, "hidden md:flex")}
            aria-label="Toggle fullscreen"
          >
            <Maximize2 className="h-4 w-4" />
          </button>

          {/* Both icons render on the server; CSS picks one so markup matches on hydration */}
          <button
            onClick={() => setTheme(resolvedTheme === "dark" ? "light" : "dark")}
            className={actionButton}
            aria-label="Toggle theme"
          >
            <Sun className="hidden h-4 w-4 dark:block" />
            <Moon className="h-4 w-4 dark:hidden" />
          </button>

          <button className={cn(actionButton, "relative")} aria-label="Notifications">
            <Bell className="h-4 w-4" />
            <span className="absolute right-2 top-2 h-2 w-2 rounded-full border-2 border-card bg-destructive" />
          </button>

          <UserMenu />
        </div>
      </div>
    </header>
  );
}
