import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Popovers",
};

export default function PopoversPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Popovers"
        description="Contextual overlay content triggered by interaction."
      />

      <Card>
        <CardHeader>
          <CardTitle>Popover Positions</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex flex-wrap items-center justify-center gap-8 py-12">
            <div className="relative">
              <div className="absolute -top-12 left-1/2 -translate-x-1/2 rounded-xl border border-border bg-card px-3 py-1.5 text-xs shadow-dropdown">
                Top Popover
                <div className="absolute -bottom-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 border-b border-r border-border bg-card" />
              </div>
              <div className="rounded-lg border border-border px-4 py-2 text-sm">Top</div>
            </div>

            <div className="relative">
              <div className="absolute -bottom-12 left-1/2 -translate-x-1/2 rounded-xl border border-border bg-card px-3 py-1.5 text-xs shadow-dropdown">
                Bottom Popover
                <div className="absolute -top-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 border-l border-t border-border bg-card" />
              </div>
              <div className="rounded-lg border border-border px-4 py-2 text-sm">Bottom</div>
            </div>

            <div className="relative">
              <div className="absolute -right-32 top-1/2 -translate-y-1/2 rounded-xl border border-border bg-card px-3 py-1.5 text-xs shadow-dropdown">
                Right Popover
                <div className="absolute -left-1 top-1/2 h-2 w-2 -translate-y-1/2 rotate-45 border-b border-l border-border bg-card" />
              </div>
              <div className="rounded-lg border border-border px-4 py-2 text-sm">Right</div>
            </div>

            <div className="relative">
              <div className="absolute -left-32 top-1/2 -translate-y-1/2 rounded-xl border border-border bg-card px-3 py-1.5 text-xs shadow-dropdown">
                Left Popover
                <div className="absolute -right-1 top-1/2 h-2 w-2 -translate-y-1/2 rotate-45 border-r border-t border-border bg-card" />
              </div>
              <div className="rounded-lg border border-border px-4 py-2 text-sm">Left</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
