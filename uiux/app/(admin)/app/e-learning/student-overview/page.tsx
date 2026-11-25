import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { BookOpen, Clock, GraduationCap } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Student Overview",
};

const enrolledCourses = [
  { id: 1, title: "React Advanced Patterns", progress: 78, grade: "A", status: "in-progress" },
  { id: 2, title: "TypeScript Mastery", progress: 100, grade: "A+", status: "completed" },
  { id: 3, title: "Cloud Architecture on AWS", progress: 35, grade: "B+", status: "in-progress" },
  { id: 4, title: "UI/UX Design Fundamentals", progress: 100, grade: "A", status: "completed" },
];

const statusColors: Record<string, "info" | "success"> = {
  "in-progress": "info",
  completed: "success",
};

export default function StudentOverviewPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Student Overview" description="Track student progress and performance." />

      <div className="grid gap-6 lg:grid-cols-4">
        <Card className="lg:col-span-1">
          <CardContent className="p-5 text-center">
            <Avatar name="Alex Thompson" size="lg" />
            <h2 className="mt-3 text-lg font-semibold">Alex Thompson</h2>
            <p className="text-sm text-muted-foreground">alex@student.edu</p>
            <Badge variant="info" className="mt-2">
              Active Student
            </Badge>
            <div className="mt-4 pt-4 border-t border-border text-left space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-muted-foreground">Joined</span>
                <span>Jan 2025</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Courses</span>
                <span>4</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Certificates</span>
                <span>2</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="lg:col-span-3 space-y-6">
          <div className="grid gap-4 sm:grid-cols-3">
            <GradientCard
              title="Overall GPA"
              value="3.8"
              change={5}
              variant="primary"
              icon={<GraduationCap className="h-4 w-4" />}
            />
            <GradientCard
              title="Hours Studied"
              value="124h"
              change={12}
              variant="ocean"
              icon={<Clock className="h-4 w-4" />}
            />
            <GradientCard
              title="Assignments"
              value="28/32"
              change={3}
              variant="success"
              icon={<BookOpen className="h-4 w-4" />}
            />
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Enrolled Courses</CardTitle>
            </CardHeader>
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <table className="data-table w-full text-sm">
                  <thead>
                    <tr className="border-b border-border bg-muted/50">
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Course
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Progress
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Grade
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Status
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {enrolledCourses.map((course) => (
                      <tr
                        key={course.id}
                        className="border-b border-border last:border-0 hover:bg-muted/50"
                      >
                        <td className="px-4 py-3 font-medium">{course.title}</td>
                        <td className="px-4 py-3">{course.progress}%</td>
                        <td className="px-4 py-3 font-medium">{course.grade}</td>
                        <td className="px-4 py-3">
                          <Badge variant={statusColors[course.status]}>{course.status}</Badge>
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
    </div>
  );
}
