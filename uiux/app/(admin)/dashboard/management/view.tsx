"use client";

import { PageHeader } from "@/components/layout/page-header";
import { AnimatedProgress } from "@/components/ui/animated-progress";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { Briefcase, CheckSquare, Timer, Users } from "lucide-react";
import {
  Bar,
  BarChart,
  CartesianGrid,
  Legend,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { axisTick, chartColors, tooltipProps } from "@/lib/chart-theme";

const tasksByProject = [
  { project: "Website Redesign", todo: 4, inProgress: 6, done: 18 },
  { project: "Mobile App", todo: 8, inProgress: 5, done: 12 },
  { project: "API Migration", todo: 3, inProgress: 4, done: 22 },
  { project: "CRM Integration", todo: 6, inProgress: 3, done: 9 },
  { project: "Analytics v2", todo: 5, inProgress: 7, done: 14 },
];

const projects = [
  { name: "Website Redesign", progress: 82, deadline: "Aug 20, 2026", status: "On Track" },
  { name: "Mobile App v2.0", progress: 48, deadline: "Sep 15, 2026", status: "On Track" },
  { name: "API Migration", progress: 91, deadline: "Aug 10, 2026", status: "At Risk" },
  { name: "CRM Integration", progress: 35, deadline: "Oct 1, 2026", status: "On Track" },
  { name: "Analytics Platform v2", progress: 64, deadline: "Sep 30, 2026", status: "Behind" },
];

const teamPerformance = [
  { name: "Frontend Team", tasksCompleted: 142, velocity: 28, satisfaction: 92 },
  { name: "Backend Team", tasksCompleted: 168, velocity: 32, satisfaction: 88 },
  { name: "Design Team", tasksCompleted: 96, velocity: 18, satisfaction: 95 },
  { name: "DevOps Team", tasksCompleted: 84, velocity: 15, satisfaction: 90 },
];

const statusVariant: Record<string, "success" | "warning" | "danger"> = {
  "On Track": "success",
  "At Risk": "warning",
  Behind: "danger",
};

export default function ManagementDashboard() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Project Management"
        description="Track projects, tasks, and team productivity."
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <GradientCard
          title="Active Projects"
          value="12"
          change={2}
          variant="primary"
          icon={<Briefcase className="h-4 w-4" />}
        />
        <GradientCard
          title="Tasks Completed"
          value="847"
          change={15.3}
          variant="success"
          icon={<CheckSquare className="h-4 w-4" />}
        />
        <GradientCard
          title="Team Members"
          value="24"
          change={4}
          variant="ocean"
          icon={<Users className="h-4 w-4" />}
        />
        <GradientCard
          title="Deadlines This Week"
          value="5"
          variant="warning"
          icon={<Timer className="h-4 w-4" />}
        />
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Tasks by Project & Status</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-[300px]">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={tasksByProject}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                <XAxis dataKey="project" tick={axisTick} axisLine={false} tickLine={false} />
                <YAxis tick={axisTick} axisLine={false} tickLine={false} />
                <Tooltip {...tooltipProps} />
                <Legend wrapperStyle={{ fontSize: "11px" }} />
                <Bar
                  dataKey="todo"
                  stackId="a"
                  fill={chartColors.warning}
                  name="To Do"
                  radius={[0, 0, 0, 0]}
                />
                <Bar
                  dataKey="inProgress"
                  stackId="a"
                  fill={chartColors.primary}
                  name="In Progress"
                />
                <Bar
                  dataKey="done"
                  stackId="a"
                  fill={chartColors.success}
                  name="Done"
                  radius={[3, 3, 0, 0]}
                />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </CardContent>
      </Card>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Project Progress</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {projects.map((project) => (
              <div key={project.name} className="space-y-1.5">
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium">{project.name}</span>
                  <Badge variant={statusVariant[project.status]}>{project.status}</Badge>
                </div>
                <AnimatedProgress value={project.progress} size="sm" variant="primary" showValue />
                <p className="text-2xs text-muted-foreground">Deadline: {project.deadline}</p>
              </div>
            ))}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Team Performance</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              <div className="grid grid-cols-4 text-xs font-medium text-muted-foreground border-b border-border pb-2">
                <span>Team</span>
                <span className="text-center">Tasks</span>
                <span className="text-center">Velocity</span>
                <span className="text-right">Score</span>
              </div>
              {teamPerformance.map((team) => (
                <div key={team.name} className="grid grid-cols-4 items-center text-sm py-2">
                  <span className="font-medium">{team.name}</span>
                  <span className="text-center text-muted-foreground">{team.tasksCompleted}</span>
                  <span className="text-center text-muted-foreground">{team.velocity}/wk</span>
                  <span className="text-right font-semibold">{team.satisfaction}%</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
