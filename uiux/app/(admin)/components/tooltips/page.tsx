import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Tooltips",
};

export default function TooltipsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Tooltips" description="Small text hints displayed on hover." />

      <Card>
        <CardHeader>
          <CardTitle>Tooltip Positions</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex flex-wrap justify-center gap-12 py-16">
            <div className="relative">
              <div className="absolute -top-10 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-foreground px-2.5 py-1 text-xs text-background">
                Tooltip on top
                <div className="absolute -bottom-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-foreground" />
              </div>
              <span className="rounded-lg border border-border px-4 py-2 text-sm">Top</span>
            </div>

            <div className="relative">
              <div className="absolute -right-[120px] top-1/2 -translate-y-1/2 whitespace-nowrap rounded-lg bg-foreground px-2.5 py-1 text-xs text-background">
                Tooltip on right
                <div className="absolute -left-1 top-1/2 h-2 w-2 -translate-y-1/2 rotate-45 bg-foreground" />
              </div>
              <span className="rounded-lg border border-border px-4 py-2 text-sm">Right</span>
            </div>

            <div className="relative">
              <div className="absolute -bottom-10 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-foreground px-2.5 py-1 text-xs text-background">
                Tooltip on bottom
                <div className="absolute -top-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-foreground" />
              </div>
              <span className="rounded-lg border border-border px-4 py-2 text-sm">Bottom</span>
            </div>

            <div className="relative">
              <div className="absolute -left-[110px] top-1/2 -translate-y-1/2 whitespace-nowrap rounded-lg bg-foreground px-2.5 py-1 text-xs text-background">
                Tooltip on left
                <div className="absolute -right-1 top-1/2 h-2 w-2 -translate-y-1/2 rotate-45 bg-foreground" />
              </div>
              <span className="rounded-lg border border-border px-4 py-2 text-sm">Left</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
