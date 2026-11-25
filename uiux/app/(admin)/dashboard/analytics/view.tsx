"use client";

import { PageHeader } from "@/components/layout/page-header";
import { CircularProgress } from "@/components/ui/animated-progress";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { Clock, Eye, MousePointerClick, Users } from "lucide-react";
import {
  Bar,
  BarChart,
  CartesianGrid,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { axisTick, chartColors, tooltipProps } from "@/lib/chart-theme";

const dailyVisitsData = [
  { day: "Jan 1", visits: 3200 },
  { day: "Jan 3", visits: 4100 },
  { day: "Jan 6", visits: 3800 },
  { day: "Jan 9", visits: 5200 },
  { day: "Jan 12", visits: 4600 },
  { day: "Jan 15", visits: 6100 },
  { day: "Jan 18", visits: 5400 },
  { day: "Jan 21", visits: 7200 },
  { day: "Jan 24", visits: 6800 },
  { day: "Jan 27", visits: 5900 },
  { day: "Jan 29", visits: 7400 },
  { day: "Jan 30", visits: 8100 },
];

const topPagesData = [
  { page: "/home", views: 42300 },
  { page: "/products", views: 28100 },
  { page: "/pricing", views: 19400 },
  { page: "/blog", views: 15800 },
  { page: "/docs", views: 12600 },
  { page: "/about", views: 9200 },
];

const topReferrers = [
  { source: "google.com", visitors: 18420, share: "43.2%" },
  { source: "twitter.com", visitors: 6840, share: "16.1%" },
  { source: "github.com", visitors: 5230, share: "12.3%" },
  { source: "linkedin.com", visitors: 4100, share: "9.6%" },
  { source: "reddit.com", visitors: 3280, share: "7.7%" },
  { source: "dev.to", visitors: 2410, share: "5.7%" },
];

const deviceBreakdown = [
  { label: "Desktop", value: 58, variant: "primary" as const },
  { label: "Mobile", value: 32, variant: "success" as const },
  { label: "Tablet", value: 10, variant: "warning" as const },
];

export default function AnalyticsDashboard() {
  return (
    <div className="space-y-6">
      <PageHeader title="Analytics" description="Website traffic and user behavior insights." />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <GradientCard
          title="Page Views"
          value="128,430"
          change={14.2}
          variant="primary"
          icon={<Eye className="h-4 w-4" />}
        />
        <GradientCard
          title="Unique Visitors"
          value="42,580"
          change={8.7}
          variant="ocean"
          icon={<Users className="h-4 w-4" />}
        />
        <GradientCard
          title="Bounce Rate"
          value="24.3%"
          change={-2.1}
          variant="warning"
          icon={<MousePointerClick className="h-4 w-4" />}
        />
        <GradientCard
          title="Avg Session"
          value="4m 32s"
          change={5.3}
          variant="sunset"
          icon={<Clock className="h-4 w-4" />}
        />
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Daily Visits (Last 30 Days)</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-[280px]">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={dailyVisitsData}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                <XAxis dataKey="day" tick={axisTick} axisLine={false} tickLine={false} />
                <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                <Tooltip {...tooltipProps} />
                <Line
                  type="monotone"
                  dataKey="visits"
                  stroke={chartColors.primary}
                  strokeWidth={2}
                  dot={false}
                />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </CardContent>
      </Card>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Top Pages</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-[260px]">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={topPagesData} layout="vertical">
                  <CartesianGrid
                    strokeDasharray="3 3"
                    className="stroke-border"
                    horizontal={false}
                  />
                  <XAxis type="number" tick={axisTick} axisLine={false} tickLine={false} />
                  <YAxis
                    type="category"
                    dataKey="page"
                    tick={axisTick}
                    axisLine={false}
                    tickLine={false}
                    width={80}
                  />
                  <Tooltip {...tooltipProps} />
                  <Bar dataKey="views" fill={chartColors.violet} radius={[0, 3, 3, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Top Referrers</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              <div className="grid grid-cols-3 text-xs font-medium text-muted-foreground border-b border-border pb-2">
                <span>Source</span>
                <span className="text-right">Visitors</span>
                <span className="text-right">Share</span>
              </div>
              {topReferrers.map((ref) => (
                <div key={ref.source} className="grid grid-cols-3 text-sm py-1.5">
                  <span className="font-medium">{ref.source}</span>
                  <span className="text-right text-muted-foreground">
                    {ref.visitors.toLocaleString()}
                  </span>
                  <span className="text-right font-medium">{ref.share}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Device Breakdown</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-around py-4">
            {deviceBreakdown.map((device) => (
              <CircularProgress
                key={device.label}
                value={device.value}
                variant={device.variant}
                label={device.label}
                size={80}
              />
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
