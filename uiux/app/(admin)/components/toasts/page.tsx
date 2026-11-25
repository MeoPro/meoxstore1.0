import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Toasts",
};

const toasts = [
  {
    type: "success",
    icon: "✓",
    title: "Success",
    message: "Your changes have been saved.",
    bg: "bg-success/10",
    border: "border-success/20",
    text: "text-success",
  },
  {
    type: "error",
    icon: "✕",
    title: "Error",
    message: "Something went wrong. Please try again.",
    bg: "bg-destructive/10",
    border: "border-destructive/20",
    text: "text-destructive",
  },
  {
    type: "warning",
    icon: "⚠",
    title: "Warning",
    message: "Your session will expire in 5 minutes.",
    bg: "bg-warning/10",
    border: "border-warning/20",
    text: "text-warning",
  },
  {
    type: "info",
    icon: "ℹ",
    title: "Info",
    message: "A new update is available for download.",
    bg: "bg-info/10",
    border: "border-info/20",
    text: "text-info",
  },
];

export default function ToastsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Toasts" description="Lightweight notification messages." />

      <Card>
        <CardHeader>
          <CardTitle>Toast Variants</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {toasts.map((toast) => (
            <div
              key={toast.type}
              className={`flex items-start gap-3 rounded-xl border px-4 py-3 ${toast.bg} ${toast.border}`}
            >
              <span className={`mt-0.5 text-lg ${toast.text}`}>{toast.icon}</span>
              <div className="flex-1">
                <p className={`text-sm font-medium ${toast.text}`}>{toast.title}</p>
                <p className="text-xs text-muted-foreground">{toast.message}</p>
              </div>
              <button className="text-muted-foreground hover:text-foreground">×</button>
            </div>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Minimal Toast</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="max-w-sm rounded-xl border border-border bg-card px-4 py-3 shadow-dropdown">
            <div className="flex items-center justify-between">
              <p className="text-sm font-medium">File uploaded successfully</p>
              <button className="text-muted-foreground hover:text-foreground text-lg">×</button>
            </div>
            <p className="mt-1 text-xs text-muted-foreground">2 seconds ago</p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
