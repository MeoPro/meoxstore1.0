"use client";

import { PageHeader } from "@/components/layout/page-header";
import { AnimatedProgress, CircularProgress } from "@/components/ui/animated-progress";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { courses, enrollments, instructors, lmsStats, quizzes } from "@/data/lms";
import { Award, BookOpen, GraduationCap, Star, Users, Wallet } from "lucide-react";
import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { axisTick, chartColors, tooltipProps } from "@/lib/chart-theme";

const enrollmentTrend = [
  { month: "Sep", enrollments: 420, completions: 240 },
  { month: "Oct", enrollments: 560, completions: 310 },
  { month: "Nov", enrollments: 680, completions: 380 },
  { month: "Dec", enrollments: 520, completions: 350 },
  { month: "Jan", enrollments: 740, completions: 420 },
  { month: "Feb", enrollments: 890, completions: 510 },
  { month: "Mar", enrollments: 760, completions: 480 },
  { month: "Apr", enrollments: 920, completions: 560 },
  { month: "May", enrollments: 1080, completions: 620 },
  { month: "Jun", enrollments: 940, completions: 590 },
  { month: "Jul", enrollments: 1120, completions: 680 },
  { month: "Aug", enrollments: 1240, completions: 730 },
];

const enrollmentsPerCourse = courses
  .filter((c) => c.status === "published")
  .map((c) => ({
    name: c.title.length > 18 ? c.title.slice(0, 18) + "…" : c.title,
    students: c.students,
  }))
  .sort((a, b) => b.students - a.students)
  .slice(0, 6);

const categoryCompletion = [
  { label: "Frontend", value: 78, variant: "primary" as const },
  { label: "Backend", value: 64, variant: "success" as const },
  { label: "Design", value: 88, variant: "gradient" as const },
  { label: "DevOps", value: 52, variant: "warning" as const },
  { label: "Data", value: 71, variant: "info" as const },
];

const topCourses = courses
  .filter((c) => c.status === "published" && c.rating > 0)
  .sort((a, b) => b.rating - a.rating)
  .slice(0, 5);

const topInstructors = [...instructors].sort((a, b) => b.students - a.students).slice(0, 5);

const recentEnrollments = enrollments.slice(0, 5);

const enrollmentStatusVariant: Record<string, "info" | "success" | "danger"> = {
  active: "info",
  completed: "success",
  dropped: "danger",
};

export default function LmsDashboard() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="LMS Dashboard"
        description="Learning management overview — students, courses, and performance."
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <GradientCard
          title="Total Students"
          value={lmsStats.totalStudents.toLocaleString()}
          change={11.4}
          variant="primary"
          icon={<Users className="h-4 w-4" />}
        />
        <GradientCard
          title="Active Courses"
          value={lmsStats.totalCourses.toString()}
          change={4}
          variant="ocean"
          icon={<BookOpen className="h-4 w-4" />}
        />
        <GradientCard
          title="Completion Rate"
          value={`${lmsStats.completionRate}%`}
          change={3.2}
          variant="success"
          icon={<GraduationCap className="h-4 w-4" />}
        />
        <GradientCard
          title="Revenue"
          value={`$${(lmsStats.totalRevenue / 1000).toFixed(0)}K`}
          change={18}
          variant="sunset"
          icon={<Wallet className="h-4 w-4" />}
        />
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <Card className="min-w-0 lg:col-span-2">
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle>Enrollment Overview</CardTitle>
              <div className="flex items-center gap-3 text-xs text-muted-foreground">
                <span className="flex items-center gap-1">
                  <span className="h-2 w-2 rounded-full bg-primary" /> Enrollments
                </span>
                <span className="flex items-center gap-1">
                  <span className="h-2 w-2 rounded-full bg-success" /> Completions
                </span>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div className="h-[260px]">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={enrollmentTrend}>
                  <defs>
                    <linearGradient id="gradEnroll" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor={chartColors.primary} stopOpacity={0.25} />
                      <stop offset="95%" stopColor={chartColors.primary} stopOpacity={0} />
                    </linearGradient>
                    <linearGradient id="gradComplete" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor={chartColors.success} stopOpacity={0.2} />
                      <stop offset="95%" stopColor={chartColors.success} stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                  <XAxis dataKey="month" tick={axisTick} axisLine={false} tickLine={false} />
                  <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                  <Tooltip {...tooltipProps} />
                  <Area
                    type="monotone"
                    dataKey="enrollments"
                    stroke={chartColors.primary}
                    fill="url(#gradEnroll)"
                    strokeWidth={2}
                  />
                  <Area
                    type="monotone"
                    dataKey="completions"
                    stroke={chartColors.success}
                    fill="url(#gradComplete)"
                    strokeWidth={2}
                  />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>

        <Card className="min-w-0">
          <CardHeader>
            <CardTitle>Completion by Category</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex flex-wrap items-center justify-around gap-4 py-4">
              {categoryCompletion.map((cat) => (
                <CircularProgress
                  key={cat.label}
                  value={cat.value}
                  variant={cat.variant}
                  label={cat.label}
                  size={72}
                />
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card className="min-w-0">
          <CardHeader>
            <CardTitle>Enrollments Per Course</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-[260px] overflow-x-auto">
              <div className="h-full min-w-[480px]">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={enrollmentsPerCourse}>
                    <CartesianGrid
                      strokeDasharray="3 3"
                      className="stroke-border"
                      vertical={false}
                    />
                    <XAxis dataKey="name" tick={axisTick} axisLine={false} tickLine={false} />
                    <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                    <Tooltip {...tooltipProps} />
                    <Bar dataKey="students" fill={chartColors.violet} radius={[4, 4, 0, 0]} />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="min-w-0">
          <CardHeader>
            <CardTitle>Quiz Performance</CardTitle>
          </CardHeader>
          <CardContent className="space-y-5">
            {quizzes.map((quiz) => (
              <div key={quiz.id} className="space-y-2">
                <div className="flex items-center justify-between">
                  <div className="min-w-0">
                    <p className="text-sm font-medium truncate">{quiz.title}</p>
                    <p className="text-2xs text-muted-foreground">
                      {quiz.course} · {quiz.attempts} attempts
                    </p>
                  </div>
                  <Badge variant="outline">{quiz.avgScore}% avg</Badge>
                </div>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <AnimatedProgress
                    value={quiz.avgScore}
                    variant="primary"
                    size="sm"
                    label="Avg Score"
                    showValue={false}
                  />
                  <AnimatedProgress
                    value={quiz.passRate}
                    variant="success"
                    size="sm"
                    label="Pass Rate"
                    showValue={false}
                  />
                </div>
              </div>
            ))}
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card className="min-w-0">
          <CardHeader>
            <CardTitle>Recent Enrollments</CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <div className="divide-y divide-border">
              {recentEnrollments.map((enrollment) => (
                <div key={enrollment.id} className="flex items-center gap-3 px-4 py-3">
                  <Avatar name={enrollment.student} size="sm" />
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium truncate">{enrollment.student}</p>
                    <p className="text-2xs text-muted-foreground truncate">
                      {enrollment.course} · {enrollment.lastAccess}
                    </p>
                  </div>
                  <div className="hidden sm:block w-24">
                    <AnimatedProgress value={enrollment.progress} size="sm" showValue />
                  </div>
                  <Badge variant={enrollmentStatusVariant[enrollment.status]}>
                    {enrollment.status}
                  </Badge>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <div className="space-y-6">
          <Card className="min-w-0">
            <CardHeader>
              <CardTitle>Top Courses</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {topCourses.map((course, i) => (
                  <div key={course.id} className="flex items-center gap-3 py-1.5">
                    <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-muted text-2xs font-bold text-muted-foreground">
                      {i + 1}
                    </span>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium truncate">{course.title}</p>
                      <p className="text-2xs text-muted-foreground">
                        {course.instructor} · {course.students} students
                      </p>
                    </div>
                    <div className="flex items-center gap-1">
                      <Star className="h-3 w-3 fill-warning text-warning" />
                      <span className="text-sm font-semibold">{course.rating}</span>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          <Card className="min-w-0">
            <CardHeader>
              <CardTitle>Top Instructors</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {topInstructors.map((instructor) => (
                  <div key={instructor.id} className="flex items-center gap-3 py-1.5">
                    <Avatar name={instructor.name} size="sm" />
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium truncate">{instructor.name}</p>
                      <p className="text-2xs text-muted-foreground">
                        <Award className="mr-0.5 inline h-3 w-3" />
                        {instructor.courses} courses · {instructor.students} students
                      </p>
                    </div>
                    <div className="flex items-center gap-1">
                      <Star className="h-3 w-3 fill-warning text-warning" />
                      <span className="text-sm font-semibold">{instructor.rating}</span>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
