"use client";

import { PageHeader } from "@/components/layout/page-header";
import { AnimatedProgress } from "@/components/ui/animated-progress";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import {
  Activity,
  ArrowUpRight,
  BarChart3,
  BookOpen,
  Briefcase,
  CreditCard,
  DollarSign,
  Eye,
  Globe,
  GraduationCap,
  Headphones,
  Package,
  ShoppingBag,
  ShoppingCart,
  Target,
  TrendingUp,
  UserCheck,
  Users,
} from "lucide-react";
import Link from "next/link";
import {
  Area,
  AreaChart,
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

// ============================
// Dashboard Hub Data
// ============================

const overviewStats = {
  totalRevenue: {
    title: "Total Revenue",
    value: "$284,520",
    change: 18.2,
    variant: "primary" as const,
  },
  activeUsers: {
    title: "Active Users",
    value: "12,842",
    change: 12.5,
    variant: "success" as const,
  },
  totalOrders: { title: "Total Orders", value: "3,456", change: 8.7, variant: "ocean" as const },
  conversionRate: {
    title: "Conversion Rate",
    value: "4.28%",
    change: 2.1,
    variant: "sunset" as const,
  },
};

// Analytics Section
const trafficData = [
  { day: "Mon", visitors: 3200, pageViews: 8400 },
  { day: "Tue", visitors: 4100, pageViews: 10200 },
  { day: "Wed", visitors: 3800, pageViews: 9600 },
  { day: "Thu", visitors: 5200, pageViews: 13100 },
  { day: "Fri", visitors: 4600, pageViews: 11800 },
  { day: "Sat", visitors: 3100, pageViews: 7200 },
  { day: "Sun", visitors: 2800, pageViews: 6400 },
];

// E-commerce Section
const salesData = [
  { month: "Jan", revenue: 18200, orders: 142 },
  { month: "Feb", revenue: 22400, orders: 168 },
  { month: "Mar", revenue: 28600, orders: 204 },
  { month: "Apr", revenue: 24100, orders: 186 },
  { month: "May", revenue: 31500, orders: 238 },
  { month: "Jun", revenue: 29800, orders: 221 },
  { month: "Jul", revenue: 35400, orders: 264 },
  { month: "Aug", revenue: 42100, orders: 312 },
];

const recentOrders = [
  {
    id: "#ORD-7821",
    customer: "Sarah Johnson",
    amount: 1299.99,
    status: "delivered",
    time: "2 min ago",
  },
  { id: "#ORD-7820", customer: "Mike Chen", amount: 849.5, status: "shipped", time: "15 min ago" },
  {
    id: "#ORD-7819",
    customer: "Emily Davis",
    amount: 2490.0,
    status: "processing",
    time: "1 hour ago",
  },
  { id: "#ORD-7818", customer: "Alex Kim", amount: 599.99, status: "pending", time: "2 hours ago" },
  {
    id: "#ORD-7817",
    customer: "Lisa Wang",
    amount: 1790.0,
    status: "delivered",
    time: "3 hours ago",
  },
];

// CRM Section
const pipelineData = [
  { stage: "Leads", count: 48, value: "$142K" },
  { stage: "Qualified", count: 32, value: "$380K" },
  { stage: "Proposal", count: 18, value: "$520K" },
  { stage: "Negotiation", count: 12, value: "$680K" },
  { stage: "Closed", count: 8, value: "$310K" },
];

const dealStageChart = [
  { stage: "Lead", deals: 48 },
  { stage: "Qualified", deals: 32 },
  { stage: "Proposal", deals: 18 },
  { stage: "Negotiation", deals: 12 },
  { stage: "Won", deals: 8 },
];

// LMS Section
const coursePerformance = [
  { name: "React Masterclass", students: 1240, completion: 78, rating: 4.8 },
  { name: "Node.js Advanced", students: 890, completion: 65, rating: 4.6 },
  { name: "UI/UX Design", students: 1580, completion: 82, rating: 4.9 },
  { name: "Python for Data", students: 720, completion: 54, rating: 4.5 },
  { name: "DevOps Pipeline", students: 460, completion: 71, rating: 4.7 },
];

// Management Section
const projectProgress = [
  { name: "Website Redesign", progress: 82, variant: "primary" as const },
  { name: "Mobile App v2.0", progress: 48, variant: "success" as const },
  { name: "API Migration", progress: 91, variant: "gradient" as const },
  { name: "CRM Integration", progress: 35, variant: "info" as const },
  { name: "Analytics Platform", progress: 64, variant: "warning" as const },
];

// Support Desk Section
const ticketStats = [
  { day: "Mon", opened: 12, resolved: 9 },
  { day: "Tue", opened: 18, resolved: 15 },
  { day: "Wed", opened: 8, resolved: 14 },
  { day: "Thu", opened: 22, resolved: 19 },
  { day: "Fri", opened: 15, resolved: 12 },
  { day: "Sat", opened: 6, resolved: 8 },
  { day: "Sun", opened: 4, resolved: 5 },
];

// Activity Feed
const activityFeed = [
  {
    type: "order",
    text: "New order #7821 placed by Sarah Johnson",
    time: "2 min ago",
    icon: <ShoppingCart className="h-3 w-3" />,
    color: "bg-primary/10 text-primary",
  },
  {
    type: "customer",
    text: "New customer registered: Mike Chen",
    time: "15 min ago",
    icon: <Users className="h-3 w-3" />,
    color: "bg-success/10 text-success",
  },
  {
    type: "payment",
    text: "Payment received: $2,490 from Emily Davis",
    time: "1 hour ago",
    icon: <CreditCard className="h-3 w-3" />,
    color: "bg-warning/10 text-warning",
  },
  {
    type: "shipping",
    text: "Order #7815 shipped via FedEx Express",
    time: "2 hours ago",
    icon: <Package className="h-3 w-3" />,
    color: "bg-info/10 text-info",
  },
  {
    type: "view",
    text: "Product 'MacBook Pro' reached 1K views today",
    time: "3 hours ago",
    icon: <Eye className="h-3 w-3" />,
    color: "bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400",
  },
  {
    type: "report",
    text: "Weekly revenue report generated for admins",
    time: "4 hours ago",
    icon: <Activity className="h-3 w-3" />,
    color: "bg-pink-100 text-pink-600 dark:bg-pink-500/10 dark:text-pink-400",
  },
];

const statusColors: Record<string, "success" | "info" | "warning" | "default"> = {
  delivered: "success",
  shipped: "info",
  processing: "warning",
  pending: "default",
};

// Quick Links to other dashboards
const dashboardLinks = [
  {
    title: "Analytics",
    href: "/dashboard/analytics",
    icon: <BarChart3 className="h-4 w-4" />,
    description: "Traffic & engagement",
    color: "text-indigo-500",
  },
  {
    title: "CRM",
    href: "/dashboard/crm",
    icon: <Users className="h-4 w-4" />,
    description: "Leads & deals",
    color: "text-emerald-500",
  },
  {
    title: "E-commerce",
    href: "/dashboard/ecommerce",
    icon: <ShoppingBag className="h-4 w-4" />,
    description: "Sales & orders",
    color: "text-orange-500",
  },
  {
    title: "LMS",
    href: "/dashboard/lms",
    icon: <GraduationCap className="h-4 w-4" />,
    description: "Courses & students",
    color: "text-purple-500",
  },
  {
    title: "Management",
    href: "/dashboard/management",
    icon: <Briefcase className="h-4 w-4" />,
    description: "Projects & tasks",
    color: "text-cyan-500",
  },
  {
    title: "Support Desk",
    href: "/dashboard/support-desk",
    icon: <Headphones className="h-4 w-4" />,
    description: "Tickets & resolution",
    color: "text-pink-500",
  },
];

export function DashboardContent() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Dashboard"
        description="Welcome back! Here's your complete business overview across all departments."
      />

      {/* Overview Stats */}
      <div className="stagger grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <GradientCard
          title={overviewStats.totalRevenue.title}
          value={overviewStats.totalRevenue.value}
          change={overviewStats.totalRevenue.change}
          variant={overviewStats.totalRevenue.variant}
          icon={<DollarSign className="h-4 w-4" />}
        />
        <GradientCard
          title={overviewStats.activeUsers.title}
          value={overviewStats.activeUsers.value}
          change={overviewStats.activeUsers.change}
          variant={overviewStats.activeUsers.variant}
          icon={<Users className="h-4 w-4" />}
        />
        <GradientCard
          title={overviewStats.totalOrders.title}
          value={overviewStats.totalOrders.value}
          change={overviewStats.totalOrders.change}
          variant={overviewStats.totalOrders.variant}
          icon={<ShoppingCart className="h-4 w-4" />}
        />
        <GradientCard
          title={overviewStats.conversionRate.title}
          value={overviewStats.conversionRate.value}
          change={overviewStats.conversionRate.change}
          variant={overviewStats.conversionRate.variant}
          icon={<TrendingUp className="h-4 w-4" />}
        />
      </div>

      {/* Quick Navigation to Dashboards */}
      <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
        {dashboardLinks.map((link) => (
          <Link key={link.title} href={link.href}>
            <Card className="group cursor-pointer transition-all hover:shadow-dropdown hover:border-primary/20">
              <CardContent className="flex flex-col items-center gap-2 p-5 text-center">
                <div className={`${link.color} transition-transform group-hover:scale-110`}>
                  {link.icon}
                </div>
                <p className="text-xs font-semibold">{link.title}</p>
                <p className="text-2xs text-muted-foreground">{link.description}</p>
              </CardContent>
            </Card>
          </Link>
        ))}
      </div>

      {/* Analytics + E-commerce Charts */}
      <div className="grid gap-6 lg:grid-cols-7">
        {/* Revenue Chart - E-commerce */}
        <Card className="lg:col-span-4">
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle className="flex items-center gap-2">
                <ShoppingBag className="h-4 w-4 text-primary" />
                Revenue & Orders
              </CardTitle>
              <Link
                href="/dashboard/ecommerce"
                className="text-xs font-medium text-primary flex items-center gap-0.5"
              >
                View details <ArrowUpRight className="h-3 w-3" />
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div className="h-[280px]">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={salesData}>
                  <defs>
                    <linearGradient id="gRevHub" x1="0" y1="0" x2="0" y2="1">
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
                    dataKey="revenue"
                    stroke={chartColors.primary}
                    fill="url(#gRevHub)"
                    strokeWidth={2}
                    name="Revenue ($)"
                  />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>

        {/* Traffic Chart - Analytics */}
        <Card className="lg:col-span-3">
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle className="flex items-center gap-2">
                <Globe className="h-4 w-4 text-purple-500" />
                Site Traffic
              </CardTitle>
              <Link
                href="/dashboard/analytics"
                className="text-xs font-medium text-primary flex items-center gap-0.5"
              >
                Details <ArrowUpRight className="h-3 w-3" />
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div className="h-[280px]">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={trafficData}>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                  <XAxis dataKey="day" tick={axisTick} axisLine={false} tickLine={false} />
                  <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                  <Tooltip {...tooltipProps} />
                  <Bar
                    dataKey="visitors"
                    fill={chartColors.violet}
                    radius={[3, 3, 0, 0]}
                    name="Visitors"
                  />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* CRM Pipeline + Support Tickets */}
      <div className="grid gap-6 lg:grid-cols-2">
        {/* CRM Pipeline */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle className="flex items-center gap-2">
                <Target className="h-4 w-4 text-emerald-500" />
                Sales Pipeline
              </CardTitle>
              <Link
                href="/dashboard/crm"
                className="text-xs font-medium text-primary flex items-center gap-0.5"
              >
                View CRM <ArrowUpRight className="h-3 w-3" />
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div className="h-[220px]">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={dealStageChart} layout="vertical">
                  <CartesianGrid
                    strokeDasharray="3 3"
                    className="stroke-border"
                    horizontal={false}
                  />
                  <XAxis type="number" tick={axisTick} axisLine={false} tickLine={false} />
                  <YAxis
                    type="category"
                    dataKey="stage"
                    tick={axisTick}
                    axisLine={false}
                    tickLine={false}
                    width={80}
                  />
                  <Tooltip {...tooltipProps} />
                  <Bar
                    dataKey="deals"
                    fill={chartColors.success}
                    radius={[0, 3, 3, 0]}
                    name="Deals"
                  />
                </BarChart>
              </ResponsiveContainer>
            </div>
            <div className="mt-3 grid grid-cols-5 gap-2">
              {pipelineData.map((stage) => (
                <div key={stage.stage} className="text-center">
                  <p className="text-lg font-bold">{stage.count}</p>
                  <p className="text-2xs text-muted-foreground">{stage.value}</p>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Support Desk Tickets */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle className="flex items-center gap-2">
                <Headphones className="h-4 w-4 text-pink-500" />
                Support Tickets
              </CardTitle>
              <Link
                href="/dashboard/support-desk"
                className="text-xs font-medium text-primary flex items-center gap-0.5"
              >
                View all <ArrowUpRight className="h-3 w-3" />
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div className="h-[220px]">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={ticketStats}>
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
                    name="Opened"
                  />
                  <Line
                    type="monotone"
                    dataKey="resolved"
                    stroke={chartColors.success}
                    strokeWidth={2}
                    dot={false}
                    name="Resolved"
                  />
                </LineChart>
              </ResponsiveContainer>
            </div>
            <div className="mt-3 flex items-center justify-around text-center">
              <div>
                <p className="text-lg font-bold text-destructive">45</p>
                <p className="text-2xs text-muted-foreground">Open</p>
              </div>
              <div>
                <p className="text-lg font-bold text-warning">12</p>
                <p className="text-2xs text-muted-foreground">In Progress</p>
              </div>
              <div>
                <p className="text-lg font-bold text-success">23</p>
                <p className="text-2xs text-muted-foreground">Resolved Today</p>
              </div>
              <div>
                <p className="text-lg font-bold">2h 14m</p>
                <p className="text-2xs text-muted-foreground">Avg Response</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* LMS + Project Management */}
      <div className="grid gap-6 lg:grid-cols-2">
        {/* LMS - Course Performance */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle className="flex items-center gap-2">
                <BookOpen className="h-4 w-4 text-purple-500" />
                Course Performance
              </CardTitle>
              <Link
                href="/dashboard/lms"
                className="text-xs font-medium text-primary flex items-center gap-0.5"
              >
                View LMS <ArrowUpRight className="h-3 w-3" />
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              <div className="grid grid-cols-4 text-xs font-medium text-muted-foreground border-b border-border pb-2">
                <span className="col-span-2">Course</span>
                <span className="text-center">Students</span>
                <span className="text-right">Completion</span>
              </div>
              {coursePerformance.map((course) => (
                <div key={course.name} className="grid grid-cols-4 items-center text-sm py-1.5">
                  <span className="col-span-2 font-medium truncate">{course.name}</span>
                  <span className="text-center text-muted-foreground">
                    {course.students.toLocaleString()}
                  </span>
                  <span className="text-right font-medium">{course.completion}%</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Management - Project Progress */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle className="flex items-center gap-2">
                <Briefcase className="h-4 w-4 text-cyan-500" />
                Project Progress
              </CardTitle>
              <Link
                href="/dashboard/management"
                className="text-xs font-medium text-primary flex items-center gap-0.5"
              >
                View all <ArrowUpRight className="h-3 w-3" />
              </Link>
            </div>
          </CardHeader>
          <CardContent className="space-y-4">
            {projectProgress.map((project) => (
              <AnimatedProgress
                key={project.name}
                label={project.name}
                value={project.progress}
                variant={project.variant}
                size="md"
              />
            ))}
          </CardContent>
        </Card>
      </div>

      {/* Recent Orders + Activity Feed */}
      <div className="grid gap-6 lg:grid-cols-2">
        {/* Recent Orders */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle>Recent Orders</CardTitle>
              <Link
                href="/app/ecommerce/orders"
                className="text-xs font-medium text-primary flex items-center gap-0.5"
              >
                View all <ArrowUpRight className="h-3 w-3" />
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {recentOrders.map((order) => (
                <div key={order.id} className="flex items-center gap-3 py-1.5">
                  <Avatar name={order.customer} size="sm" />
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium truncate">{order.customer}</p>
                    <p className="text-2xs text-muted-foreground">
                      {order.id} · {order.time}
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="text-sm font-semibold">${order.amount.toLocaleString()}</p>
                    <Badge variant={statusColors[order.status]} className="mt-0.5">
                      {order.status}
                    </Badge>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Activity Feed */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle>Live Activity</CardTitle>
              <span className="flex items-center gap-1 text-2xs font-medium text-success">
                <span className="h-1.5 w-1.5 rounded-full bg-success animate-pulse" /> Live
              </span>
            </div>
          </CardHeader>
          <CardContent>
            <div className="relative space-y-0">
              <div className="absolute left-[15px] top-2 h-[calc(100%-16px)] w-px bg-border" />
              {activityFeed.map((item, i) => (
                <div key={i} className="relative flex items-start gap-3 py-2.5">
                  <div
                    className={`relative z-10 flex h-[30px] w-[30px] shrink-0 items-center justify-center rounded-md ${item.color}`}
                  >
                    {item.icon}
                  </div>
                  <div className="pt-0.5">
                    <p className="text-xs leading-relaxed">{item.text}</p>
                    <p className="mt-0.5 text-2xs text-muted-foreground">{item.time}</p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Bottom Row - Key Metrics Summary */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <UserCheck className="h-5 w-5" />
            </div>
            <div>
              <p className="text-2xs text-muted-foreground">Customer Retention</p>
              <p className="text-lg font-bold">94.2%</p>
            </div>
          </div>
        </Card>
        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-success/10 text-success">
              <DollarSign className="h-5 w-5" />
            </div>
            <div>
              <p className="text-2xs text-muted-foreground">Avg Order Value</p>
              <p className="text-lg font-bold">$186.40</p>
            </div>
          </div>
        </Card>
        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-warning/10 text-warning">
              <GraduationCap className="h-5 w-5" />
            </div>
            <div>
              <p className="text-2xs text-muted-foreground">Course Completion</p>
              <p className="text-lg font-bold">72.8%</p>
            </div>
          </div>
        </Card>
        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-info/10 text-info">
              <Headphones className="h-5 w-5" />
            </div>
            <div>
              <p className="text-2xs text-muted-foreground">CSAT Score</p>
              <p className="text-lg font-bold">4.6/5.0</p>
            </div>
          </div>
        </Card>
      </div>
    </div>
  );
}
