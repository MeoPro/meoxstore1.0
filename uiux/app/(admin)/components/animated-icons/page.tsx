import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Animated Icons",
};

const icons = [
  { label: "Pulse", animation: "animate-pulse", icon: "●" },
  { label: "Spin", animation: "animate-spin", icon: "⟳" },
  { label: "Bounce", animation: "animate-bounce", icon: "▲" },
  { label: "Ping", animation: "animate-ping", icon: "◉" },
  { label: "Pulse", animation: "animate-pulse", icon: "♥" },
  { label: "Spin", animation: "animate-spin", icon: "⚙" },
  { label: "Bounce", animation: "animate-bounce", icon: "⬇" },
  { label: "Ping", animation: "animate-ping", icon: "◎" },
];

export default function AnimatedIconsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Animated Icons"
        description="Icons with CSS animation effects using Tailwind animate classes."
      />

      <Card>
        <CardHeader>
          <CardTitle>Animation Types</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 gap-6 sm:grid-cols-4">
            {icons.map((item, index) => (
              <div
                key={index}
                className="flex flex-col items-center gap-3 rounded-xl border border-border p-6"
              >
                <span className={`text-3xl text-primary ${item.animation}`}>{item.icon}</span>
                <span className="text-xs text-muted-foreground">{item.label}</span>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Icons in Context</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center gap-4">
            <div className="flex items-center gap-2 rounded-lg bg-success/10 px-3 py-2 text-success">
              <span className="animate-pulse">●</span>
              <span className="text-sm">System Online</span>
            </div>
            <div className="flex items-center gap-2 rounded-lg bg-warning/10 px-3 py-2 text-warning">
              <span className="animate-spin">⟳</span>
              <span className="text-sm">Processing</span>
            </div>
            <div className="flex items-center gap-2 rounded-lg bg-destructive/10 px-3 py-2 text-destructive">
              <span className="animate-ping">◉</span>
              <span className="text-sm">Alert Active</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
