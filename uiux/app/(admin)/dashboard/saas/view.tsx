"use client";

import { PageHeader } from "@/components/layout/page-header";
import { AnimatedProgress } from "@/components/ui/animated-progress";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { DollarSign, Heart, TrendingUp, UserMinus } from "lucide-react";
import {
  Area,
  AreaChart,
  CartesianGrid,
  Cell,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { axisTick, chartColors, tooltipProps } from "@/lib/chart-theme";

const mrrGrowthData = [
  { month: "Sep", mrr: 28400 },
  { month: "Oct", mrr: 30100 },
  { month: "Nov", mrr: 31800 },
  { month: "Dec", mrr: 33500 },
  { month: "Jan", mrr: 34200 },
  { month: "Feb", mrr: 35800 },
  { month: "Mar", mrr: 37100 },
  { month: "Apr", mrr: 38400 },
  { month: "May", mrr: 39600 },
  { month: "Jun", mrr: 40200 },
  { month: "Jul", mrr: 41500 },
  { month: "Aug", mrr: 42800 },
];

const revenueByPlan = [
  { name: "Free", value: 0, color: chartColors.muted },
  { name: "Starter", value: 8200, color: chartColors.primary },
  { name: "Pro", value: 22400, color: chartColors.violet },
  { name: "Enterprise", value: 12200, color: chartColors.pink },
];

const topAccounts = [
  { name: "Acme Corp", plan: "Enterprise", mrr: 4200, users: 120 },
  { name: "TechCo", plan: "Enterprise", mrr: 3800, users: 85 },
  { name: "Global Inc", plan: "Pro", mrr: 1200, users: 42 },
  { name: "Start.io", plan: "Pro", mrr: 890, users: 28 },
  { name: "Cloudify", plan: "Pro", mrr: 720, users: 18 },
  { name: "BigCorp", plan: "Enterprise", mrr: 5100, users: 200 },
];

const featureAdoption = [
  { name: "Dashboard Analytics", adoption: 94, variant: "primary" as const },
  { name: "Team Collaboration", adoption: 78, variant: "success" as const },
  { name: "API Access", adoption: 62, variant: "info" as const },
  { name: "Custom Reports", adoption: 45, variant: "warning" as const },
  { name: "SSO Integration", adoption: 31, variant: "gradient" as const },
];

export default function SaasDashboard() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="SaaS Metrics"
        description="Monthly recurring revenue, churn, and growth indicators."
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <GradientCard
          title="MRR"
          value="$42.8K"
          change={8.4}
          variant="primary"
          icon={<DollarSign className="h-4 w-4" />}
        />
        <GradientCard
          title="ARR"
          value="$513K"
          change={12.1}
          variant="success"
          icon={<TrendingUp className="h-4 w-4" />}
        />
        <GradientCard
          title="Churn Rate"
          value="2.4%"
          change={-0.3}
          variant="danger"
          icon={<UserMinus className="h-4 w-4" />}
        />
        <GradientCard
          title="LTV"
          value="$1,240"
          change={6.8}
          variant="ocean"
          icon={<Heart className="h-4 w-4" />}
        />
      </div>

      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle>MRR Growth</CardTitle>
            <span className="text-xs text-muted-foreground">Last 12 months</span>
          </div>
        </CardHeader>
        <CardContent>
          <div className="h-[280px]">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={mrrGrowthData}>
                <defs>
                  <linearGradient id="gMrr" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor={chartColors.primary} stopOpacity={0.2} />
                    <stop offset="95%" stopColor={chartColors.primary} stopOpacity={0} />
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                <XAxis dataKey="month" tick={axisTick} axisLine={false} tickLine={false} />
                <YAxis
                  tick={axisTick}
                  tickFormatter={(v) => `$${v / 1000}k`}
                  axisLine={false}
                  tickLine={false}
                />
                <Tooltip {...tooltipProps} />
                <Area
                  type="monotone"
                  dataKey="mrr"
                  stroke={chartColors.primary}
                  fill="url(#gMrr)"
                  strokeWidth={2}
                />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </CardContent>
      </Card>

      <div className="grid gap-6 lg:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle>Revenue by Plan</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex justify-center">
              <div className="h-[160px] w-[160px]">
                <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Pie
                      data={revenueByPlan.filter((p) => p.value > 0)}
                      cx="50%"
                      cy="50%"
                      innerRadius={45}
                      outerRadius={70}
                      paddingAngle={3}
                      dataKey="value"
                    >
                      {revenueByPlan
                        .filter((p) => p.value > 0)
                        .map((entry, i) => (
                          <Cell key={i} fill={entry.color} />
                        ))}
                    </Pie>
                    <Tooltip {...tooltipProps} />
                  </PieChart>
                </ResponsiveContainer>
              </div>
            </div>
            <div className="mt-4 space-y-2">
              {revenueByPlan.map((plan) => (
                <div key={plan.name} className="flex items-center justify-between text-xs">
                  <span className="flex items-center gap-2">
                    <span
                      className="h-2 w-2 rounded-full"
                      style={{ backgroundColor: plan.color }}
                    />
                    {plan.name}
                  </span>
                  <span className="font-medium">${plan.value.toLocaleString()}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Top Accounts</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              <div className="grid grid-cols-4 text-xs font-medium text-muted-foreground border-b border-border pb-2">
                <span>Account</span>
                <span>Plan</span>
                <span className="text-right">MRR</span>
                <span className="text-right">Users</span>
              </div>
              {topAccounts
                .sort((a, b) => b.mrr - a.mrr)
                .map((account) => (
                  <div key={account.name} className="grid grid-cols-4 items-center text-sm py-1.5">
                    <span className="font-medium">{account.name}</span>
                    <span className="text-muted-foreground">{account.plan}</span>
                    <span className="text-right font-medium">${account.mrr.toLocaleString()}</span>
                    <span className="text-right text-muted-foreground">{account.users}</span>
                  </div>
                ))}
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Feature Adoption</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {featureAdoption.map((feature) => (
            <AnimatedProgress
              key={feature.name}
              label={feature.name}
              value={feature.adoption}
              variant={feature.variant}
              size="md"
            />
          ))}
        </CardContent>
      </Card>
    </div>
  );
}
