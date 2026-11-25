import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Stretched Link",
};

export default function StretchedLinkPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Stretched Link"
        description="Make an entire card or container clickable via a stretched link."
      />

      <div className="grid gap-6 md:grid-cols-2">
        <Card className="relative overflow-hidden">
          <CardHeader>
            <CardTitle>Card with Stretched Link</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="mb-4 text-sm text-muted-foreground">
              The entire card is clickable because the link inside stretches to cover the full
              container using absolute positioning.
            </p>
            <a
              href="#"
              className="text-primary after:absolute after:inset-0 after:content-[''] hover:underline"
            >
              Go somewhere →
            </a>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden">
          <div className="h-32 bg-gradient-to-br from-primary/20 to-blue-500/20" />
          <CardHeader>
            <CardTitle>Image Card Example</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="mb-4 text-sm text-muted-foreground">
              This card has a header image area and the link stretches over everything.
            </p>
            <a
              href="#"
              className="text-primary after:absolute after:inset-0 after:content-[''] hover:underline"
            >
              Read more →
            </a>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>How It Works</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            Add <code className="rounded bg-muted px-1 py-0.5 text-xs">position: relative</code> to
            the container and use an{" "}
            <code className="rounded bg-muted px-1 py-0.5 text-xs">::after</code> pseudo-element on
            the link with{" "}
            <code className="rounded bg-muted px-1 py-0.5 text-xs">absolute inset-0</code> to cover
            the area.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
