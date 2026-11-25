import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Placeholder",
};

export default function PlaceholderPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Placeholder"
        description="Skeleton loading animations for content placeholders."
      />

      <Card>
        <CardHeader>
          <CardTitle>Card Skeleton</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center gap-4">
            <div className="h-12 w-12 animate-pulse rounded-full bg-muted" />
            <div className="flex-1 space-y-2">
              <div className="h-4 w-3/4 animate-pulse rounded-md bg-muted" />
              <div className="h-3 w-1/2 animate-pulse rounded-md bg-muted" />
            </div>
          </div>
          <div className="space-y-2">
            <div className="h-3 w-full animate-pulse rounded-md bg-muted" />
            <div className="h-3 w-5/6 animate-pulse rounded-md bg-muted" />
            <div className="h-3 w-4/6 animate-pulse rounded-md bg-muted" />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Table Skeleton</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            <div className="flex gap-4">
              <div className="h-4 w-1/4 animate-pulse rounded-md bg-muted" />
              <div className="h-4 w-1/4 animate-pulse rounded-md bg-muted" />
              <div className="h-4 w-1/4 animate-pulse rounded-md bg-muted" />
              <div className="h-4 w-1/4 animate-pulse rounded-md bg-muted" />
            </div>
            {Array.from({ length: 4 }).map((_, i) => (
              <div key={i} className="flex gap-4">
                <div className="h-3 w-1/4 animate-pulse rounded-md bg-muted/70" />
                <div className="h-3 w-1/4 animate-pulse rounded-md bg-muted/70" />
                <div className="h-3 w-1/4 animate-pulse rounded-md bg-muted/70" />
                <div className="h-3 w-1/4 animate-pulse rounded-md bg-muted/70" />
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Image Grid Skeleton</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-3 gap-3">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="aspect-square animate-pulse rounded-xl bg-muted" />
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
