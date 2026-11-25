"use client";

import { PageHeader } from "@/components/layout/page-header";
import { AnimatedProgress } from "@/components/ui/animated-progress";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { activities, crmStats } from "@/data/crm";
import {
  Calendar,
  DollarSign,
  FileText,
  Handshake,
  Mail,
  Phone,
  StickyNote,
  Target,
  TrendingUp,
} from "lucide-react";
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts";
import { axisTick, chartColors, tooltipProps } from "@/lib/chart-theme";

const dealsByStage = [
  { stage: "Qualification", count: 8, value: 240000 },
  { stage: "Needs Analysis", count: 5, value: 380000 },
  { stage: "Proposal", count: 6, value: 520000 },
  { stage: "Negotiation", count: 4, value: 680000 },
  { stage: "Closed Won", count: 3, value: 310000 },
];

const salesRepPerformance = [
  { name: "John Doe", target: 85, variant: "primary" as const },
  { name: "Jane Smith", target: 72, variant: "success" as const },
  { name: "Mark Wilson", target: 94, variant: "gradient" as const },
  { name: "Sara Davis", target: 61, variant: "info" as const },
];

const activityIcons: Record<string, React.ReactNode> = {
  call: <Phone className="h-3 w-3" />,
  email: <Mail className="h-3 w-3" />,
  meeting: <Calendar className="h-3 w-3" />,
  task: <FileText className="h-3 w-3" />,
  note: <StickyNote className="h-3 w-3" />,
};

export default function CrmDashboard() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="CRM Dashboard"
        description="Track leads, deals, and sales pipeline at a glance."
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <GradientCard
          title="Total Leads"
          value={crmStats.totalLeads.value}
          change={crmStats.totalLeads.change}
          variant="primary"
          icon={<Target className="h-4 w-4" />}
        />
        <GradientCard
          title="Active Deals"
          value={crmStats.activeDeals.value}
          change={crmStats.activeDeals.change}
          variant="success"
          icon={<Handshake className="h-4 w-4" />}
        />
        <GradientCard
          title="Pipeline Value"
          value={`$${(crmStats.pipelineValue.value / 1000).toFixed(0)}K`}
          change={crmStats.pipelineValue.change}
          variant="ocean"
          icon={<DollarSign className="h-4 w-4" />}
        />
        <GradientCard
          title="Win Rate"
          value={`${crmStats.winRate.value}%`}
          change={crmStats.winRate.change}
          variant="sunset"
          icon={<TrendingUp className="h-4 w-4" />}
        />
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Deals by Stage</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-[280px]">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={dealsByStage}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                <XAxis dataKey="stage" tick={axisTick} axisLine={false} tickLine={false} />
                <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                <Tooltip {...tooltipProps} />
                <Bar
                  dataKey="count"
                  fill={chartColors.primary}
                  radius={[3, 3, 0, 0]}
                  name="Deals"
                />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </CardContent>
      </Card>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Recent Activities</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {activities.map((activity) => (
                <div key={activity.id} className="flex items-start gap-3 py-1.5">
                  <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-muted">
                    {activityIcons[activity.type]}
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium truncate">{activity.title}</p>
                    <p className="text-2xs text-muted-foreground">
                      {activity.contact} · {activity.date}
                    </p>
                  </div>
                  <Badge variant={activity.done ? "success" : "default"}>
                    {activity.done ? "Done" : "Pending"}
                  </Badge>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Sales Rep Performance</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {salesRepPerformance.map((rep) => (
              <AnimatedProgress
                key={rep.name}
                label={rep.name}
                value={rep.target}
                variant={rep.variant}
                size="md"
              />
            ))}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
