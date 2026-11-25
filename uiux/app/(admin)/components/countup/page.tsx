import { AnimatedCounter } from "@/components/ui/animated-counter";
import { Card, CardContent } from "@/components/ui/card";
import { PageHeader } from "@/components/layout/page-header";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Count Up",
};

const counters = [
  { label: "Total Users", value: 12450 },
  { label: "Revenue", value: 84230, prefix: "$" },
  { label: "Orders", value: 3672 },
  { label: "Conversion", value: 4.8, decimals: 1, suffix: "%" },
];

export default function CountUpPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Count Up"
        description="Animated counter numbers for displaying statistics."
      />

      <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {counters.map((counter) => (
          <Card key={counter.label}>
            <CardContent className="flex flex-col items-center py-2 text-center">
              <AnimatedCounter
                value={counter.value}
                prefix={counter.prefix}
                suffix={counter.suffix}
                decimals={counter.decimals}
                className="mt-2 text-2xl font-bold"
              />
              <span className="mt-1 text-sm text-muted-foreground">{counter.label}</span>
            </CardContent>
          </Card>
        ))}
      </div>

      <Card>
        <CardContent className="space-y-4">
          <div className="flex items-center justify-between">
            <span className="text-sm font-medium">Goal Progress</span>
            <span className="text-sm font-bold">
              <AnimatedCounter value={7500} /> / 10,000
            </span>
          </div>
          <div className="h-2 w-full rounded-full bg-muted">
            <div className="h-2 w-3/4 rounded-full bg-primary" />
          </div>
          <p className="text-sm text-muted-foreground">75% of monthly target achieved</p>
        </CardContent>
      </Card>
    </div>
  );
}
