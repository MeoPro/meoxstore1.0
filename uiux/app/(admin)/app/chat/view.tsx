"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { cn } from "@/lib/utils";
import {
  Check,
  CheckCheck,
  ChevronRight,
  Download,
  FileText,
  Image as ImageIcon,
  MoreVertical,
  Paperclip,
  Phone,
  PhoneCall,
  Search,
  Send,
  Smile,
  Video,
  X,
} from "lucide-react";
import { useEffect, useMemo, useRef, useState } from "react";

type Message = {
  id: number;
  sender: string;
  text: string;
  time: string;
  day: string;
  isMine: boolean;
};

type Conversation = {
  id: number;
  name: string;
  role: string;
  color: string;
  online: boolean;
  lastActive: string;
};

const conversations: Conversation[] = [
  {
    id: 1,
    name: "Sarah Chen",
    role: "Product Designer",
    color: "from-violet-500 to-purple-600",
    online: true,
    lastActive: "Online",
  },
  {
    id: 2,
    name: "Mike Johnson",
    role: "Frontend Engineer",
    color: "from-sky-500 to-blue-600",
    online: true,
    lastActive: "Online",
  },
  {
    id: 3,
    name: "Emma Davis",
    role: "Marketing Lead",
    color: "from-pink-500 to-rose-600",
    online: false,
    lastActive: "12:40 PM",
  },
  {
    id: 4,
    name: "James Wilson",
    role: "Backend Engineer",
    color: "from-emerald-500 to-green-600",
    online: false,
    lastActive: "Yesterday",
  },
  {
    id: 5,
    name: "Lisa Park",
    role: "QA Analyst",
    color: "from-amber-500 to-orange-600",
    online: true,
    lastActive: "Online",
  },
  {
    id: 6,
    name: "David Kim",
    role: "DevOps Engineer",
    color: "from-cyan-500 to-teal-600",
    online: false,
    lastActive: "11:20 AM",
  },
  {
    id: 7,
    name: "Nina Gupta",
    role: "Data Scientist",
    color: "from-indigo-500 to-blue-700",
    online: true,
    lastActive: "Online",
  },
];

type Thread = { unread: number; typing: boolean; messages: Message[] };

const initialThreads: Record<number, Thread> = {
  1: {
    unread: 3,
    typing: false,
    messages: [
      {
        id: 1,
        sender: "Sarah Chen",
        text: "Hey! How's the project going?",
        time: "10:30 AM",
        day: "Today",
        isMine: false,
      },
      {
        id: 2,
        sender: "You",
        text: "Going well! Almost done with the new feature.",
        time: "10:32 AM",
        day: "Today",
        isMine: true,
      },
      {
        id: 3,
        sender: "Sarah Chen",
        text: "That's great! Can you share a preview?",
        time: "10:33 AM",
        day: "Today",
        isMine: false,
      },
      {
        id: 4,
        sender: "You",
        text: "Sure, I'll send that over in a few minutes.",
        time: "10:35 AM",
        day: "Today",
        isMine: true,
      },
      {
        id: 5,
        sender: "Sarah Chen",
        text: "Perfect. Also, did you see the latest design mockups we uploaded?",
        time: "10:36 AM",
        day: "Today",
        isMine: false,
      },
    ],
  },
  2: {
    unread: 0,
    typing: false,
    messages: [
      {
        id: 1,
        sender: "You",
        text: "Could you review the new branch?",
        time: "09:05 AM",
        day: "Today",
        isMine: true,
      },
      {
        id: 2,
        sender: "Mike Johnson",
        text: "On it. I'll push the merge request now.",
        time: "09:07 AM",
        day: "Today",
        isMine: false,
      },
    ],
  },
  3: {
    unread: 0,
    typing: false,
    messages: [
      {
        id: 1,
        sender: "Emma Davis",
        text: "The landing page copy is ready for review.",
        time: "4:15 PM",
        day: "Yesterday",
        isMine: false,
      },
      {
        id: 2,
        sender: "You",
        text: "Nice, I'll take a look tonight.",
        time: "4:20 PM",
        day: "Yesterday",
        isMine: true,
      },
    ],
  },
  4: {
    unread: 0,
    typing: false,
    messages: [
      {
        id: 1,
        sender: "James Wilson",
        text: "Metrics endpoint deployed to staging.",
        time: "3:10 PM",
        day: "Yesterday",
        isMine: false,
      },
    ],
  },
  5: {
    unread: 1,
    typing: false,
    messages: [
      {
        id: 1,
        sender: "Lisa Park",
        text: "All test cases pass on the latest build!",
        time: "11:00 AM",
        day: "Today",
        isMine: false,
      },
    ],
  },
  6: {
    unread: 0,
    typing: false,
    messages: [
      {
        id: 1,
        sender: "David Kim",
        text: "CI pipeline is green again.",
        time: "10:52 AM",
        day: "Today",
        isMine: false,
      },
    ],
  },
  7: {
    unread: 0,
    typing: false,
    messages: [
      {
        id: 1,
        sender: "Nina Gupta",
        text: "Here's the Q3 metrics report.",
        time: "09:30 AM",
        day: "Today",
        isMine: false,
      },
    ],
  },
};

const autoReplies = [
  "Sounds good — I'll take a look and get back to you.",
  "Perfect, thanks for the quick update!",
  "Got it. Let's circle back on this tomorrow.",
  "Nice, that works for me.",
  "I'll confirm with the team and follow up shortly.",
];

const sharedFiles = [
  { id: 1, name: "dashboard-mockups.sketch", meta: "6.2 MB · PDF", icon: FileText },
  { id: 2, name: "roadmap-2026.pdf", meta: "2.1 MB · PDF", icon: ImageIcon },
  { id: 3, name: "brand-guidelines.pdf", meta: "4.8 MB · PDF", icon: FileText },
];

let nextId = 1000;

function formatTime(date: Date) {
  return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

export default function ChatPage() {
  const [draft, setDraft] = useState("");
  const [query, setQuery] = useState("");
  const [filter, setFilter] = useState<"all" | "unread">("all");
  const [activeId, setActiveId] = useState(1);
  const [listOpen, setListOpen] = useState(false);
  const [detailsOpen, setDetailsOpen] = useState(false);
  const [threads, setThreads] = useState(initialThreads);
  const scrollRef = useRef<HTMLDivElement>(null);

  const active = conversations.find((c) => c.id === activeId)!;
  const thread = threads[activeId];
  const activeMessages = thread.messages;
  const isTyping = thread.typing;

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    return conversations.filter((c) => {
      const text =
        `${c.name} ${c.role} ${threads[c.id]?.messages.at(-1)?.text ?? ""}`.toLowerCase();
      const matchesQuery = !q || text.includes(q);
      const matchesFilter = filter === "all" || (threads[c.id]?.unread ?? 0) > 0;
      return matchesQuery && matchesFilter;
    });
  }, [query, filter, threads]);

  useEffect(() => {
    scrollRef.current?.scrollTo({ top: scrollRef.current.scrollHeight, behavior: "smooth" });
  }, [activeMessages, isTyping]);

  const openThread = (id: number) => {
    setActiveId(id);
    setListOpen(false);
    if (threads[id].unread > 0) {
      setThreads((prev) => ({ ...prev, [id]: { ...prev[id], unread: 0 } }));
    }
  };

  const sendMessage = () => {
    const text = draft.trim();
    if (!text) return;
    const time = formatTime(new Date());
    setThreads((prev) => ({
      ...prev,
      [activeId]: {
        ...prev[activeId],
        unread: 0,
        messages: [
          ...prev[activeId].messages,
          { id: nextId++, sender: "You", text, time, day: "Today", isMine: true },
        ],
      },
    }));
    setDraft("");

    setThreads((prev) => ({ ...prev, [activeId]: { ...prev[activeId], typing: true } }));
    window.setTimeout(() => {
      const reply = autoReplies[Math.floor(Math.random() * autoReplies.length)];
      setThreads((prev) => {
        const replyMessage = {
          id: nextId++,
          sender: active.name,
          text: reply,
          time: formatTime(new Date()),
          day: "Today",
          isMine: false,
        };
        return {
          ...prev,
          [activeId]: {
            ...prev[activeId],
            typing: false,
            messages: [...prev[activeId].messages, replyMessage],
          },
        };
      });
    }, 1600);
  };

  return (
    <div className="flex h-full min-h-0 flex-col space-y-6">
      <PageHeader title="Chat" description="Real-time messaging with your team." />

      <Card className="relative flex min-h-0 flex-1 overflow-hidden">
        {/* Conversation list */}
        <aside
          className={cn(
            "w-72 shrink-0 flex-col border-r border-border bg-muted/20 lg:flex",
            listOpen ? "flex" : "hidden",
          )}
        >
          <div className="border-b border-border p-3">
            <div className="relative">
              <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <input
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="Search conversations..."
                className="h-9 w-full rounded-lg border border-input bg-transparent pl-9 pr-3 text-sm shadow-card transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
            </div>
            <div className="mt-3 flex items-center justify-between gap-2">
              <h2 className="text-sm font-semibold">Inbox</h2>
              <div className="flex gap-1 rounded-lg bg-muted p-0.5">
                <button
                  onClick={() => setFilter("all")}
                  className={cn(
                    "rounded-md px-2.5 py-1 text-xs font-medium transition-colors",
                    filter === "all"
                      ? "bg-card text-foreground shadow-card"
                      : "text-muted-foreground hover:text-foreground",
                  )}
                >
                  All
                </button>
                <button
                  onClick={() => setFilter("unread")}
                  className={cn(
                    "rounded-md px-2.5 py-1 text-xs font-medium transition-colors",
                    filter === "unread"
                      ? "bg-card text-foreground shadow-card"
                      : "text-muted-foreground hover:text-foreground",
                  )}
                >
                  Unread
                </button>
              </div>
            </div>
          </div>

          <div className="flex-1 overflow-y-auto">
            {filtered.length === 0 && (
              <p className="px-4 py-8 text-center text-xs text-muted-foreground">
                No conversations found.
              </p>
            )}
            {filtered.map((contact) => {
              const t = threads[contact.id];
              const last = t.messages.at(-1);
              return (
                <button
                  key={contact.id}
                  onClick={() => openThread(contact.id)}
                  className={cn(
                    "flex w-full items-center gap-3 border-b border-border px-3 py-3 text-left transition-colors hover:bg-muted/60",
                    activeId === contact.id && "bg-primary/5 hover:bg-primary/5",
                  )}
                >
                  <div className="relative shrink-0">
                    <Avatar
                      name={contact.name}
                      size="md"
                      className={cn("bg-gradient-to-br", contact.color)}
                    />
                    <span
                      className={cn(
                        "absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full ring-2 ring-card",
                        contact.online ? "bg-green-500" : "bg-muted-foreground/40",
                      )}
                    />
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center justify-between gap-2">
                      <p className="truncate text-sm font-medium">{contact.name}</p>
                      {t.unread > 0 && (
                        <span className="grid h-4 min-w-4 place-items-center rounded-full bg-primary px-1 text-2xs font-semibold text-primary-foreground">
                          {t.unread}
                        </span>
                      )}
                    </div>
                    <p className="mt-0.5 truncate text-xs text-muted-foreground">
                      {last?.text ?? "Start the conversation"}
                    </p>
                  </div>
                </button>
              );
            })}
          </div>
        </aside>

        {/* Chat window */}
        <section className={cn("min-w-0 flex-1 lg:flex", listOpen ? "hidden" : "flex flex-col")}>
          {/* Header */}
          <header className="flex items-center gap-3 border-b border-border px-4 py-3">
            <button
              onClick={() => setListOpen(true)}
              className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground hover:bg-muted md:hidden"
            >
              <ChevronRight className="h-4 w-4 -scale-x-100" />
            </button>
            <Avatar
              name={active.name}
              size="md"
              className={cn("bg-gradient-to-br", active.color)}
            />
            <div className="min-w-0 flex-1">
              <p className="truncate text-sm font-semibold">{active.name}</p>
              <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
                {active.online && <span className="h-1.5 w-1.5 rounded-full bg-green-500" />}
                <span>{active.lastActive}</span>
              </p>
            </div>
            <div className="flex items-center gap-1">
              <Button size="icon" variant="ghost" aria-label="Call">
                <Phone className="h-4 w-4" />
              </Button>
              <Button size="icon" variant="ghost" aria-label="Video call">
                <Video className="h-4 w-4" />
              </Button>
              <Button
                size="icon"
                variant="ghost"
                aria-label="Toggle details"
                onClick={() => setDetailsOpen((v) => !v)}
                className="hidden sm:inline-flex"
              >
                <MoreVertical className="h-4 w-4" />
              </Button>
            </div>
          </header>

          {/* Messages */}
          <div
            className="flex-1 space-y-4 overflow-y-auto bg-gradient-to-b from-muted/40 to-transparent px-4 py-4"
            ref={scrollRef}
          >
            {thread.messages.map((msg, i) => {
              const isDayStart = i === 0 || thread.messages[i - 1].day !== msg.day;
              const isGroupEnd =
                i === thread.messages.length - 1 || thread.messages[i + 1].isMine !== msg.isMine;
              return (
                <div key={msg.id} className="flex flex-col gap-1.5">
                  {isDayStart && (
                    <div className="my-2 flex justify-center">
                      <span className="rounded-full bg-card px-3 py-1 text-2xs font-medium text-muted-foreground shadow-card">
                        {msg.day}
                      </span>
                    </div>
                  )}
                  <div className={cn("flex gap-2.5", msg.isMine && "flex-row-reverse")}>
                    {!msg.isMine && (
                      <div className={cn("self-end", isGroupEnd ? "" : "opacity-0")}>
                        <Avatar
                          name={active.name}
                          size="sm"
                          className={cn("bg-gradient-to-br", active.color)}
                        />
                      </div>
                    )}
                    <div
                      className={cn(
                        "max-w-[75%] sm:max-w-[65%]",
                        msg.isMine ? "text-right" : "text-left",
                      )}
                    >
                      <div
                        className={cn(
                          "relative inline-block rounded-2xl px-3.5 py-2 text-left text-sm leading-relaxed shadow-card",
                          msg.isMine
                            ? "rounded-br-md bg-primary text-primary-foreground"
                            : "rounded-bl-md bg-card",
                        )}
                      >
                        <p className="whitespace-pre-wrap">{msg.text}</p>
                        <span
                          className={cn(
                            "mt-0.5 flex items-center justify-end gap-1 text-2xs",
                            msg.isMine ? "text-primary-foreground/70" : "text-muted-foreground",
                          )}
                        >
                          {msg.time}
                          {msg.isMine &&
                            (i === thread.messages.length - 1 ? (
                              <>
                                <CheckCheck className="h-3 w-3" />
                                Read
                              </>
                            ) : (
                              <Check className="h-3 w-3" />
                            ))}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}

            {thread.typing && (
              <div className="flex gap-2.5">
                <Avatar
                  name={active.name}
                  size="sm"
                  className={cn("bg-gradient-to-br self-end", active.color)}
                />
                <div className="flex items-center gap-1 rounded-2xl rounded-bl-md bg-card px-4 py-3 shadow-card">
                  {[0, 1, 2].map((d) => (
                    <span
                      key={d}
                      className="h-1.5 w-1.5 animate-bounce rounded-full bg-muted-foreground"
                      style={{ animationDelay: `${d * 0.15}s` }}
                    />
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Composer */}
          <div className="border-t border-border p-3">
            <div className="flex items-end gap-2">
              <Button size="icon" variant="ghost" aria-label="Attach">
                <Paperclip className="h-4 w-4" />
              </Button>
              <Button size="icon" variant="ghost" aria-label="Emoji">
                <Smile className="h-4 w-4" />
              </Button>
              <textarea
                value={draft}
                onChange={(e) => setDraft(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === "Enter" && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                  }
                }}
                rows={1}
                placeholder="Type a message..."
                className="max-h-36 min-h-10 flex-1 resize-none rounded-lg border border-input bg-transparent px-3 py-2.5 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
              <Button size="icon" onClick={sendMessage} aria-label="Send message">
                <Send className="h-4 w-4" />
              </Button>
            </div>
            <p className="mt-1.5 text-2xs text-muted-foreground">
              Press Enter to send · Shift+Enter for a new line
            </p>
          </div>
        </section>

        {/* Details panel */}
        {detailsOpen && (
          <aside className="hidden w-72 shrink-0 flex-col overflow-y-auto border-l border-border bg-card lg:flex">
            <div className="flex items-center justify-between border-b border-border px-4 py-3">
              <h3 className="text-sm font-semibold">Details</h3>
              <Button
                size="icon"
                variant="ghost"
                onClick={() => setDetailsOpen(false)}
                aria-label="Close details"
              >
                <X className="h-4 w-4" />
              </Button>
            </div>

            <div className="flex flex-col items-center gap-3 px-4 py-6">
              <Avatar
                name={active.name}
                size="xl"
                className={cn("bg-gradient-to-br", active.color)}
              />
              <div className="text-center">
                <p className="text-sm font-semibold">{active.name}</p>
                <p className="text-xs text-muted-foreground">
                  {active.role} · {active.online ? "Online" : active.lastActive}
                </p>
              </div>
              <div className="mt-1 flex gap-2">
                <Button size="sm" variant="outline">
                  <PhoneCall className="h-3.5 w-3.5" />
                  Call
                </Button>
                <Button size="sm" variant="outline">
                  <Video className="h-3.5 w-3.5" />
                  Video
                </Button>
              </div>
            </div>

            <div className="border-t border-border px-4 py-4">
              <h4 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                Shared files
              </h4>
              <div className="mt-3 space-y-2">
                {sharedFiles.map((f) => (
                  <div
                    key={f.id}
                    className="flex items-center gap-3 rounded-lg border border-border p-2.5"
                  >
                    <span className="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-muted">
                      <f.icon className="h-4 w-4 text-primary" />
                    </span>
                    <div className="min-w-0 flex-1">
                      <p className="truncate text-xs font-medium">{f.name}</p>
                      <p className="text-2xs text-muted-foreground">{f.meta}</p>
                    </div>
                    <Button size="icon-sm" variant="ghost" aria-label="Download">
                      <Download className="h-3.5 w-3.5" />
                    </Button>
                  </div>
                ))}
              </div>
            </div>
          </aside>
        )}
      </Card>
    </div>
  );
}
