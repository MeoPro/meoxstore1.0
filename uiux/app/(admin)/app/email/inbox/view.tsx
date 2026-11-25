"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import { Inbox as InboxIcon, MailOpen, PenSquare, Search, Star, Trash2 } from "lucide-react";
import Link from "next/link";
import { useMemo, useState } from "react";

type Label = "work" | "project" | "system" | "meeting" | "social";

type Email = {
  id: number;
  from: string;
  color: string;
  subject: string;
  preview: string;
  time: string;
  day: string;
  unread: boolean;
  starred: boolean;
  label: Label;
};

const initialEmails: Email[] = [
  {
    id: 1,
    from: "Sarah Chen",
    color: "from-violet-500 to-purple-600",
    subject: "Q3 Report Ready for Review",
    preview: "Hi, the Q3 report is now ready. Please review at your earliest convenience.",
    time: "10:30 AM",
    day: "Today",
    unread: true,
    starred: true,
    label: "work",
  },
  {
    id: 2,
    from: "Mike Johnson",
    color: "from-sky-500 to-blue-600",
    subject: "Re: Project Timeline",
    preview:
      "Thanks for the update. I've adjusted the milestones accordingly and pushed the new schedule.",
    time: "9:15 AM",
    day: "Today",
    unread: true,
    starred: false,
    label: "project",
  },
  {
    id: 3,
    from: "System",
    color: "from-slate-500 to-gray-600",
    subject: "Your weekly digest",
    preview: "Here's a summary of your activity this week: 24 tasks completed, 3 new followers...",
    time: "8:00 AM",
    day: "Today",
    unread: false,
    starred: false,
    label: "system",
  },
  {
    id: 4,
    from: "Emma Davis",
    color: "from-pink-500 to-rose-600",
    subject: "Design Review Meeting",
    preview:
      "Can we schedule the design review for Thursday afternoon? I have the latest mockups ready.",
    time: "4:12 PM",
    day: "Yesterday",
    unread: false,
    starred: true,
    label: "meeting",
  },
  {
    id: 5,
    from: "James Wilson",
    color: "from-emerald-500 to-green-600",
    subject: "API Documentation Updated",
    preview:
      "I've updated the API docs with the new endpoints and added examples for the auth flow.",
    time: "11:48 AM",
    day: "Yesterday",
    unread: false,
    starred: false,
    label: "work",
  },
  {
    id: 6,
    from: "Lisa Park",
    color: "from-amber-500 to-orange-600",
    subject: "Team Lunch Friday",
    preview:
      "Hey everyone! Let's do lunch at the new place on Friday. Vote on the menu before noon.",
    time: "9:02 AM",
    day: "Yesterday",
    unread: false,
    starred: false,
    label: "social",
  },
  {
    id: 7,
    from: "David Kim",
    color: "from-cyan-500 to-teal-600",
    subject: "Staging Deployment",
    preview:
      "The latest build is live on staging. E2E suite is running and I'll share results shortly.",
    time: "Yesterday",
    day: "Yesterday",
    unread: false,
    starred: false,
    label: "project",
  },
  {
    id: 8,
    from: "Nina Gupta",
    color: "from-indigo-500 to-blue-700",
    subject: "Q3 Metrics Report",
    preview:
      "Attached is the analytics summary. Conversion improved across all funnels this quarter.",
    time: "Tue",
    day: "Tuesday",
    unread: false,
    starred: true,
    label: "system",
  },
];

const labelColors: Record<Label, "info" | "success" | "warning" | "secondary" | "default"> = {
  work: "info",
  project: "success",
  system: "secondary",
  meeting: "warning",
  social: "default",
};

type Filter = "all" | "unread" | "starred";

export default function InboxPage() {
  const [emails, setEmails] = useState(initialEmails);
  const [query, setQuery] = useState("");
  const [filter, setFilter] = useState<Filter>("all");
  const [selected, setSelected] = useState<Set<number>>(new Set());

  const visible = useMemo(() => {
    const q = query.trim().toLowerCase();
    return emails.filter((e) => {
      const matchesQuery = !q || `${e.from} ${e.subject} ${e.preview}`.toLowerCase().includes(q);
      const matchesFilter = filter === "all" || (filter === "unread" ? e.unread : e.starred);
      return matchesQuery && matchesFilter;
    });
  }, [emails, query, filter]);

  const allSelected = visible.length > 0 && visible.every((e) => selected.has(e.id));

  const toggleSelected = (id: number) => {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const toggleAll = () => {
    setSelected(allSelected ? new Set() : new Set(visible.map((e) => e.id)));
  };

  const toggleStar = (id: number) => {
    setEmails((prev) => prev.map((e) => (e.id === id ? { ...e, starred: !e.starred } : e)));
  };

  const setRead = (ids: number[], read: boolean) => {
    setEmails((prev) => prev.map((e) => (ids.includes(e.id) ? { ...e, unread: !read } : e)));
  };

  const deleteEmails = (ids: number[]) => {
    setEmails((prev) => prev.filter((e) => !ids.includes(e.id)));
    setSelected(new Set());
  };

  const markRead = (id: number) => {
    setEmails((prev) => prev.map((e) => (e.id === id ? { ...e, unread: false } : e)));
  };

  const unreadCount = emails.filter((e) => e.unread).length;

  return (
    <div className="flex h-full min-h-0 flex-col space-y-6">
      <PageHeader
        title="Inbox"
        description={`Manage your emails and conversations · ${unreadCount} unread`}
        actions={
          <Link href="/app/email/compose">
            <Button>
              <PenSquare className="h-4 w-4" />
              Compose
            </Button>
          </Link>
        }
      />

      <Card className="flex min-h-0 flex-1 flex-col overflow-hidden">
        {/* Toolbar */}
        <div className="flex flex-wrap items-center gap-3 border-b border-border px-4 py-3">
          <div className="min-w-[180px] flex-1">
            <Input
              icon={<Search className="h-4 w-4" />}
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Search emails..."
            />
          </div>

          <input
            type="checkbox"
            checked={allSelected}
            onChange={toggleAll}
            className="h-4 w-4 accent-primary"
            aria-label="Select all"
          />

          <div className="flex gap-1 rounded-lg bg-muted p-0.5">
            {(["all", "unread", "starred"] as Filter[]).map((f) => (
              <button
                key={f}
                onClick={() => setFilter(f)}
                className={cn(
                  "rounded-md px-2.5 py-1 text-xs font-medium capitalize transition-colors",
                  filter === f
                    ? "bg-card text-foreground shadow-card"
                    : "text-muted-foreground hover:text-foreground",
                )}
              >
                {f}
              </button>
            ))}
          </div>
        </div>

        {/* Bulk actions */}
        {selected.size > 0 && (
          <div className="flex items-center gap-2 border-b border-border bg-primary/5 px-4 py-2">
            <span className="text-xs font-medium">{selected.size} selected</span>
            <div className="ml-auto flex gap-2">
              <Button size="sm" variant="ghost" onClick={() => setRead([...selected], true)}>
                <MailOpen className="h-3.5 w-3.5" />
                Read
              </Button>
              <Button size="sm" variant="ghost" onClick={() => setRead([...selected], false)}>
                <InboxIcon className="h-3.5 w-3.5" />
                Unread
              </Button>
              <Button
                size="sm"
                variant="ghost"
                className="text-destructive hover:text-destructive"
                onClick={() => deleteEmails([...selected])}
              >
                <Trash2 className="h-3.5 w-3.5" />
                Delete
              </Button>
            </div>
          </div>
        )}

        {/* List */}
        <div className="flex-1 overflow-y-auto">
          {visible.length === 0 && (
            <div className="flex flex-col items-center justify-center gap-2 py-16 text-center">
              <span className="grid h-12 w-12 place-items-center rounded-full bg-muted">
                <InboxIcon className="h-5 w-5 text-muted-foreground" />
              </span>
              <p className="text-sm font-medium">No emails found</p>
              <p className="text-xs text-muted-foreground">Try a different search or filter.</p>
            </div>
          )}

          {visible.map((email) => (
            <div
              key={email.id}
              className={cn(
                "group flex items-start gap-3 border-b border-border px-4 py-3 transition-colors last:border-0",
                email.unread ? "bg-primary/5" : "hover:bg-muted/40",
                selected.has(email.id) && "bg-primary/10",
              )}
            >
              <input
                type="checkbox"
                checked={selected.has(email.id)}
                onChange={() => toggleSelected(email.id)}
                className="mt-1.5 h-4 w-4 accent-primary"
                aria-label={`Select ${email.from}`}
              />
              <button
                onClick={() => toggleStar(email.id)}
                className="mt-1 flex h-6 w-6 items-center justify-center rounded-md text-muted-foreground/50 transition-colors hover:bg-muted hover:text-warning"
                aria-label="Toggle star"
              >
                <Star className={cn("h-4 w-4", email.starred && "fill-warning text-warning")} />
              </button>

              <Link
                href="/app/email/detail"
                onClick={() => markRead(email.id)}
                className="flex min-w-0 flex-1 items-center gap-3"
              >
                <div className="relative shrink-0">
                  <Avatar
                    name={email.from}
                    size="md"
                    className={cn("bg-gradient-to-br", email.color)}
                  />
                  {email.unread && (
                    <span className="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-primary ring-2 ring-card" />
                  )}
                </div>
                <div className="min-w-0 flex-1">
                  <div className="flex items-center gap-2">
                    <p
                      className={cn(
                        "truncate text-sm",
                        email.unread ? "font-semibold" : "font-medium",
                      )}
                    >
                      {email.from}
                    </p>
                    <Badge
                      variant={labelColors[email.label]}
                      className="hidden text-2xs sm:inline-flex"
                    >
                      {email.label}
                    </Badge>
                    <span className="ml-auto shrink-0 text-2xs text-muted-foreground">
                      {email.time}
                    </span>
                  </div>
                  <p
                    className={cn(
                      "truncate text-sm",
                      email.unread ? "font-medium text-foreground" : "text-muted-foreground",
                    )}
                  >
                    {email.subject}
                  </p>
                  <p className="hidden truncate text-xs text-muted-foreground sm:block">
                    {email.preview}
                  </p>
                </div>
              </Link>
            </div>
          ))}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-between border-t border-border px-4 py-2 text-xs text-muted-foreground">
          <span>
            {visible.length} of {emails.length} emails
          </span>
          <span>{unreadCount} unread</span>
        </div>
      </Card>
    </div>
  );
}
