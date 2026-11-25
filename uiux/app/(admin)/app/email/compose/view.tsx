"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { cn } from "@/lib/utils";
import {
  Bold,
  CheckCircle2,
  FileText,
  Image as ImageIcon,
  Italic,
  Link2,
  List,
  Paperclip,
  Quote,
  Save,
  Send,
  Underline,
  X,
} from "lucide-react";
import { useState } from "react";

const sampleFiles = [
  { id: 1, name: "q3-report.pdf", meta: "2.4 MB", icon: FileText },
  { id: 2, name: "banner.png", meta: "1.1 MB", icon: ImageIcon },
  { id: 3, name: "proposal.docx", meta: "420 KB", icon: FileText },
];

const formatTools = [
  { id: "bold", icon: Bold, label: "Bold" },
  { id: "italic", icon: Italic, label: "Italic" },
  { id: "underline", icon: Underline, label: "Underline" },
  { id: "link", icon: Link2, label: "Insert link" },
  { id: "list", icon: List, label: "Bullet list" },
  { id: "quote", icon: Quote, label: "Quote" },
];

let fileSeq = 100;

export default function ComposeEmailPage() {
  const [to, setTo] = useState("");
  const [cc, setCc] = useState("");
  const [subject, setSubject] = useState("");
  const [body, setBody] = useState("");
  const [attachments, setAttachments] = useState<typeof sampleFiles>([]);
  const [notice, setNotice] = useState<"sent" | "saved" | "error" | null>(null);

  const attachIndex = attachments.length % sampleFiles.length;

  const attachFile = () => {
    setAttachments((prev) => [...prev, { ...sampleFiles[attachIndex], id: fileSeq++ }]);
  };

  const removeFile = (id: number) => {
    setAttachments((prev) => prev.filter((f) => f.id !== id));
  };

  const resetForm = () => {
    setTo("");
    setCc("");
    setSubject("");
    setBody("");
    setAttachments([]);
  };

  const sendEmail = () => {
    if (!to.trim()) {
      setNotice("error");
      return;
    }
    setNotice("sent");
    resetForm();
  };

  const saveDraft = () => {
    setNotice("saved");
  };

  const wordCount = body.trim() ? body.trim().split(/\s+/).length : 0;

  return (
    <div className="space-y-6">
      <PageHeader title="Compose Email" description="Write and send a new email." />

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
          {notice === "sent" && "Your email has been sent successfully."}
          {notice === "saved" && "Draft saved to your drafts folder."}
          {notice === "error" && "Please add at least one recipient."}
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
        <div className="space-y-0">
          <div className="flex flex-col gap-1 border-b border-border px-5 py-2.5 sm:flex-row sm:items-center sm:px-6">
            <span className="w-12 shrink-0 text-xs font-medium text-muted-foreground">To</span>
            <input
              value={to}
              onChange={(e) => setTo(e.target.value)}
              placeholder="recipient@example.com"
              className="h-9 w-full flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
            />
          </div>
          <div className="flex flex-col gap-1 border-b border-border px-5 py-2.5 sm:flex-row sm:items-center sm:px-6">
            <span className="w-12 shrink-0 text-xs font-medium text-muted-foreground">CC</span>
            <input
              value={cc}
              onChange={(e) => setCc(e.target.value)}
              placeholder="cc@example.com"
              className="h-9 w-full flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
            />
          </div>
          <div className="flex flex-col gap-1 border-b border-border px-5 py-2.5 sm:flex-row sm:items-center sm:px-6">
            <span className="w-12 shrink-0 text-xs font-medium text-muted-foreground">Subject</span>
            <input
              value={subject}
              onChange={(e) => setSubject(e.target.value)}
              placeholder="Enter email subject"
              className="h-9 w-full flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
            />
          </div>

          <div className="flex flex-wrap items-center gap-0.5 border-b border-border px-3 py-1.5 sm:px-4">
            {formatTools.map((t) => (
              <button
                key={t.id}
                className="flex h-7 w-7 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                aria-label={t.label}
                title={t.label}
              >
                <t.icon className="h-3.5 w-3.5" />
              </button>
            ))}
            <div className="ml-auto flex items-center gap-3 px-1 text-2xs text-muted-foreground">
              <span>{wordCount} words</span>
              <span>{body.length} characters</span>
            </div>
          </div>

          <textarea
            value={body}
            onChange={(e) => setBody(e.target.value)}
            rows={12}
            placeholder="Write your message here..."
            className="w-full resize-y bg-transparent px-5 py-3 text-sm outline-none placeholder:text-muted-foreground sm:px-6"
          />
        </div>

        {attachments.length > 0 && (
          <div className="flex flex-wrap gap-2 border-t border-border px-5 py-4 sm:px-6">
            {attachments.map((f) => (
              <div
                key={f.id}
                className="flex items-center gap-2 rounded-lg border border-border py-1.5 pl-2 pr-1.5"
              >
                <f.icon className="h-4 w-4 shrink-0 text-primary" />
                <span className="text-xs font-medium">{f.name}</span>
                <span className="hidden text-2xs text-muted-foreground sm:inline">{f.meta}</span>
                <button
                  onClick={() => removeFile(f.id)}
                  className="flex h-5 w-5 items-center justify-center rounded-full text-muted-foreground hover:bg-muted hover:text-foreground"
                  aria-label={`Remove ${f.name}`}
                >
                  <X className="h-3 w-3" />
                </button>
              </div>
            ))}
          </div>
        )}

        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-border px-5 py-4 sm:px-6">
          <Button variant="outline" onClick={attachFile}>
            <Paperclip className="h-4 w-4" />
            Attach Files
          </Button>
          <div className="flex flex-wrap gap-2">
            <Button variant="outline" onClick={saveDraft}>
              <Save className="h-4 w-4" />
              Save Draft
            </Button>
            <Button onClick={sendEmail}>
              <Send className="h-4 w-4" />
              Send Email
            </Button>
          </div>
        </div>
      </Card>
    </div>
  );
}
