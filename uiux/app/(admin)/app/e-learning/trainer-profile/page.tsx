import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { DollarSign, Star, Users } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Trainer Profile",
};

const courses = [
  { id: 1, title: "React Advanced Patterns", students: 1420, rating: 4.8, revenue: "$71,000" },
  { id: 2, title: "TypeScript Mastery", students: 1100, rating: 4.8, revenue: "$49,500" },
  { id: 3, title: "Next.js Full Stack", students: 860, rating: 4.7, revenue: "$43,000" },
];

export default function TrainerProfilePage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Trainer Profile" description="Instructor information and performance." />

      <div className="grid gap-6 lg:grid-cols-4">
        <Card>
          <CardContent className="p-5 text-center">
            <Avatar name="Sarah Chen" size="lg" />
            <h2 className="mt-3 text-lg font-semibold">Sarah Chen</h2>
            <p className="text-sm text-muted-foreground">Senior Instructor</p>
            <Badge variant="success" className="mt-2">
              Verified
            </Badge>
            <div className="mt-4 pt-4 border-t border-border text-left space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-muted-foreground">Joined</span>
                <span>Mar 2023</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Courses</span>
                <span>3</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Avg Rating</span>
                <span>⭐ 4.8</span>
              </div>
            </div>
            <Button variant="outline" className="w-full mt-4">
              Edit Profile
            </Button>
          </CardContent>
        </Card>

        <div className="lg:col-span-3 space-y-6">
          <div className="grid gap-4 sm:grid-cols-3">
            <GradientCard
              title="Total Students"
              value="3,380"
              change={18.5}
              variant="primary"
              icon={<Users className="h-4 w-4" />}
            />
            <GradientCard
              title="Total Revenue"
              value="$163.5K"
              change={22}
              variant="success"
              icon={<DollarSign className="h-4 w-4" />}
            />
            <GradientCard
              title="Avg. Rating"
              value="4.8/5"
              change={2.1}
              variant="sunset"
              icon={<Star className="h-4 w-4" />}
            />
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Published Courses</CardTitle>
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
                        Students
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Rating
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Revenue
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {courses.map((course) => (
                      <tr
                        key={course.id}
                        className="border-b border-border last:border-0 hover:bg-muted/50"
                      >
                        <td className="px-4 py-3 font-medium">{course.title}</td>
                        <td className="px-4 py-3">{course.students.toLocaleString()}</td>
                        <td className="px-4 py-3">⭐ {course.rating}</td>
                        <td className="px-4 py-3 font-medium">{course.revenue}</td>
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
