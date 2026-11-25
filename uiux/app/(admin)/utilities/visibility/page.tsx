import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Visibility",
};

export default function VisibilityPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Visibility"
        description="Control element visibility without affecting layout."
      />

      <Card>
        <CardHeader>
          <CardTitle>Visible vs Invisible</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div>
            <p className="mb-2 text-xs font-medium text-muted-foreground">Visible (default)</p>
            <div className="flex gap-2">
              <div className="rounded bg-primary/20 px-4 py-3 text-sm">Box 1</div>
              <div className="visible rounded bg-green-500/20 px-4 py-3 text-sm">
                Box 2 (visible)
              </div>
              <div className="rounded bg-primary/20 px-4 py-3 text-sm">Box 3</div>
            </div>
          </div>
          <div>
            <p className="mb-2 text-xs font-medium text-muted-foreground">
              Invisible (still takes space)
            </p>
            <div className="flex gap-2">
              <div className="rounded bg-primary/20 px-4 py-3 text-sm">Box 1</div>
              <div className="invisible rounded bg-green-500/20 px-4 py-3 text-sm">
                Box 2 (invisible)
              </div>
              <div className="rounded bg-primary/20 px-4 py-3 text-sm">Box 3</div>
            </div>
          </div>
          <div>
            <p className="mb-2 text-xs font-medium text-muted-foreground">
              Hidden (removed from layout)
            </p>
            <div className="flex gap-2">
              <div className="rounded bg-primary/20 px-4 py-3 text-sm">Box 1</div>
              <div className="hidden rounded bg-green-500/20 px-4 py-3 text-sm">Box 2 (hidden)</div>
              <div className="rounded bg-primary/20 px-4 py-3 text-sm">Box 3</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Key Difference</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            <code className="rounded bg-muted px-1 py-0.5 text-xs">invisible</code> hides the
            element visually but it still occupies space in the layout.{" "}
            <code className="rounded bg-muted px-1 py-0.5 text-xs">hidden</code> removes the element
            from the document flow entirely, collapsing the space it would have taken.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
