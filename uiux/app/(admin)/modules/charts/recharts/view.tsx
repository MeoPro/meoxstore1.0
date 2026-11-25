"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
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

const barData = [
  { month: "Jan", revenue: 4000 },
  { month: "Feb", revenue: 3000 },
  { month: "Mar", revenue: 5000 },
  { month: "Apr", revenue: 4500 },
  { month: "May", revenue: 6000 },
  { month: "Jun", revenue: 5500 },
];

const lineData = [
  { month: "Jan", users: 1200, sessions: 2400 },
  { month: "Feb", users: 1900, sessions: 3200 },
  { month: "Mar", users: 2400, sessions: 4100 },
  { month: "Apr", users: 2800, sessions: 4800 },
  { month: "May", users: 3200, sessions: 5200 },
  { month: "Jun", users: 3800, sessions: 6100 },
];

export default function RechartsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Recharts"
        description="Interactive charts built with the Recharts library."
      />

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Monthly Revenue</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-[300px]">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={barData}>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                  <XAxis dataKey="month" tick={axisTick} axisLine={false} tickLine={false} />
                  <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                  <Tooltip {...tooltipProps} />
                  <Bar dataKey="revenue" fill={chartColors.primary} radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Users &amp; Sessions</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-[300px]">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={lineData}>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                  <XAxis dataKey="month" tick={axisTick} axisLine={false} tickLine={false} />
                  <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                  <Tooltip {...tooltipProps} />
                  <Line
                    type="monotone"
                    dataKey="users"
                    stroke={chartColors.primary}
                    strokeWidth={2}
                  />
                  <Line
                    type="monotone"
                    dataKey="sessions"
                    stroke={chartColors.info}
                    strokeWidth={2}
                  />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
