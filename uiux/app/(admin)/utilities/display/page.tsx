import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Display",
};

export default function DisplayPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Display"
        description="Display utility classes for controlling element rendering."
      />

      <Card>
        <CardHeader>
          <CardTitle>Display Types</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div>
            <p className="mb-1 text-xs font-medium text-muted-foreground">block</p>
            <div className="block rounded bg-primary/20 p-3 text-sm">display: block</div>
          </div>
          <div>
            <p className="mb-1 text-xs font-medium text-muted-foreground">inline</p>
            <span className="inline rounded bg-green-500/20 px-3 py-1 text-sm">inline-1</span>
            <span className="inline rounded bg-green-500/20 px-3 py-1 text-sm">inline-2</span>
          </div>
          <div>
            <p className="mb-1 text-xs font-medium text-muted-foreground">inline-block</p>
            <div className="inline-block rounded bg-blue-500/20 px-3 py-2 text-sm">
              inline-block-1
            </div>
            <div className="inline-block rounded bg-blue-500/20 px-3 py-2 text-sm">
              inline-block-2
            </div>
          </div>
          <div>
            <p className="mb-1 text-xs font-medium text-muted-foreground">flex</p>
            <div className="flex gap-2">
              <div className="rounded bg-purple-500/20 px-3 py-2 text-sm">flex-1</div>
              <div className="rounded bg-purple-500/20 px-3 py-2 text-sm">flex-2</div>
              <div className="rounded bg-purple-500/20 px-3 py-2 text-sm">flex-3</div>
            </div>
          </div>
          <div>
            <p className="mb-1 text-xs font-medium text-muted-foreground">grid</p>
            <div className="grid grid-cols-3 gap-2">
              <div className="rounded bg-yellow-500/20 px-3 py-2 text-center text-sm">1</div>
              <div className="rounded bg-yellow-500/20 px-3 py-2 text-center text-sm">2</div>
              <div className="rounded bg-yellow-500/20 px-3 py-2 text-center text-sm">3</div>
            </div>
          </div>
          <div>
            <p className="mb-1 text-xs font-medium text-muted-foreground">hidden</p>
            <div className="rounded border border-dashed border-border p-3 text-sm text-muted-foreground">
              The element below is hidden: <span className="hidden">You cannot see me</span>
              [hidden element here]
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
