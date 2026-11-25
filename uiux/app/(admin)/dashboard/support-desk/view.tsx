"use client";

import { PageHeader } from "@/components/layout/page-header";
import { CircularProgress } from "@/components/ui/animated-progress";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { CheckCircle2, Clock, Inbox, Star } from "lucide-react";
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

const ticketsOverTime = [
  { day: "Mon", opened: 12, resolved: 9 },
  { day: "Tue", opened: 18, resolved: 15 },
  { day: "Wed", opened: 8, resolved: 14 },
  { day: "Thu", opened: 22, resolved: 19 },
  { day: "Fri", opened: 15, resolved: 12 },
  { day: "Sat", opened: 6, resolved: 8 },
  { day: "Sun", opened: 4, resolved: 5 },
];

const ticketsByCategory = [
  { category: "Billing", count: 28 },
  { category: "Technical", count: 42 },
  { category: "Account", count: 18 },
  { category: "Feature Request", count: 14 },
  { category: "Bug Report", count: 22 },
  { category: "General", count: 9 },
];

const recentTickets = [
  {
    id: "TKT-1024",
    subject: "Cannot access dashboard after update",
    priority: "high",
    status: "open",
    customer: "John Smith",
    created: "10 min ago",
  },
  {
    id: "TKT-1023",
    subject: "Billing discrepancy on last invoice",
    priority: "medium",
    status: "open",
    customer: "Maria Garcia",
    created: "25 min ago",
  },
  {
    id: "TKT-1022",
    subject: "Feature request: dark mode for mobile",
    priority: "low",
    status: "pending",
    customer: "Alex Kim",
    created: "1 hour ago",
  },
  {
    id: "TKT-1021",
    subject: "API rate limiting returning 429 errors",
    priority: "high",
    status: "in-progress",
    customer: "Sophie Martinez",
    created: "2 hours ago",
  },
  {
    id: "TKT-1020",
    subject: "Password reset email not received",
    priority: "medium",
    status: "resolved",
    customer: "David Lee",
    created: "3 hours ago",
  },
  {
    id: "TKT-1019",
    subject: "Export CSV not including all columns",
    priority: "low",
    status: "resolved",
    customer: "Lisa Wang",
    created: "4 hours ago",
  },
];

const priorityVariant: Record<string, "danger" | "warning" | "default"> = {
  high: "danger",
  medium: "warning",
  low: "default",
};

const statusVariant: Record<string, "danger" | "warning" | "info" | "success" | "default"> = {
  open: "danger",
  pending: "warning",
  "in-progress": "info",
  resolved: "success",
};

export default function SupportDeskDashboard() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Support Desk"
        description="Ticket management, resolution metrics, and customer satisfaction."
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <GradientCard
          title="Open Tickets"
          value="45"
          change={-8}
          variant="danger"
          icon={<Inbox className="h-4 w-4" />}
        />
        <GradientCard
          title="Resolved Today"
          value="23"
          change={12}
          variant="success"
          icon={<CheckCircle2 className="h-4 w-4" />}
        />
        <GradientCard
          title="Avg Response"
          value="2h 14m"
          change={-18}
          variant="ocean"
          icon={<Clock className="h-4 w-4" />}
        />
        <GradientCard
          title="CSAT Score"
          value="4.6/5"
          change={3.2}
          variant="sunset"
          icon={<Star className="h-4 w-4" />}
        />
      </div>

      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle>Tickets Over Time</CardTitle>
            <div className="flex items-center gap-3 text-xs text-muted-foreground">
              <span className="flex items-center gap-1">
                <span className="h-2 w-2 rounded-full bg-destructive" /> Opened
              </span>
              <span className="flex items-center gap-1">
                <span className="h-2 w-2 rounded-full bg-success" /> Resolved
              </span>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="h-[260px]">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={ticketsOverTime}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                <XAxis dataKey="day" tick={axisTick} axisLine={false} tickLine={false} />
                <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                <Tooltip {...tooltipProps} />
                <Line
                  type="monotone"
                  dataKey="opened"
                  stroke={chartColors.danger}
                  strokeWidth={2}
                  dot={false}
                />
                <Line
                  type="monotone"
                  dataKey="resolved"
                  stroke={chartColors.success}
                  strokeWidth={2}
                  dot={false}
                />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </CardContent>
      </Card>

      <div className="grid gap-6 lg:grid-cols-3">
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Recent Tickets</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {recentTickets.map((ticket) => (
                <div
                  key={ticket.id}
                  className="flex items-start justify-between gap-3 py-2 border-b border-border last:border-0"
                >
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                      <span className="font-mono text-xs text-muted-foreground">{ticket.id}</span>
                      <Badge variant={priorityVariant[ticket.priority]}>{ticket.priority}</Badge>
                      <Badge variant={statusVariant[ticket.status]}>{ticket.status}</Badge>
                    </div>
                    <p className="mt-1 text-sm font-medium truncate">{ticket.subject}</p>
                    <p className="text-2xs text-muted-foreground">
                      {ticket.customer} · {ticket.created}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Resolution Rate</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex justify-center py-4">
                <CircularProgress value={82} variant="success" size={100} label="This Week" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Tickets by Category</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="h-[200px]">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={ticketsByCategory}>
                    <CartesianGrid
                      strokeDasharray="3 3"
                      className="stroke-border"
                      vertical={false}
                    />
                    <XAxis dataKey="category" tick={axisTick} axisLine={false} tickLine={false} />
                    <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                    <Tooltip {...tooltipProps} />
                    <Bar dataKey="count" fill={chartColors.warning} radius={[3, 3, 0, 0]} />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
