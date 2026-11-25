import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Spinners",
};

export default function SpinnersPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Spinners" description="Loading indicators and spinner animations." />

      <Card>
        <CardHeader>
          <CardTitle>Spinner Variants</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-wrap items-center gap-8">
          <div className="text-center">
            <div className="h-8 w-8 animate-spin rounded-full border-4 border-muted border-t-primary" />
            <p className="mt-2 text-xs text-muted-foreground">Border</p>
          </div>
          <div className="text-center">
            <div className="h-8 w-8 animate-spin rounded-full border-4 border-muted border-t-success" />
            <p className="mt-2 text-xs text-muted-foreground">Success</p>
          </div>
          <div className="text-center">
            <div className="h-8 w-8 animate-spin rounded-full border-4 border-muted border-t-warning" />
            <p className="mt-2 text-xs text-muted-foreground">Warning</p>
          </div>
          <div className="text-center">
            <div className="h-8 w-8 animate-spin rounded-full border-4 border-muted border-t-destructive" />
            <p className="mt-2 text-xs text-muted-foreground">Danger</p>
          </div>
          <div className="text-center">
            <div className="h-8 w-8 animate-spin rounded-full border-4 border-muted border-t-info" />
            <p className="mt-2 text-xs text-muted-foreground">Info</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Sizes</CardTitle>
        </CardHeader>
        <CardContent className="flex items-end gap-6">
          <div className="h-4 w-4 animate-spin rounded-full border-2 border-muted border-t-primary" />
          <div className="h-6 w-6 animate-spin rounded-full border-2 border-muted border-t-primary" />
          <div className="h-8 w-8 animate-spin rounded-full border-4 border-muted border-t-primary" />
          <div className="h-12 w-12 animate-spin rounded-full border-4 border-muted border-t-primary" />
          <div className="h-16 w-16 animate-spin rounded-full border-4 border-muted border-t-primary" />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Dots Spinner</CardTitle>
        </CardHeader>
        <CardContent className="flex items-center gap-1">
          <div className="h-2.5 w-2.5 animate-bounce rounded-full bg-primary [animation-delay:-0.3s]" />
          <div className="h-2.5 w-2.5 animate-bounce rounded-full bg-primary [animation-delay:-0.15s]" />
          <div className="h-2.5 w-2.5 animate-bounce rounded-full bg-primary" />
        </CardContent>
      </Card>
    </div>
  );
}
