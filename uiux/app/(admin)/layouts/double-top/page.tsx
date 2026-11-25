import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Double Top Layout",
};

export default function DoubleTopPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Double Top" description="Two-tier horizontal navigation layout." />
      <Card>
        <CardContent className="flex flex-col items-center justify-center py-12">
          <Badge className="mb-4">New</Badge>
          <h3 className="text-lg font-semibold">Double Top Navigation Layout</h3>
          <p className="mt-2 max-w-md text-center text-sm text-muted-foreground">
            Features a primary navbar on top with a secondary navigation bar below for
            sub-categories. Great for complex applications.
          </p>
          <div className="mt-6 w-full max-w-lg rounded-lg border border-border p-4">
            <div className="h-8 rounded-md bg-primary/10" />
            <div className="mt-2 h-6 rounded-md bg-primary/5" />
            <div className="mt-4 h-48 rounded-md bg-muted" />
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
