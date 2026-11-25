import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Figures",
};

export default function FiguresPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Figures"
        description="Images with captions using the figure element pattern."
      />

      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Card>
          <CardContent>
            <figure>
              <div className="h-40 w-full rounded-xl bg-gradient-to-br from-primary/20 to-primary/5" />
              <figcaption className="mt-3 text-sm text-muted-foreground">
                A caption for the above image. This text describes the visual content.
              </figcaption>
            </figure>
          </CardContent>
        </Card>

        <Card>
          <CardContent>
            <figure className="text-center">
              <div className="h-40 w-full rounded-xl bg-gradient-to-br from-success/20 to-success/5" />
              <figcaption className="mt-3 text-sm text-muted-foreground">
                Centered caption below a figure element.
              </figcaption>
            </figure>
          </CardContent>
        </Card>

        <Card>
          <CardContent>
            <figure>
              <div className="h-40 w-full rounded-xl bg-gradient-to-br from-warning/20 to-warning/5" />
              <figcaption className="mt-3 text-right text-sm italic text-muted-foreground">
                — Right-aligned caption with attribution
              </figcaption>
            </figure>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Figure with Border</CardTitle>
        </CardHeader>
        <CardContent>
          <figure className="rounded-xl border border-border p-4">
            <div className="h-48 w-full rounded-lg bg-gradient-to-br from-info/20 to-info/5" />
            <figcaption className="mt-3 text-sm text-muted-foreground">
              A bordered figure container. Useful for distinguishing figures from surrounding
              content.
            </figcaption>
          </figure>
        </CardContent>
      </Card>
    </div>
  );
}
