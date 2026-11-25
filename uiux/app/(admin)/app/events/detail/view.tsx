"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import {
  Briefcase,
  Calendar,
  CalendarPlus,
  Check,
  CheckCircle2,
  ChevronLeft,
  Clock,
  Mail,
  MapPin,
  Pencil,
  Search,
  Share2,
  Star,
  UserPlus,
  Users,
  Video,
  X,
} from "lucide-react";
import { useRouter } from "next/navigation";
import { useMemo, useState } from "react";

interface Attendee {
  name: string;
  email: string;
  status: "accepted" | "pending" | "declined";
  gradient: string;
}

const attendees: Attendee[] = [
  {
    name: "Sarah Chen",
    email: "sarah@company.com",
    status: "accepted",
    gradient: "from-emerald-400 to-teal-600",
  },
  {
    name: "Mike Johnson",
    email: "mike@company.com",
    status: "accepted",
    gradient: "from-fuchsia-400 to-purple-600",
  },
  {
    name: "Emma Davis",
    email: "emma@company.com",
    status: "accepted",
    gradient: "from-orange-400 to-red-600",
  },
  {
    name: "James Wilson",
    email: "james@company.com",
    status: "pending",
    gradient: "from-indigo-400 to-blue-600",
  },
  {
    name: "Lisa Park",
    email: "lisa@company.com",
    status: "pending",
    gradient: "from-rose-400 to-pink-600",
  },
  {
    name: "David Kim",
    email: "david@company.com",
    status: "declined",
    gradient: "from-emerald-400 to-teal-600",
  },
  {
    name: "Nina Gupta",
    email: "nina@company.com",
    status: "declined",
    gradient: "from-fuchsia-400 to-purple-600",
  },
];

const agenda = [
  {
    time: "1:30 PM",
    title: "Arrival & coffee",
    detail: "Grab a coffee and settle in, materials are on the tables.",
  },
  {
    time: "2:00 PM",
    title: "Welcome & recap",
    detail: "Kickoff, last quarter recap, and what's changed since.",
  },
  {
    time: "2:30 PM",
    title: "OKR deep-dive",
    detail: "Review the proposed OKRs for each team in breakout groups.",
  },
  {
    time: "3:30 PM",
    title: "Prioritization exercise",
    detail: "Vote and align on top priorities for the quarter.",
  },
  {
    time: "4:30 PM",
    title: "Wrap-up & next steps",
    detail: "Summarize decisions and assign owners and deadlines.",
  },
];

const statusColors: Record<Attendee["status"], "success" | "warning" | "destructive"> = {
  accepted: "success",
  pending: "warning",
  declined: "destructive",
};

const statusTabs: { id: "all" | Attendee["status"]; label: string }[] = [
  { id: "all", label: "All" },
  { id: "accepted", label: "Accepted" },
  { id: "pending", label: "Pending" },
  { id: "declined", label: "Declined" },
];

export default function EventDetailPage() {
  const router = useRouter();
  const [going, setGoing] = useState(false);
  const [maybe, setMaybe] = useState(false);
  const [saved, setSaved] = useState(false);
  const [cancelled, setCancelled] = useState(false);
  const [attendeeFilter, setAttendeeFilter] = useState<"all" | Attendee["status"]>("all");
  const [attendeeQuery, setAttendeeQuery] = useState("");
  const [notice, setNotice] = useState<string | null>(null);

  const acceptedCount = attendees.filter((a) => a.status === "accepted").length;
  const pendingCount = attendees.filter((a) => a.status === "pending").length;
  const declinedCount = attendees.filter((a) => a.status === "declined").length;
  const confirmedCount = acceptedCount + (going ? 1 : 0);

  const filteredAttendees = useMemo(
    () =>
      attendees.filter((a) => {
        const matchesStatus = attendeeFilter === "all" || a.status === attendeeFilter;
        const matchesQuery = a.name.toLowerCase().includes(attendeeQuery.trim().toLowerCase());
        return matchesStatus && matchesQuery;
      }),
    [attendeeFilter, attendeeQuery],
  );

  const toggleGoing = () => {
    if (!going) {
      setMaybe(false);
      setGoing(true);
      setNotice("You're going — see you there!");
    } else {
      setGoing(false);
      setNotice("You're no longer attending.");
    }
  };

  const toggleMaybe = () => {
    if (!maybe) {
      setGoing(false);
      setMaybe(true);
      setNotice("Marked as maybe — we'll keep you posted.");
    } else {
      setMaybe(false);
      setNotice("Removed your maybe RSVP.");
    }
  };

  const toggleSave = () => {
    setSaved((prev) => {
      const next = !prev;
      setNotice(next ? "Event saved to your calendar." : "Event removed from your calendar.");
      return next;
    });
  };

  const shareEvent = () => {
    setNotice("Link copied to clipboard.");
  };

  const inviteGuests = () => {
    setNotice("Invitation sent to pending guests.");
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Event Detail"
        description="Review event information, RSVP, and manage attendees."
        actions={
          <div className="flex flex-wrap gap-2">
            <Button variant="outline" onClick={() => router.push("/app/events/list")}>
              <ChevronLeft className="h-4 w-4" />
              Back
            </Button>
            <Button variant="outline" onClick={toggleSave}>
              <Star className={cn("h-4 w-4", saved && "fill-amber-400 text-amber-400")} />
              {saved ? "Saved" : "Save"}
            </Button>
            <Button variant="outline" onClick={() => router.push("/app/events/create")}>
              <Pencil className="h-4 w-4" />
              Edit
            </Button>
            <Button variant="outline" onClick={shareEvent}>
              <Share2 className="h-4 w-4" />
              Share
            </Button>
            <Button
              variant={cancelled ? "default" : "destructive"}
              onClick={() => setCancelled((prev) => !prev)}
            >
              <X className="h-4 w-4" />
              {cancelled ? "Reactivate" : "Cancel Event"}
            </Button>
          </div>
        }
      />

      {notice && (
        <div
          className={cn(
            "flex items-center gap-2.5 rounded-lg border px-4 py-3 text-sm",
            notice.includes("no longer") || notice.includes("cancel")
              ? "border-muted bg-muted/50 text-foreground"
              : "border-success/30 bg-success/5 text-success",
          )}
        >
          <CheckCircle2 className="h-4 w-4 shrink-0" />
          {notice}
          <Button
            size="sm"
            variant="ghost"
            className="ml-auto h-7 px-2"
            onClick={() => setNotice(null)}
          >
            <X className="h-3.5 w-3.5" />
          </Button>
        </div>
      )}

      <Card className="overflow-hidden">
        <div className="relative bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500 px-6 pb-6 pt-16 text-white">
          <div className="absolute right-5 top-5 flex items-center gap-2">
            <Badge
              variant={cancelled ? "destructive" : "secondary"}
              className="bg-white/20 text-white hover:bg-white/20"
            >
              {cancelled ? "Cancelled" : "Confirmed"}
            </Badge>
          </div>
          <div className="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-white/80">
            <Briefcase className="h-3.5 w-3.5" />
            Workshop
          </div>
          <h2 className="mt-1 text-2xl font-semibold">Q3 Planning Workshop</h2>
          <p className="mt-1 max-w-2xl text-sm text-white/85">
            Quarterly planning session to review goals, set OKRs, and align on priorities for Q3.
          </p>
          <div className="mt-5 flex flex-wrap items-center gap-2">
            <Button variant="secondary" onClick={toggleGoing}>
              <Check className={cn("h-4 w-4", !going && "opacity-0")} />
              {going ? "Going" : "RSVP Going"}
            </Button>
            <Button
              variant="secondary"
              className={cn(!maybe && "bg-white/15 text-white hover:bg-white/25")}
              onClick={toggleMaybe}
            >
              {maybe ? "Maybe ✓" : "Maybe"}
            </Button>
            <Button
              variant="secondary"
              className="bg-white/15 text-white hover:bg-white/25"
              onClick={shareEvent}
            >
              <CalendarPlus className="h-4 w-4" />
              Add to Calendar
            </Button>
          </div>
        </div>
        <CardContent className="p-0">
          <div className="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4 sm:p-6">
            <div>
              <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                Date
              </p>
              <p className="mt-1.5 flex items-center gap-1.5 text-sm font-medium">
                <Calendar className="h-4 w-4 text-primary" />
                August 20, 2026
              </p>
            </div>
            <div>
              <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                Time
              </p>
              <p className="mt-1.5 flex items-center gap-1.5 text-sm font-medium">
                <Clock className="h-4 w-4 text-primary" />
                2:00 PM – 5:00 PM
              </p>
            </div>
            <div>
              <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                Location
              </p>
              <p className="mt-1.5 flex items-center gap-1.5 text-sm font-medium">
                <MapPin className="h-4 w-4 text-primary" />
                Conference Room A
              </p>
            </div>
            <div>
              <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                Attendees
              </p>
              <p className="mt-1.5 flex items-center gap-1.5 text-sm font-medium">
                <Users className="h-4 w-4 text-primary" />
                {confirmedCount} going
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Agenda</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-0">
                {agenda.map((item, index) => (
                  <div key={item.time} className="relative flex gap-4 pb-6 last:pb-0">
                    {index < agenda.length - 1 && (
                      <span className="absolute left-[5px] top-4 h-full w-px bg-border" />
                    )}
                    <span
                      className={cn(
                        "relative mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full ring-4 ring-background",
                        index === 0 ? "bg-primary" : "bg-muted-foreground/40",
                      )}
                    />
                    <div className="min-w-0 flex-1">
                      <div className="flex flex-wrap items-baseline gap-x-2">
                        <span className="text-xs font-semibold text-muted-foreground">
                          {item.time}
                        </span>
                        <span className="text-sm font-medium">{item.title}</span>
                      </div>
                      <p className="mt-1 text-sm text-muted-foreground">{item.detail}</p>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Meeting Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex items-start gap-3">
                <Mail className="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                <div>
                  <p className="text-sm font-medium">Organizer</p>
                  <p className="text-sm text-muted-foreground">events@company.com</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <Video className="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                <div>
                  <p className="text-sm font-medium">Virtual link</p>
                  <p className="text-sm text-muted-foreground">
                    For hybrid attendees — link will be shared 1 hour before the event.
                  </p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                <div>
                  <p className="text-sm font-medium">Notes</p>
                  <p className="text-sm text-muted-foreground">
                    Please come prepared with your team&apos;s progress reports and proposals.
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card className="flex flex-col">
          <CardHeader className="flex-row items-center justify-between space-y-0">
            <CardTitle>Attendees</CardTitle>
            <Badge variant="secondary">{attendees.length} total</Badge>
          </CardHeader>
          <CardContent className="flex flex-1 flex-col gap-3">
            <Input
              icon={<Search className="h-4 w-4" />}
              value={attendeeQuery}
              onChange={(e) => setAttendeeQuery(e.target.value)}
              placeholder="Search attendees..."
            />
            <div className="flex flex-wrap gap-1">
              {statusTabs.map((tab) => {
                const count =
                  tab.id === "all"
                    ? attendees.length
                    : attendees.filter((a) => a.status === tab.id).length;
                return (
                  <button
                    key={tab.id}
                    onClick={() => setAttendeeFilter(tab.id)}
                    className={cn(
                      "flex h-7 items-center gap-1.5 rounded-md px-2.5 text-xs font-medium transition-colors",
                      attendeeFilter === tab.id
                        ? "bg-primary text-primary-foreground"
                        : "text-muted-foreground hover:bg-muted hover:text-foreground",
                    )}
                  >
                    {tab.label}
                    <span
                      className={cn(
                        "rounded-full px-1.5 text-2xs font-semibold",
                        attendeeFilter === tab.id ? "bg-primary-foreground/20" : "bg-muted",
                      )}
                    >
                      {count}
                    </span>
                  </button>
                );
              })}
            </div>

            <div className="flex-1 space-y-1">
              {filteredAttendees.length === 0 ? (
                <p className="py-8 text-center text-sm text-muted-foreground">
                  No attendees match your search.
                </p>
              ) : (
                filteredAttendees.map((a) => (
                  <div
                    key={a.email}
                    className="flex items-center justify-between gap-2 rounded-md px-2 py-2 hover:bg-muted/50"
                  >
                    <div className="flex min-w-0 items-center gap-2.5">
                      <Avatar
                        name={a.name}
                        size="sm"
                        className={cn("bg-gradient-to-br", a.gradient)}
                      />
                      <div className="min-w-0">
                        <p className="truncate text-sm font-medium">{a.name}</p>
                        <p className="truncate text-xs text-muted-foreground">{a.email}</p>
                      </div>
                    </div>
                    <Badge variant={statusColors[a.status]}>{a.status}</Badge>
                  </div>
                ))
              )}
            </div>

            <div className="space-y-2 border-t border-border pt-3">
              <p className="text-xs text-muted-foreground">
                {acceptedCount} accepted · {pendingCount} pending · {declinedCount} declined
              </p>
              <Button variant="outline" className="w-full" onClick={inviteGuests}>
                <UserPlus className="h-4 w-4" />
                Invite More
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
