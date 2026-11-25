import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { CheckCircle2, Clock, Inbox, Star } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Support Reports",
};

const weeklyData = [
  { day: "Mon", tickets: 18, resolved: 15 },
  { day: "Tue", tickets: 22, resolved: 20 },
  { day: "Wed", tickets: 15, resolved: 14 },
  { day: "Thu", tickets: 28, resolved: 22 },
  { day: "Fri", tickets: 20, resolved: 18 },
  { day: "Sat", tickets: 8, resolved: 8 },
  { day: "Sun", tickets: 5, resolved: 5 },
];

const categoryBreakdown = [
  { category: "Authentication", count: 32, percentage: "28%" },
  { category: "Billing", count: 24, percentage: "21%" },
  { category: "Feature Request", count: 20, percentage: "17%" },
  { category: "Bug Report", count: 18, percentage: "16%" },
  { category: "General Inquiry", count: 12, percentage: "10%" },
  { category: "Other", count: 9, percentage: "8%" },
];

export default function SupportReportsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Support Reports"
        description="Analytics and insights for support performance."
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <GradientCard
          title="Total Tickets"
          value="115"
          change={-8.2}
          variant="primary"
          icon={<Inbox className="h-4 w-4" />}
        />
        <GradientCard
          title="Resolution Rate"
          value="92%"
          change={3.5}
          variant="success"
          icon={<CheckCircle2 className="h-4 w-4" />}
        />
        <GradientCard
          title="Avg. First Response"
          value="1.8h"
          change={-22}
          variant="ocean"
          icon={<Clock className="h-4 w-4" />}
        />
        <GradientCard
          title="Customer Satisfaction"
          value="4.6/5"
          change={5}
          variant="sunset"
          icon={<Star className="h-4 w-4" />}
        />
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Weekly Volume</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {weeklyData.map((day) => (
                <div key={day.day} className="flex items-center gap-3">
                  <span className="w-8 text-sm font-medium">{day.day}</span>
                  <div className="flex-1 h-6 bg-muted rounded-full overflow-hidden relative">
                    <div
                      className="h-full bg-primary/20 rounded-full absolute"
                      style={{ width: `${(day.tickets / 30) * 100}%` }}
                    />
                    <div
                      className="h-full bg-primary rounded-full absolute"
                      style={{ width: `${(day.resolved / 30) * 100}%` }}
                    />
                  </div>
                  <span className="text-xs text-muted-foreground w-16 text-right">
                    {day.resolved}/{day.tickets}
                  </span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>By Category</CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <div className="overflow-x-auto">
              <table className="data-table w-full text-sm">
                <thead>
                  <tr className="border-b border-border bg-muted/50">
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Category
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Count</th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Share</th>
                  </tr>
                </thead>
                <tbody>
                  {categoryBreakdown.map((cat) => (
                    <tr
                      key={cat.category}
                      className="border-b border-border last:border-0 hover:bg-muted/50"
                    >
                      <td className="px-4 py-3 font-medium">{cat.category}</td>
                      <td className="px-4 py-3">{cat.count}</td>
                      <td className="px-4 py-3">
                        <Badge variant="outline">{cat.percentage}</Badge>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
