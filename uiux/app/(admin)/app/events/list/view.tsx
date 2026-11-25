"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { EmptyState } from "@/components/ui/empty-state";
import { GradientCard } from "@/components/ui/gradient-card";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import {
  Briefcase,
  CalendarDays,
  Check,
  Clock,
  LayoutGrid,
  List,
  MapPin,
  PartyPopper,
  Plus,
  Presentation,
  Search,
  Star,
  UserPlus,
  Users,
} from "lucide-react";
import Link from "next/link";
import { useMemo, useState } from "react";

type EventType = "meeting" | "social" | "workshop";

interface Attendee {
  name: string;
  gradient: string;
}

interface EventItem {
  id: number;
  title: string;
  day: string;
  month: string;
  time: string;
  location: string;
  description: string;
  type: EventType;
  attendees: Attendee[];
}

const typeConfig: Record<
  EventType,
  { label: string; badge: "info" | "success" | "warning"; gradient: string; icon: typeof Briefcase }
> = {
  meeting: {
    label: "Meeting",
    badge: "info",
    gradient: "from-sky-500 to-blue-600",
    icon: Briefcase,
  },
  social: {
    label: "Social",
    badge: "success",
    gradient: "from-pink-500 to-rose-500",
    icon: PartyPopper,
  },
  workshop: {
    label: "Workshop",
    badge: "warning",
    gradient: "from-amber-500 to-orange-600",
    icon: Presentation,
  },
};

const events: EventItem[] = [
  {
    id: 1,
    title: "Q3 Planning Workshop",
    day: "20",
    month: "Aug",
    time: "2:00 PM – 5:00 PM",
    location: "Conference Room A, 3rd Floor",
    description:
      "Quarterly planning session to review goals, set OKRs, and align on priorities for Q3.",
    type: "workshop",
    attendees: [
      { name: "Sarah Chen", gradient: "from-emerald-400 to-teal-600" },
      { name: "Mike Johnson", gradient: "from-fuchsia-400 to-purple-600" },
      { name: "Emma Davis", gradient: "from-orange-400 to-red-600" },
      { name: "James Wilson", gradient: "from-indigo-400 to-blue-600" },
    ],
  },
  {
    id: 2,
    title: "Product Launch Party",
    day: "25",
    month: "Aug",
    time: "6:00 PM – 9:00 PM",
    location: "Main Hall, 1st Floor",
    description:
      "Celebrate the launch of our latest product with the full team. Drinks and refreshments provided.",
    type: "social",
    attendees: [
      { name: "Lisa Park", gradient: "from-rose-400 to-pink-600" },
      { name: "David Kim", gradient: "from-emerald-400 to-teal-600" },
      { name: "Nina Gupta", gradient: "from-fuchsia-400 to-purple-600" },
    ],
  },
  {
    id: 3,
    title: "Tech Talk: AI in Production",
    day: "28",
    month: "Aug",
    time: "11:00 AM – 12:30 PM",
    location: "Auditorium",
    description:
      "Engineering deep-dive on running LLMs in production at scale, with live demos and Q&A.",
    type: "workshop",
    attendees: [
      { name: "Alex Turner", gradient: "from-orange-400 to-red-600" },
      { name: "Priya Sharma", gradient: "from-indigo-400 to-blue-600" },
      { name: "Tom Becker", gradient: "from-rose-400 to-pink-600" },
      { name: "Maya Patel", gradient: "from-emerald-400 to-teal-600" },
    ],
  },
  {
    id: 4,
    title: "Team Building Activity",
    day: "02",
    month: "Sep",
    time: "10:00 AM – 1:00 PM",
    location: "Outdoor Area, Roof Garden",
    description:
      "Outdoor games, lunch, and a friendly competition to close out the sprint. Family friendly.",
    type: "social",
    attendees: [
      { name: "Sarah Chen", gradient: "from-emerald-400 to-teal-600" },
      { name: "Nina Gupta", gradient: "from-fuchsia-400 to-purple-600" },
      { name: "Tom Becker", gradient: "from-rose-400 to-pink-600" },
    ],
  },
  {
    id: 5,
    title: "Sprint Review & Demo",
    day: "05",
    month: "Sep",
    time: "3:00 PM – 4:00 PM",
    location: "Virtual · Google Meet",
    description:
      "Walk through everything shipped this sprint, review the roadmap, and gather feedback.",
    type: "meeting",
    attendees: [
      { name: "David Kim", gradient: "from-emerald-400 to-teal-600" },
      { name: "Lisa Park", gradient: "from-rose-400 to-pink-600" },
      { name: "James Wilson", gradient: "from-indigo-400 to-blue-600" },
    ],
  },
  {
    id: 6,
    title: "Design System Workshop",
    day: "10",
    month: "Sep",
    time: "1:00 PM – 4:00 PM",
    location: "Design Lab, 2nd Floor",
    description:
      "Hands-on session to refresh tokens, components, and documentation for the design system.",
    type: "workshop",
    attendees: [
      { name: "Emma Davis", gradient: "from-orange-400 to-red-600" },
      { name: "Priya Sharma", gradient: "from-indigo-400 to-blue-600" },
      { name: "Maya Patel", gradient: "from-emerald-400 to-teal-600" },
      { name: "Alex Turner", gradient: "from-orange-400 to-red-600" },
    ],
  },
];

const tabs: { id: "all" | EventType; label: string }[] = [
  { id: "all", label: "All" },
  { id: "meeting", label: "Meeting" },
  { id: "social", label: "Social" },
  { id: "workshop", label: "Workshop" },
];

export default function EventsListPage() {
  const [query, setQuery] = useState("");
  const [typeFilter, setTypeFilter] = useState<"all" | EventType>("all");
  const [view, setView] = useState<"grid" | "list">("grid");
  const [registered, setRegistered] = useState<Set<number>>(new Set([1, 3]));
  const [starred, setStarred] = useState<Set<number>>(new Set([2]));

  const filtered = useMemo(
    () =>
      events.filter((e) => {
        const matchesType = typeFilter === "all" || e.type === typeFilter;
        const matchesQuery = e.title.toLowerCase().includes(query.trim().toLowerCase());
        return matchesType && matchesQuery;
      }),
    [typeFilter, query],
  );

  const toggleRegistered = (id: number) => {
    setRegistered((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  };

  const toggleStarred = (id: number) => {
    setStarred((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  };

  const totalInvited = events.reduce((sum, e) => sum + e.attendees.length, 0);

  const stats = [
    {
      title: "Upcoming events",
      value: events.length,
      icon: <CalendarDays className="h-4 w-4" />,
      variant: "primary" as const,
    },
    {
      title: "Total invited",
      value: totalInvited,
      icon: <Users className="h-4 w-4" />,
      variant: "info" as const,
    },
    {
      title: "You're going",
      value: registered.size,
      icon: <Check className="h-4 w-4" />,
      variant: "success" as const,
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Events"
        description="Discover, register, and manage upcoming events."
        actions={
          <Link href="/app/events/create">
            <Button>
              <Plus className="h-4 w-4" />
              Create Event
            </Button>
          </Link>
        }
      />

      <div className="grid gap-4 sm:grid-cols-3">
        {stats.map((s) => (
          <GradientCard key={s.title} {...s} />
        ))}
      </div>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex flex-wrap items-center gap-1">
          {tabs.map((tab) => {
            const count =
              tab.id === "all" ? events.length : events.filter((e) => e.type === tab.id).length;
            return (
              <button
                key={tab.id}
                onClick={() => setTypeFilter(tab.id)}
                className={cn(
                  "flex h-8 items-center gap-1.5 rounded-md px-3 text-sm font-medium transition-colors",
                  typeFilter === tab.id
                    ? "bg-primary text-primary-foreground"
                    : "text-muted-foreground hover:bg-muted hover:text-foreground",
                )}
              >
                {tab.label}
                <span
                  className={cn(
                    "rounded-full px-1.5 text-2xs font-semibold",
                    typeFilter === tab.id ? "bg-primary-foreground/20" : "bg-muted",
                  )}
                >
                  {count}
                </span>
              </button>
            );
          })}
        </div>

        <div className="flex items-center gap-2">
          <div className="flex-1 sm:w-60 sm:flex-none">
            <Input
              icon={<Search className="h-4 w-4" />}
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Search events..."
            />
          </div>
          <div className="flex rounded-md border border-border p-0.5">
            <button
              onClick={() => setView("grid")}
              aria-label="Grid view"
              className={cn(
                "flex h-8 w-8 items-center justify-center rounded-md",
                view === "grid"
                  ? "bg-muted text-foreground"
                  : "text-muted-foreground hover:text-foreground",
              )}
            >
              <LayoutGrid className="h-4 w-4" />
            </button>
            <button
              onClick={() => setView("list")}
              aria-label="List view"
              className={cn(
                "flex h-8 w-8 items-center justify-center rounded-md",
                view === "list"
                  ? "bg-muted text-foreground"
                  : "text-muted-foreground hover:text-foreground",
              )}
            >
              <List className="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      {filtered.length === 0 ? (
        <EmptyState
          icon={<CalendarDays className="h-8 w-8" />}
          title="No events found"
          description="Try adjusting your search or filter to find what you're looking for."
        />
      ) : view === "grid" ? (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          {filtered.map((event) => {
            const isGoing = registered.has(event.id);
            const isStarred = starred.has(event.id);
            const config = typeConfig[event.type];
            return (
              <Card
                key={event.id}
                className="flex flex-col transition-shadow hover:shadow-dropdown"
              >
                <CardContent className="flex flex-1 flex-col gap-4 p-5">
                  <div className="flex items-start gap-3">
                    <div
                      className={cn(
                        "flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-gradient-to-br text-white",
                        config.gradient,
                      )}
                    >
                      <span className="text-base font-bold leading-none">{event.day}</span>
                      <span className="text-2xs font-medium uppercase opacity-90">
                        {event.month}
                      </span>
                    </div>
                    <div className="min-w-0 flex-1">
                      <div className="flex items-start justify-between gap-2">
                        <Link
                          href="/app/events/detail"
                          className="line-clamp-1 text-sm font-semibold transition-colors hover:text-primary"
                        >
                          {event.title}
                        </Link>
                        <button
                          onClick={() => toggleStarred(event.id)}
                          aria-label={isStarred ? "Remove from saved" : "Save event"}
                          className="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        >
                          <Star
                            className={cn("h-4 w-4", isStarred && "fill-amber-400 text-amber-400")}
                          />
                        </button>
                      </div>
                      <div className="mt-1 flex items-center gap-2">
                        <Badge variant={config.badge}>{config.label}</Badge>
                        <span className="flex items-center gap-1 text-xs text-muted-foreground">
                          <Clock className="h-3 w-3" />
                          {event.time}
                        </span>
                      </div>
                    </div>
                  </div>

                  <p className="line-clamp-2 text-sm text-muted-foreground">{event.description}</p>

                  <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                    <MapPin className="h-4 w-4 shrink-0" />
                    <span className="line-clamp-1">{event.location}</span>
                  </div>

                  <div className="mt-auto flex items-center justify-between gap-2 border-t border-border pt-3">
                    <div className="flex items-center gap-2">
                      <div className="flex -space-x-2">
                        {event.attendees.slice(0, 3).map((a) => (
                          <Avatar
                            key={a.name}
                            name={a.name}
                            size="sm"
                            className={cn("bg-gradient-to-br ring-2 ring-background", a.gradient)}
                          />
                        ))}
                        {event.attendees.length > 3 && (
                          <div className="flex h-8 w-8 items-center justify-center rounded-full bg-muted text-2xs font-semibold text-muted-foreground ring-2 ring-background">
                            +{event.attendees.length - 3}
                          </div>
                        )}
                      </div>
                      <span className="text-xs text-muted-foreground">
                        {event.attendees.length} going
                      </span>
                    </div>
                    <Button
                      size="sm"
                      variant={isGoing ? "success" : "outline"}
                      onClick={() => toggleRegistered(event.id)}
                    >
                      {isGoing ? <Check className="h-4 w-4" /> : <UserPlus className="h-4 w-4" />}
                      {isGoing ? "Going" : "Register"}
                    </Button>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>
      ) : (
        <Card>
          <div className="divide-y divide-border">
            {filtered.map((event) => {
              const isGoing = registered.has(event.id);
              const isStarred = starred.has(event.id);
              const config = typeConfig[event.type];
              return (
                <div
                  key={event.id}
                  className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center"
                >
                  <div
                    className={cn(
                      "flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-gradient-to-br text-white",
                      config.gradient,
                    )}
                  >
                    <span className="text-base font-bold leading-none">{event.day}</span>
                    <span className="text-2xs font-medium uppercase opacity-90">{event.month}</span>
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2">
                      <Link
                        href="/app/events/detail"
                        className="truncate text-sm font-semibold transition-colors hover:text-primary"
                      >
                        {event.title}
                      </Link>
                      <Badge variant={config.badge}>{config.label}</Badge>
                    </div>
                    <div className="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                      <span className="flex items-center gap-1">
                        <CalendarDays className="h-3 w-3" />
                        {event.month} {event.day}, 2026
                      </span>
                      <span className="flex items-center gap-1">
                        <Clock className="h-3 w-3" />
                        {event.time}
                      </span>
                      <span className="flex items-center gap-1">
                        <MapPin className="h-3 w-3" />
                        {event.location}
                      </span>
                      <span className="flex items-center gap-1">
                        <Users className="h-3 w-3" />
                        {event.attendees.length} going
                      </span>
                    </div>
                  </div>
                  <div className="flex items-center gap-2 self-start sm:self-center">
                    <button
                      onClick={() => toggleStarred(event.id)}
                      aria-label={isStarred ? "Remove from saved" : "Save event"}
                      className="flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    >
                      <Star
                        className={cn("h-4 w-4", isStarred && "fill-amber-400 text-amber-400")}
                      />
                    </button>
                    <Button
                      size="sm"
                      variant={isGoing ? "success" : "outline"}
                      onClick={() => toggleRegistered(event.id)}
                    >
                      {isGoing ? <Check className="h-4 w-4" /> : <UserPlus className="h-4 w-4" />}
                      {isGoing ? "Going" : "Register"}
                    </Button>
                  </div>
                </div>
              );
            })}
          </div>
        </Card>
      )}
    </div>
  );
}
