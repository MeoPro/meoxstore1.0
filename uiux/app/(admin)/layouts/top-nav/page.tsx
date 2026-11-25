import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Top Nav Layout",
};

export default function TopNavPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Top Nav" description="Horizontal top navigation layout." />
      <Card>
        <CardContent className="flex flex-col items-center justify-center py-12">
          <Badge variant="secondary" className="mb-4">
            Layout Preview
          </Badge>
          <h3 className="text-lg font-semibold">Top Navigation Layout</h3>
          <p className="mt-2 max-w-md text-center text-sm text-muted-foreground">
            The navigation is placed horizontally at the top of the page. Suitable for applications
            with fewer navigation items.
          </p>
          <div className="mt-6 w-full max-w-lg rounded-lg border border-border p-4">
            <div className="h-8 rounded-md bg-primary/10" />
            <div className="mt-4 h-48 rounded-md bg-muted" />
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
