"use client";

import { navigation } from "@/config/navigation";
import type { NavItem } from "@/types";
import { ChevronRight, Home } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";

// Grouping segments like /app or /modules have no page, so only real routes become links
function collectHrefs(items: NavItem[]): string[] {
  return items.flatMap((item) => [item.href, ...collectHrefs(item.children ?? [])]);
}
const routeHrefs = new Set(navigation.flatMap((group) => collectHrefs(group.items)));

export function Breadcrumb() {
  const pathname = usePathname();
  const segments = pathname.split("/").filter(Boolean);

  if (segments.length === 0) return null;

  const breadcrumbs = segments.map((segment, index) => {
    const href = "/" + segments.slice(0, index + 1).join("/");
    const label = segment.replace(/-/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    const isLast = index === segments.length - 1;

    return { label, href, isLast, isLink: !isLast && routeHrefs.has(href) };
  });

  return (
    <nav aria-label="Breadcrumb" className="flex items-center gap-1 text-sm text-muted-foreground">
      <Link
        href="/dashboard"
        aria-label="Home"
        className="flex items-center gap-1 transition-colors hover:text-foreground"
      >
        <Home className="h-3.5 w-3.5" />
      </Link>
      {breadcrumbs.map((crumb) => (
        <span key={crumb.href} className="flex items-center gap-1">
          <ChevronRight className="h-3.5 w-3.5" aria-hidden="true" />
          {crumb.isLast ? (
            <span aria-current="page" className="font-medium text-foreground">
              {crumb.label}
            </span>
          ) : crumb.isLink ? (
            <Link href={crumb.href} className="transition-colors hover:text-foreground">
              {crumb.label}
            </Link>
          ) : (
            <span>{crumb.label}</span>
          )}
        </span>
      ))}
    </nav>
  );
}
