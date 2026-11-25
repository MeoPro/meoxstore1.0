"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { CalendarDays, ChevronLeft, ChevronRight, Plus, Sparkles, X } from "lucide-react";
import { useEffect, useState } from "react";

type EventColor = "info" | "success" | "warning" | "destructive";

interface CalendarEvent {
  title: string;
  time: string;
  color: EventColor;
}

const initialEventsByDay: Record<number, CalendarEvent[]> = {
  3: [{ title: "Team Standup", time: "10:00", color: "info" }],
  7: [{ title: "Sprint Review", time: "14:00", color: "success" }],
  12: [{ title: "Client Call", time: "11:30", color: "warning" }],
  15: [{ title: "Project Deadline", time: "17:00", color: "destructive" }],
  18: [{ title: "Design Workshop", time: "09:30", color: "info" }],
  22: [{ title: "1:1 Meeting", time: "15:00", color: "success" }],
  25: [{ title: "Demo Day", time: "13:00", color: "warning" }],
  28: [{ title: "Sprint Retro", time: "16:00", color: "info" }],
};

const colorStyles: Record<EventColor, { dot: string; chip: string }> = {
  info: { dot: "bg-info", chip: "border-info/60 bg-info/10 text-info" },
  success: { dot: "bg-success", chip: "border-success/60 bg-success/10 text-success" },
  warning: { dot: "bg-warning", chip: "border-warning/60 bg-warning/10 text-warning" },
  destructive: {
    dot: "bg-destructive",
    chip: "border-destructive/60 bg-destructive/10 text-destructive",
  },
};

const weekDays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

export default function CalendarPage() {
  const [viewDate, setViewDate] = useState<Date | null>(null);
  const [eventsByDay, setEventsByDay] = useState(initialEventsByDay);
  const [modalOpen, setModalOpen] = useState(false);
  const [form, setForm] = useState({ title: "", day: "", time: "", color: "info" as EventColor });

  // Calendar relies on the current date, so render it only after hydration
  // to avoid an SSR/client `new Date()` mismatch.
  useEffect(() => {
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setViewDate(new Date());
  }, []);

  const openModal = () => {
    setForm({ title: "", day: "1", time: "09:00", color: "info" });
    setModalOpen(true);
  };

  if (!viewDate) {
    return (
      <div className="space-y-6">
        <PageHeader
          title="Calendar"
          description="Manage your schedule, meetings, and deadlines in one place."
          actions={
            <Button onClick={openModal}>
              <Plus className="h-4 w-4" />
              New Event
            </Button>
          }
        />
      </div>
    );
  }

  const year = viewDate.getFullYear();
  const month = viewDate.getMonth();
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const today = new Date();
  const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;

  const monthLabel = viewDate.toLocaleString("en-US", { month: "long", year: "numeric" });
  const monthName = viewDate.toLocaleString("en-US", { month: "long" });
  const eventCount = Object.values(eventsByDay).reduce((sum, evs) => sum + evs.length, 0);

  const upcoming = Object.entries(eventsByDay)
    .map(([day, evs]) => evs.map((e) => ({ day: Number(day), ...e })))
    .flat()
    .sort((a, b) => a.day - b.day);

  const moveMonth = (offset: number) => setViewDate(new Date(year, month + offset, 1));
  const goToday = () => setViewDate(new Date());

  const submitEvent = (e: React.FormEvent) => {
    e.preventDefault();
    const day = Number(form.day);
    if (!form.title.trim() || !day || day < 1 || day > 31) return;
    setEventsByDay((prev) => ({
      ...prev,
      [day]: [
        ...(prev[day] ?? []),
        { title: form.title.trim(), time: form.time || "09:00", color: form.color },
      ],
    }));
    setModalOpen(false);
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Calendar"
        description="Manage your schedule, meetings, and deadlines in one place."
        actions={
          <Button onClick={openModal}>
            <Plus className="h-4 w-4" />
            New Event
          </Button>
        }
      />

      <div className="grid gap-6 lg:grid-cols-3">
        {/* Calendar */}
        <div className="lg:col-span-2 rounded-xl border border-border bg-card">
          {/* Header */}
          <div className="flex flex-wrap items-center justify-between gap-3 border-b border-border p-4">
            <div className="flex items-center gap-2">
              <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10">
                <CalendarDays className="h-4 w-4 text-primary" />
              </div>
              <h2 className="text-base font-semibold">{monthLabel}</h2>
            </div>
            <div className="flex items-center gap-2">
              <Button variant="outline" size="sm" onClick={goToday}>
                Today
              </Button>
              <div className="flex items-center gap-1">
                <Button
                  variant="ghost"
                  size="icon"
                  aria-label="Previous month"
                  onClick={() => moveMonth(-1)}
                >
                  <ChevronLeft className="h-4 w-4" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  aria-label="Next month"
                  onClick={() => moveMonth(1)}
                >
                  <ChevronRight className="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>

          {/* Grid */}
          <div className="p-2 sm:p-3">
            <div className="grid grid-cols-7 gap-1">
              {weekDays.map((day) => (
                <div
                  key={day}
                  className="py-1 text-center text-2xs font-semibold uppercase tracking-wider text-muted-foreground"
                >
                  {day}
                </div>
              ))}
              {Array.from({ length: firstDay }).map((_, i) => (
                <div key={`empty-${i}`} className="min-h-[84px] rounded-lg" />
              ))}
              {Array.from({ length: daysInMonth }, (_, i) => i + 1).map((day) => {
                const isToday =
                  isCurrentMonth && today.getDate() === day && today.getMonth() === month;
                const dayEvents = eventsByDay[day] ?? [];
                return (
                  <div
                    key={day}
                    className={
                      "group flex min-h-[84px] flex-col gap-1 rounded-lg border border-border p-1.5 transition-colors hover:border-primary/40 hover:bg-primary/[0.03]"
                    }
                  >
                    <div className="flex items-center justify-between">
                      <span
                        className={
                          "flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold " +
                          (isToday
                            ? "bg-primary text-white"
                            : "text-foreground group-hover:text-primary")
                        }
                      >
                        {day}
                      </span>
                      {dayEvents.length > 0 && (
                        <span className="flex gap-0.5">
                          {dayEvents.slice(0, 2).map((e, i) => (
                            <span
                              key={i}
                              className={"h-1.5 w-1.5 rounded-full " + colorStyles[e.color].dot}
                            />
                          ))}
                        </span>
                      )}
                    </div>
                    <div className="flex flex-col gap-1">
                      {dayEvents.slice(0, 3).map((event, idx) => (
                        <div
                          key={idx}
                          className={
                            "flex items-center gap-1 overflow-hidden rounded-md border-l-2 px-1.5 py-0.5 " +
                            colorStyles[event.color].chip
                          }
                        >
                          <span className="truncate text-2xs font-medium leading-none">
                            {event.title}
                          </span>
                          <span className="ml-auto shrink-0 text-2xs opacity-70">{event.time}</span>
                        </div>
                      ))}
                      {dayEvents.length > 3 && (
                        <span className="px-1 text-2xs font-medium text-muted-foreground">
                          +{dayEvents.length - 3} more
                        </span>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          <div className="rounded-xl border border-border bg-card p-4">
            <div className="flex items-center justify-between">
              <h3 className="text-sm font-semibold">Monthly Summary</h3>
              <Sparkles className="h-4 w-4 text-primary" />
            </div>
            <div className="mt-4 grid grid-cols-2 gap-3">
              <div className="rounded-lg bg-primary/10 p-3">
                <p className="text-xs text-muted-foreground">Events</p>
                <p className="mt-1 text-xl font-bold text-primary">{eventCount}</p>
              </div>
              <div className="rounded-lg bg-success/10 p-3">
                <p className="text-xs text-muted-foreground">Meetings</p>
                <p className="mt-1 text-xl font-bold text-success">6</p>
              </div>
            </div>
          </div>

          <div className="rounded-xl border border-border bg-card">
            <div className="flex items-center justify-between border-b border-border p-4">
              <h3 className="text-sm font-semibold">Upcoming</h3>
              <span className="rounded-md bg-muted px-2 py-0.5 text-2xs font-medium text-muted-foreground">
                {monthName} {year}
              </span>
            </div>
            <div className="divide-y divide-border">
              {upcoming.map((event) => (
                <div key={event.day + event.title} className="flex items-center gap-3 p-3">
                  <div className="flex flex-col items-center rounded-lg border border-border bg-muted/40 px-2.5 py-1.5">
                    <span className="text-sm font-bold leading-none">{event.day}</span>
                    <span className="text-2xs font-medium uppercase text-muted-foreground">
                      {monthName}
                    </span>
                  </div>
                  <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium">{event.title}</p>
                    <p className="text-2xs text-muted-foreground">{event.time}</p>
                  </div>
                  <span
                    className={"h-2 w-2 shrink-0 rounded-full " + colorStyles[event.color].dot}
                  />
                </div>
              ))}
            </div>
          </div>

          <div className="rounded-xl border border-border bg-card p-4">
            <h3 className="text-sm font-semibold">Categories</h3>
            <div className="mt-3 space-y-2">
              {[
                { label: "Meetings", color: "info" as EventColor },
                { label: "Reviews", color: "success" as EventColor },
                { label: "Deadlines", color: "destructive" as EventColor },
                { label: "Calls", color: "warning" as EventColor },
              ].map((c) => (
                <div key={c.label} className="flex items-center gap-2 text-sm">
                  <span className={"h-2.5 w-2.5 rounded-full " + colorStyles[c.color].dot} />
                  <span className="font-medium">{c.label}</span>
                  <span className="ml-auto text-muted-foreground">
                    {upcoming.filter((e) => e.color === c.color).length} this month
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {modalOpen && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
          onClick={() => setModalOpen(false)}
        >
          <div
            className="w-full max-w-md rounded-xl border border-border bg-card p-5 shadow-modal"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="flex items-center justify-between">
              <h3 className="text-base font-semibold">New Event</h3>
              <button
                onClick={() => setModalOpen(false)}
                className="rounded-md p-1 text-muted-foreground transition-colors hover:text-foreground"
                aria-label="Close"
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            <form onSubmit={submitEvent} className="mt-4 space-y-4">
              <Input
                label="Event title"
                placeholder="e.g. Product Review"
                value={form.title}
                onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))}
                autoFocus
                required
              />
              <div className="grid grid-cols-2 gap-4">
                <Input
                  label="Day"
                  type="number"
                  min={1}
                  max={31}
                  value={form.day}
                  onChange={(e) => setForm((f) => ({ ...f, day: e.target.value }))}
                  required
                />
                <Input
                  label="Time"
                  type="time"
                  value={form.time}
                  onChange={(e) => setForm((f) => ({ ...f, time: e.target.value }))}
                  required
                />
              </div>

              <div>
                <span className="text-sm font-medium leading-none text-foreground">Category</span>
                <div className="mt-2 grid grid-cols-4 gap-2">
                  {(["info", "success", "warning", "destructive"] as EventColor[]).map((color) => (
                    <button
                      key={color}
                      type="button"
                      onClick={() => setForm((f) => ({ ...f, color }))}
                      className={
                        "flex flex-col items-center gap-1.5 rounded-lg border p-2.5 transition-colors " +
                        (form.color === color
                          ? "border-primary bg-primary/10"
                          : "border-border hover:border-primary/40")
                      }
                    >
                      <span className={"h-3 w-3 rounded-full " + colorStyles[color].dot} />
                      <span className="text-2xs font-medium capitalize text-muted-foreground">
                        {color}
                      </span>
                    </button>
                  ))}
                </div>
              </div>

              <div className="flex justify-end gap-2 pt-1">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => setModalOpen(false)}
                >
                  Cancel
                </Button>
                <Button type="submit" size="sm">
                  Add Event
                </Button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
