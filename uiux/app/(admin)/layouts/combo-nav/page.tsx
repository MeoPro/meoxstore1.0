import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Combo Nav Layout",
};

export default function ComboNavPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Combo Nav"
        description="Combined horizontal + vertical navigation layout."
      />
      <Card>
        <CardContent className="flex flex-col items-center justify-center py-12">
          <Badge className="mb-4">New</Badge>
          <h3 className="text-lg font-semibold">Combo Navigation Layout</h3>
          <p className="mt-2 max-w-md text-center text-sm text-muted-foreground">
            Combines a horizontal top bar with a slim vertical sidebar. Offers the best of both
            worlds for navigation-heavy applications.
          </p>
          <div className="mt-6 w-full max-w-lg rounded-lg border border-border p-4">
            <div className="h-8 rounded-md bg-primary/10" />
            <div className="mt-2 flex gap-2">
              <div className="h-48 w-12 rounded-md bg-primary/10" />
              <div className="h-48 flex-1 rounded-md bg-muted" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
