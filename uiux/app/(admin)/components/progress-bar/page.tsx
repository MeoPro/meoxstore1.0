import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Progress Bar",
};

const bars = [
  { label: "Primary", width: "75%", color: "bg-primary" },
  { label: "Success", width: "60%", color: "bg-success" },
  { label: "Warning", width: "45%", color: "bg-warning" },
  { label: "Error", width: "30%", color: "bg-destructive" },
  { label: "Info", width: "90%", color: "bg-info" },
];

export default function ProgressBarPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Progress Bar"
        description="Visual indicators of progress and completion."
      />

      <Card>
        <CardHeader>
          <CardTitle>Color Variants</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {bars.map((bar) => (
            <div key={bar.label}>
              <div className="mb-1 flex items-center justify-between text-sm">
                <span className="text-muted-foreground">{bar.label}</span>
                <span className="font-medium">{bar.width}</span>
              </div>
              <div className="h-2 w-full rounded-full bg-muted">
                <div className={`h-2 rounded-full ${bar.color}`} style={{ width: bar.width }} />
              </div>
            </div>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Sizes</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div>
            <p className="mb-1 text-sm text-muted-foreground">Small (2px)</p>
            <div className="h-0.5 w-full rounded-full bg-muted">
              <div className="h-0.5 w-3/4 rounded-full bg-primary" />
            </div>
          </div>
          <div>
            <p className="mb-1 text-sm text-muted-foreground">Default (8px)</p>
            <div className="h-2 w-full rounded-full bg-muted">
              <div className="h-2 w-3/4 rounded-full bg-primary" />
            </div>
          </div>
          <div>
            <p className="mb-1 text-sm text-muted-foreground">Large (16px)</p>
            <div className="h-4 w-full rounded-full bg-muted">
              <div className="h-4 w-3/4 rounded-full bg-primary" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
