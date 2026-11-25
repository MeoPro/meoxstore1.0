"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { cn } from "@/lib/utils";
import {
  Briefcase,
  CalendarPlus,
  Check,
  CheckCircle2,
  Link2,
  MapPin,
  PartyPopper,
  Presentation,
  UserPlus,
  Video,
  X,
} from "lucide-react";
import { useState } from "react";

type EventType = "meeting" | "social" | "workshop";

interface Guest {
  id: number;
  email: string;
}

let guestSeq = 100;

const typeOptions: {
  id: EventType;
  label: string;
  icon: typeof Briefcase;
  gradient: string;
  hint: string;
}[] = [
  {
    id: "meeting",
    label: "Meeting",
    icon: Briefcase,
    gradient: "from-sky-500 to-blue-600",
    hint: "Sprints, reviews, syncs",
  },
  {
    id: "social",
    label: "Social",
    icon: PartyPopper,
    gradient: "from-pink-500 to-rose-500",
    hint: "Parties, team building",
  },
  {
    id: "workshop",
    label: "Workshop",
    icon: Presentation,
    gradient: "from-amber-500 to-orange-600",
    hint: "Training, deep-dives",
  },
];

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function CreateEventPage() {
  const [name, setName] = useState("");
  const [type, setType] = useState<EventType>("meeting");
  const [startDate, setStartDate] = useState("");
  const [startTime, setStartTime] = useState("");
  const [endDate, setEndDate] = useState("");
  const [endTime, setEndTime] = useState("");
  const [location, setLocation] = useState("");
  const [virtual, setVirtual] = useState(false);
  const [meetingUrl, setMeetingUrl] = useState("");
  const [capacity, setCapacity] = useState("");
  const [description, setDescription] = useState("");
  const [guestInput, setGuestInput] = useState("");
  const [guestError, setGuestError] = useState("");
  const [guests, setGuests] = useState<Guest[]>([]);
  const [fieldErrors, setFieldErrors] = useState<{ name?: string; date?: string }>({});
  const [notice, setNotice] = useState<"created" | "error" | null>(null);

  const addGuest = () => {
    const email = guestInput.trim();
    if (!email) return;
    if (!EMAIL_REGEX.test(email)) {
      setGuestError("Enter a valid email address.");
      return;
    }
    if (guests.some((g) => g.email.toLowerCase() === email.toLowerCase())) {
      setGuestError("This guest is already invited.");
      return;
    }
    setGuests((prev) => [...prev, { id: guestSeq++, email }]);
    setGuestInput("");
    setGuestError("");
  };

  const removeGuest = (id: number) => {
    setGuests((prev) => prev.filter((g) => g.id !== id));
  };

  const handleGuestKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter" || e.key === ",") {
      e.preventDefault();
      addGuest();
    }
  };

  const resetForm = () => {
    setName("");
    setStartDate("");
    setStartTime("");
    setEndDate("");
    setEndTime("");
    setLocation("");
    setVirtual(false);
    setMeetingUrl("");
    setCapacity("");
    setDescription("");
    setGuests([]);
    setGuestInput("");
    setFieldErrors({});
  };

  const createEvent = () => {
    const errors: { name?: string; date?: string } = {};
    if (!name.trim()) {
      errors.name = "Event name is required.";
    }
    if (!startDate) {
      errors.date = "Start date is required.";
    }
    setFieldErrors(errors);
    if (Object.keys(errors).length > 0) {
      setNotice("error");
      return;
    }
    setNotice("created");
    resetForm();
  };

  return (
    <div className="space-y-6">
      <PageHeader title="Create Event" description="Schedule a new event and invite your team." />

      {notice && (
        <div
          className={cn(
            "flex items-center gap-2.5 rounded-lg border px-4 py-3 text-sm",
            notice === "error"
              ? "border-destructive/30 bg-destructive/5 text-destructive"
              : "border-success/30 bg-success/5 text-success",
          )}
        >
          <CheckCircle2 className="h-4 w-4 shrink-0" />
          {notice === "created"
            ? "Event created successfully. Invitations are on their way."
            : "Please fix the highlighted fields before creating the event."}
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

      <Card>
        <CardContent className="space-y-6 p-5">
          <div className="grid gap-5 lg:grid-cols-[1fr_300px]">
            <div className="space-y-5">
              <Input
                label="Event name"
                placeholder="Enter event name"
                value={name}
                onChange={(e) => setName(e.target.value)}
                error={fieldErrors.name}
              />

              <div>
                <label className="mb-2 block text-sm font-medium leading-none text-foreground">
                  Event type
                </label>
                <div className="grid gap-2 sm:grid-cols-3">
                  {typeOptions.map((option) => (
                    <button
                      key={option.id}
                      type="button"
                      onClick={() => setType(option.id)}
                      aria-pressed={type === option.id}
                      className={cn(
                        "flex items-center gap-3 rounded-lg border p-3 text-left transition-all",
                        type === option.id
                          ? "border-transparent bg-gradient-to-br text-white shadow-dropdown"
                          : "border-border hover:border-primary/40 hover:bg-muted/50",
                        type === option.id && option.gradient,
                      )}
                    >
                      <span
                        className={cn(
                          "flex h-9 w-9 shrink-0 items-center justify-center rounded-lg",
                          type === option.id ? "bg-white/20" : "bg-muted text-muted-foreground",
                        )}
                      >
                        <option.icon className="h-4.5 w-4.5" />
                      </span>
                      <span className="min-w-0">
                        <span className="block text-sm font-medium leading-tight">
                          {option.label}
                        </span>
                        <span
                          className={cn(
                            "block truncate text-xs",
                            type === option.id ? "text-white/80" : "text-muted-foreground",
                          )}
                        >
                          {option.hint}
                        </span>
                      </span>
                    </button>
                  ))}
                </div>
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <Input
                  label="Start date"
                  type="date"
                  value={startDate}
                  onChange={(e) => setStartDate(e.target.value)}
                  error={fieldErrors.date}
                />
                <Input
                  label="Start time"
                  type="time"
                  value={startTime}
                  onChange={(e) => setStartTime(e.target.value)}
                />
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <Input
                  label="End date"
                  type="date"
                  value={endDate}
                  onChange={(e) => setEndDate(e.target.value)}
                />
                <Input
                  label="End time"
                  type="time"
                  value={endTime}
                  onChange={(e) => setEndTime(e.target.value)}
                />
              </div>

              <div>
                <label className="mb-1.5 block text-sm font-medium leading-none text-foreground">
                  Location
                </label>
                <div className="space-y-2">
                  <Input
                    icon={<MapPin className="h-4 w-4" />}
                    value={location}
                    onChange={(e) => setLocation(e.target.value)}
                    placeholder="Room, building, or address"
                  />
                  <button
                    type="button"
                    onClick={() => setVirtual((prev) => !prev)}
                    className={cn(
                      "flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors",
                      virtual
                        ? "bg-primary/10 text-primary"
                        : "text-muted-foreground hover:bg-muted hover:text-foreground",
                    )}
                  >
                    <span
                      className={cn(
                        "flex h-4 w-4 items-center justify-center rounded-md border",
                        virtual
                          ? "border-primary bg-primary text-primary-foreground"
                          : "border-input",
                      )}
                    >
                      {virtual && <Check className="h-3 w-3" />}
                    </span>
                    <Video className="h-3.5 w-3.5" />
                    Virtual event (include a link)
                  </button>
                  {virtual && (
                    <Input
                      className="animate-fade-in"
                      icon={<Link2 className="h-4 w-4" />}
                      value={meetingUrl}
                      onChange={(e) => setMeetingUrl(e.target.value)}
                      placeholder="https://meet.company.com/join/abc"
                    />
                  )}
                </div>
              </div>

              <div>
                <Textarea
                  label="Description"
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  rows={5}
                  placeholder="Describe the event, agenda, and anything attendees should prepare..."
                />
                <p className="mt-1 text-right text-xs text-muted-foreground">
                  {description.length} characters
                </p>
              </div>

              <div>
                <label className="mb-1.5 block text-sm font-medium leading-none text-foreground">
                  Invite guests
                </label>
                <Input
                  icon={<UserPlus className="h-4 w-4" />}
                  value={guestInput}
                  onChange={(e) => {
                    setGuestInput(e.target.value);
                    if (guestError) setGuestError("");
                  }}
                  onKeyDown={handleGuestKeyDown}
                  onBlur={addGuest}
                  placeholder="Type an email and press Enter"
                />
                {guestError && <p className="mt-1 text-xs text-destructive">{guestError}</p>}
                {!guestError && (
                  <p className="mt-1 text-xs text-muted-foreground">
                    Press Enter or comma to add each guest.
                  </p>
                )}
                {guests.length > 0 && (
                  <div className="mt-3 flex flex-wrap gap-2">
                    {guests.map((g) => (
                      <span
                        key={g.id}
                        className="flex items-center gap-1.5 rounded-full border border-border bg-muted/50 py-1 pl-2.5 pr-1 text-xs font-medium"
                      >
                        {g.email}
                        <button
                          onClick={() => removeGuest(g.id)}
                          aria-label={`Remove ${g.email}`}
                          className="flex h-4 w-4 items-center justify-center rounded-full text-muted-foreground hover:bg-muted hover:text-foreground"
                        >
                          <X className="h-3 w-3" />
                        </button>
                      </span>
                    ))}
                  </div>
                )}
              </div>
            </div>

            <div className="space-y-5">
              <Card className="bg-muted/40">
                <CardContent className="space-y-3 p-5">
                  <p className="text-sm font-semibold">Summary</p>
                  <div className="space-y-2.5">
                    <div className="flex items-center justify-between gap-2">
                      <span className="text-xs text-muted-foreground">Name</span>
                      <span className="max-w-[150px] truncate text-xs font-medium">
                        {name.trim() || "—"}
                      </span>
                    </div>
                    <div className="flex items-center justify-between gap-2">
                      <span className="text-xs text-muted-foreground">Type</span>
                      <Badge variant="outline">
                        {typeOptions.find((t) => t.id === type)?.label}
                      </Badge>
                    </div>
                    <div className="flex items-center justify-between gap-2">
                      <span className="text-xs text-muted-foreground">Date</span>
                      <span className="max-w-[150px] truncate text-xs font-medium">
                        {startDate || "—"}
                        {startTime ? ` · ${startTime}` : ""}
                      </span>
                    </div>
                    <div className="flex items-center justify-between gap-2">
                      <span className="text-xs text-muted-foreground">Mode</span>
                      <span className="text-xs font-medium">
                        {virtual ? "Virtual" : location.trim() ? "In person" : "—"}
                      </span>
                    </div>
                    <div className="flex items-center justify-between gap-2">
                      <span className="text-xs text-muted-foreground">Guests</span>
                      <span className="text-xs font-medium">{guests.length} invited</span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <div>
                <Input
                  label="Max attendees (optional)"
                  type="number"
                  min={1}
                  placeholder="No limit"
                  value={capacity}
                  onChange={(e) => setCapacity(e.target.value)}
                  hint="Leave empty for an unlimited event."
                />
              </div>
            </div>
          </div>

          <div className="flex flex-wrap items-center justify-end gap-2 border-t border-border pt-5">
            <Button variant="outline" onClick={resetForm}>
              Clear
            </Button>
            <Button onClick={createEvent}>
              <CalendarPlus className="h-4 w-4" />
              Create Event
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
