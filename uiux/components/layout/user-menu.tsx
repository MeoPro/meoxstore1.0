"use client";

import { demoUser } from "@/config/site";
import { cn, getInitials } from "@/lib/utils";
import { ChevronDown, Lock, LogOut, Settings, ShieldCheck, User } from "lucide-react";
import Link from "next/link";
import { useEffect, useRef, useState } from "react";

const menuItems = [
  { label: "My Profile", href: "/pages/user/profile", icon: User },
  { label: "Account Settings", href: "/pages/user/settings", icon: Settings },
  { label: "Lock Screen", href: "/auth/simple/lock-screen", icon: Lock },
];

export function UserMenu() {
  const [open, setOpen] = useState(false);
  const rootRef = useRef<HTMLDivElement>(null);
  const triggerRef = useRef<HTMLButtonElement>(null);

  useEffect(() => {
    if (!open) return;
    const onPointerDown = (e: PointerEvent) => {
      if (!rootRef.current?.contains(e.target as Node)) setOpen(false);
    };
    const onKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") {
        setOpen(false);
        triggerRef.current?.focus();
      }
    };
    document.addEventListener("pointerdown", onPointerDown);
    document.addEventListener("keydown", onKeyDown);
    return () => {
      document.removeEventListener("pointerdown", onPointerDown);
      document.removeEventListener("keydown", onKeyDown);
    };
  }, [open]);

  const itemClass =
    "flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-sm text-foreground transition-colors hover:bg-muted focus-visible:bg-muted focus-visible:outline-none";

  return (
    <div ref={rootRef} className="relative ml-1">
      <button
        ref={triggerRef}
        type="button"
        onClick={() => setOpen(!open)}
        aria-haspopup="menu"
        aria-expanded={open}
        aria-label="Open user menu"
        className="flex h-9 items-center gap-2 rounded-xl border border-border/80 bg-background px-1 shadow-card transition-colors hover:border-primary/30 hover:bg-primary/5 sm:px-2.5"
      >
        <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-teal-500 text-2xs font-bold text-white">
          {getInitials(demoUser.name)}
        </span>
        <span className="hidden text-left md:block">
          <span className="block text-xs font-semibold leading-none">{demoUser.name}</span>
          <span className="mt-0.5 flex items-center gap-1 text-2xs text-muted-foreground">
            <ShieldCheck className="h-3 w-3 text-success" />
            {demoUser.role}
          </span>
        </span>
        <ChevronDown
          className={cn(
            "hidden h-3.5 w-3.5 text-muted-foreground transition-transform duration-200 sm:block",
            open && "rotate-180",
          )}
        />
      </button>

      {open && (
        <div
          role="menu"
          aria-label="User menu"
          className="absolute right-0 top-full z-50 mt-2 w-60 rounded-xl border border-border bg-card p-1.5 shadow-dropdown"
        >
          <div className="border-b border-border px-2.5 pb-2.5 pt-1.5">
            <p className="truncate text-sm font-semibold">{demoUser.name}</p>
            <p className="truncate text-xs text-muted-foreground">{demoUser.email}</p>
          </div>
          <div className="py-1">
            {menuItems.map(({ label, href, icon: Icon }) => (
              <Link
                key={href}
                href={href}
                role="menuitem"
                onClick={() => setOpen(false)}
                className={itemClass}
              >
                <Icon className="h-4 w-4 text-muted-foreground" />
                {label}
              </Link>
            ))}
          </div>
          <div className="border-t border-border pt-1">
            <Link
              href="/auth/simple/login"
              role="menuitem"
              onClick={() => setOpen(false)}
              className={cn(itemClass, "text-destructive hover:bg-destructive/10")}
            >
              <LogOut className="h-4 w-4" />
              Sign Out
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}
