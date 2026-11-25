"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Textarea } from "@/components/ui/textarea";
import { cn } from "@/lib/utils";
import {
  ChevronLeft,
  Download,
  FileText,
  Forward,
  Image as ImageIcon,
  Link2,
  Mail,
  MapPin,
  MoreVertical,
  Reply,
  ReplyAll,
  Send,
  Share2,
  Star,
  Trash2,
} from "lucide-react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";

const attachments = [
  { id: 1, name: "q3-report.pdf", meta: "2.4 MB · PDF", icon: FileText },
  { id: 2, name: "revenue-chart.png", meta: "1.1 MB · PNG", icon: ImageIcon },
  { id: 3, name: "executive-summary.pdf", meta: "820 KB · PDF", icon: FileText },
];

type ReplyItem = { id: number; text: string; time: string };

let replySeq = 100;

export default function EmailDetailPage() {
  const router = useRouter();
  const [starred, setStarred] = useState(true);
  const [replyText, setReplyText] = useState("");
  const [replies, setReplies] = useState<ReplyItem[]>([]);
  const [sent, setSent] = useState(false);

  const sendReply = () => {
    const text = replyText.trim();
    if (!text) return;
    const time = new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
    setReplies((prev) => [...prev, { id: replySeq++, text, time }]);
    setReplyText("");
    setSent(true);
    window.setTimeout(() => setSent(false), 2500);
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Email Detail"
        description="View email message."
        actions={
          <div className="flex flex-wrap gap-2">
            <Button variant="outline" size="sm" onClick={() => router.push("/app/email/inbox")}>
              <ChevronLeft className="h-4 w-4" />
              Back
            </Button>
            <Button variant="outline" size="sm" onClick={() => setStarred((v) => !v)}>
              <Star className={cn("h-4 w-4", starred && "fill-warning text-warning")} />
              {starred ? "Starred" : "Star"}
            </Button>
            <Button variant="outline" size="sm" onClick={() => router.push("/app/email/compose")}>
              <Forward className="h-4 w-4" />
              Forward
            </Button>
            <Button variant="destructive" size="sm" onClick={() => router.push("/app/email/inbox")}>
              <Trash2 className="h-4 w-4" />
              Delete
            </Button>
          </div>
        }
      />

      <div className="grid gap-6 xl:grid-cols-[1fr_300px]">
        {/* Email */}
        <div className="space-y-6 min-w-0">
          <Card>
            <div className="flex flex-wrap items-start justify-between gap-4 border-b border-border px-5 py-4 sm:px-6">
              <div className="flex min-w-0 items-center gap-3">
                <Avatar
                  name="Sarah Chen"
                  className="bg-gradient-to-br from-violet-500 to-purple-600"
                />
                <div className="min-w-0">
                  <div className="flex items-center gap-2">
                    <p className="truncate text-sm font-semibold">Sarah Chen</p>
                    <Badge variant="info" className="text-2xs">
                      work
                    </Badge>
                  </div>
                  <p className="truncate text-xs text-muted-foreground">
                    sarah@company.com · to me
                  </p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <span className="text-xs text-muted-foreground">Aug 15, 2026, 10:30 AM</span>
                <Button size="icon" variant="ghost" className="h-8 w-8">
                  <MoreVertical className="h-4 w-4" />
                </Button>
              </div>
            </div>

            <div className="px-5 py-5 sm:px-6">
              <h2 className="text-lg font-semibold">Q3 Report Ready for Review</h2>

              <div className="mt-5 space-y-4 text-sm leading-relaxed text-foreground">
                <p>Hi,</p>
                <p>
                  The Q3 report is now ready for your review. I&apos;ve included all the metrics we
                  discussed in our last meeting, including the new KPIs for the product team.
                </p>
                <p className="font-medium">Key highlights:</p>
                <ul className="list-disc space-y-1 pl-5">
                  <li>Revenue grew 18% compared to Q2</li>
                  <li>Customer acquisition cost decreased by 12%</li>
                  <li>Net Promoter Score improved to 72</li>
                  <li>Team velocity increased by 25%</li>
                </ul>
                <p>
                  Please let me know if you need any changes before the board presentation on
                  Friday.
                </p>
                <p>
                  Best regards,
                  <br />
                  Sarah
                </p>
              </div>

              <div className="mt-6 rounded-lg border border-border p-4 text-xs text-muted-foreground">
                <p>On Aug 12, 2026, you wrote:</p>
                <p className="mt-1 italic">
                  Hey Sarah, could you prepare the Q3 report before Friday? Thanks!
                </p>
              </div>
            </div>
          </Card>

          {/* Attachments */}
          <Card className="px-5 py-4 sm:px-6">
            <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              Attachments · 3
            </h3>
            <div className="mt-3 grid gap-2 sm:grid-cols-3">
              {attachments.map((a) => (
                <div
                  key={a.id}
                  className="flex items-center gap-3 rounded-lg border border-border p-3"
                >
                  <span className="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-muted">
                    <a.icon className="h-4 w-4 text-primary" />
                  </span>
                  <div className="min-w-0 flex-1">
                    <p className="truncate text-xs font-medium">{a.name}</p>
                    <p className="text-2xs text-muted-foreground">{a.meta}</p>
                  </div>
                  <Button size="icon-sm" variant="ghost" aria-label={`Download ${a.name}`}>
                    <Download className="h-3.5 w-3.5" />
                  </Button>
                </div>
              ))}
            </div>
          </Card>

          {/* Replies */}
          {replies.length > 0 && (
            <Card className="space-y-4 px-5 py-4 sm:px-6">
              <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                Replies · {replies.length}
              </h3>
              {replies.map((r) => (
                <div key={r.id} className="rounded-lg border border-border p-4">
                  <div className="flex items-center gap-2">
                    <Avatar
                      name="You"
                      size="sm"
                      className="bg-gradient-to-br from-primary to-purple-500"
                    />
                    <p className="text-xs font-medium">You</p>
                    <span className="ml-auto text-2xs text-muted-foreground">{r.time}</span>
                  </div>
                  <p className="mt-2 text-sm">{r.text}</p>
                </div>
              ))}
            </Card>
          )}

          {/* Reply composer */}
          <Card className="px-5 py-4 sm:px-6">
            <div className="mb-3 flex items-center gap-2">
              <Button size="sm" variant="ghost" onClick={sendReply}>
                <Reply className="h-3.5 w-3.5" />
                Reply
              </Button>
              <Button size="sm" variant="ghost">
                <ReplyAll className="h-3.5 w-3.5" />
                Reply All
              </Button>
              {sent && (
                <span className="ml-auto flex items-center gap-1.5 text-xs font-medium text-success">
                  <Send className="h-3.5 w-3.5" />
                  Reply sent
                </span>
              )}
            </div>
            <Textarea
              aria-label="Reply"
              value={replyText}
              onChange={(e) => setReplyText(e.target.value)}
              rows={4}
              placeholder="Write a reply..."
              className="resize-none py-2.5"
            />
            <div className="mt-3 flex items-center justify-between">
              <span className="text-xs text-muted-foreground">Press Send to post your reply</span>
              <Button onClick={sendReply} disabled={!replyText.trim()}>
                <Send className="h-4 w-4" />
                Send
              </Button>
            </div>
          </Card>
        </div>

        {/* Sender panel */}
        <div className="space-y-6 xl:min-w-0">
          <Card className="px-5 py-4 sm:px-6">
            <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              Sender details
            </h3>
            <div className="mt-4 flex flex-col items-center gap-3">
              <Avatar
                name="Sarah Chen"
                size="lg"
                className="bg-gradient-to-br from-violet-500 to-purple-600"
              />
              <div className="text-center">
                <p className="text-sm font-semibold">Sarah Chen</p>
                <p className="text-xs text-muted-foreground">Product Designer</p>
              </div>
              <div className="mt-2 flex gap-2">
                <Button size="sm" variant="outline">
                  <Mail className="h-3.5 w-3.5" />
                  Email
                </Button>
                <Button size="sm" variant="outline">
                  <Share2 className="h-3.5 w-3.5" />
                  Profile
                </Button>
              </div>
            </div>

            <div className="mt-5 space-y-3 border-t border-border pt-4 text-sm">
              <p className="flex items-center gap-2.5 text-muted-foreground">
                <Mail className="h-4 w-4 shrink-0" />
                <span className="truncate">sarah@company.com</span>
              </p>
              <p className="flex items-center gap-2.5 text-muted-foreground">
                <MapPin className="h-4 w-4 shrink-0" />
                San Francisco, CA
              </p>
              <p className="flex items-center gap-2.5 text-muted-foreground">
                <Link2 className="h-4 w-4 shrink-0" />
                <span className="truncate">sarahchen.design</span>
              </p>
            </div>
          </Card>

          <Card className="px-5 py-4 sm:px-6">
            <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              Thread
            </h3>
            <div className="mt-4 space-y-3">
              <div className="rounded-lg border border-border p-3">
                <p className="text-xs font-medium">Sarah Chen</p>
                <p className="mt-1 text-xs text-muted-foreground">Q3 Report Ready for Review</p>
                <p className="mt-1 text-2xs text-muted-foreground">Aug 15, 2026</p>
              </div>
              <div className="rounded-lg border border-border p-3">
                <p className="text-xs font-medium">You</p>
                <p className="mt-1 text-xs text-muted-foreground">Re: Q3 Report — looks great!</p>
                <p className="mt-1 text-2xs text-muted-foreground">Aug 14, 2026</p>
              </div>
              <Link
                href="/app/email/inbox"
                className="block text-xs font-medium text-primary hover:underline"
              >
                View full conversation →
              </Link>
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
}
