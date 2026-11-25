import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Images",
};

export default function ImagesPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Images" description="Image utility classes and styles." />

      <Card>
        <CardHeader>
          <CardTitle>Shape Variants</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-wrap items-center gap-6">
          <div className="text-center">
            <div className="h-24 w-24 rounded-lg bg-gradient-to-br from-primary/30 to-primary/10" />
            <p className="mt-2 text-xs text-muted-foreground">Rounded</p>
          </div>
          <div className="text-center">
            <div className="h-24 w-24 rounded-full bg-gradient-to-br from-success/30 to-success/10" />
            <p className="mt-2 text-xs text-muted-foreground">Circle</p>
          </div>
          <div className="text-center">
            <div className="h-24 w-24 rounded-xl bg-gradient-to-br from-warning/30 to-warning/10" />
            <p className="mt-2 text-xs text-muted-foreground">Extra Rounded</p>
          </div>
          <div className="text-center">
            <div className="h-24 w-24 bg-gradient-to-br from-info/30 to-info/10" />
            <p className="mt-2 text-xs text-muted-foreground">Square</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Shadow & Border</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-wrap items-center gap-6">
          <div className="text-center">
            <div className="h-24 w-32 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 shadow-dropdown" />
            <p className="mt-2 text-xs text-muted-foreground">Shadow</p>
          </div>
          <div className="text-center">
            <div className="h-24 w-32 rounded-xl border-2 border-border bg-gradient-to-br from-primary/20 to-primary/5" />
            <p className="mt-2 text-xs text-muted-foreground">Bordered</p>
          </div>
          <div className="text-center">
            <div className="h-24 w-32 rounded-xl border-2 border-border bg-gradient-to-br from-primary/20 to-primary/5 shadow-dropdown" />
            <p className="mt-2 text-xs text-muted-foreground">Both</p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
