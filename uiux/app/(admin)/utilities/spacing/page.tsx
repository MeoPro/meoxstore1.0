import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Spacing",
};

export default function SpacingPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Spacing"
        description="Margin and padding utility examples with visual indicators."
      />

      <Card>
        <CardHeader>
          <CardTitle>Padding</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {["p-1", "p-2", "p-4", "p-6", "p-8"].map((cls) => (
            <div key={cls} className="rounded border border-dashed border-border bg-primary/5">
              <div className={`${cls} rounded bg-primary/20 text-xs`}>
                <div className="rounded bg-primary/30 px-2 py-1">{cls}</div>
              </div>
            </div>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Margin</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3 rounded border border-dashed border-border p-4">
            {["m-0", "ml-2", "ml-4", "ml-8", "ml-12"].map((cls) => (
              <div key={cls} className={cls}>
                <div className="rounded bg-green-500/20 px-3 py-2 text-xs">{cls}</div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Gap (Flex/Grid)</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {["gap-1", "gap-2", "gap-4", "gap-6"].map((cls) => (
            <div key={cls}>
              <p className="mb-1 text-xs text-muted-foreground">{cls}</p>
              <div className={`flex ${cls}`}>
                <div className="rounded bg-purple-500/20 px-3 py-2 text-xs">A</div>
                <div className="rounded bg-purple-500/20 px-3 py-2 text-xs">B</div>
                <div className="rounded bg-purple-500/20 px-3 py-2 text-xs">C</div>
              </div>
            </div>
          ))}
        </CardContent>
      </Card>
    </div>
  );
}
