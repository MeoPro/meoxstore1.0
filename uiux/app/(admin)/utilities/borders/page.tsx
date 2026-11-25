import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Borders",
};

export default function BordersPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Borders" description="Border styles, radius, and color utilities." />

      <Card>
        <CardHeader>
          <CardTitle>Border Styles</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 md:grid-cols-3">
            <div className="flex h-20 items-center justify-center rounded-lg border-2 border-solid border-border">
              Solid
            </div>
            <div className="flex h-20 items-center justify-center rounded-lg border-2 border-dashed border-border">
              Dashed
            </div>
            <div className="flex h-20 items-center justify-center rounded-lg border-2 border-dotted border-border">
              Dotted
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Border Radius</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 md:grid-cols-4">
            <div className="flex h-20 items-center justify-center rounded-none border border-border bg-muted text-xs">
              rounded-none
            </div>
            <div className="flex h-20 items-center justify-center rounded-md border border-border bg-muted text-xs">
              rounded-md
            </div>
            <div className="flex h-20 items-center justify-center rounded-xl border border-border bg-muted text-xs">
              rounded-xl
            </div>
            <div className="flex h-20 items-center justify-center rounded-full border border-border bg-muted text-xs">
              rounded-full
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Border Colors</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 md:grid-cols-4">
            <div className="flex h-16 items-center justify-center rounded-lg border-2 border-primary text-xs">
              primary
            </div>
            <div className="flex h-16 items-center justify-center rounded-lg border-2 border-green-500 text-xs">
              success
            </div>
            <div className="flex h-16 items-center justify-center rounded-lg border-2 border-yellow-500 text-xs">
              warning
            </div>
            <div className="flex h-16 items-center justify-center rounded-lg border-2 border-destructive text-xs">
              destructive
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
