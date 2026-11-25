import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Clock, Users } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Courses",
};

const courses = [
  {
    id: 1,
    title: "React Advanced Patterns",
    instructor: "Sarah Chen",
    students: 1420,
    duration: "12h",
    level: "Advanced",
    rating: 4.8,
    price: "$49.99",
  },
  {
    id: 2,
    title: "Python for Data Science",
    instructor: "Mike Johnson",
    students: 2150,
    duration: "24h",
    level: "Intermediate",
    rating: 4.7,
    price: "$59.99",
  },
  {
    id: 3,
    title: "UI/UX Design Fundamentals",
    instructor: "Emma Davis",
    students: 980,
    duration: "8h",
    level: "Beginner",
    rating: 4.9,
    price: "$39.99",
  },
  {
    id: 4,
    title: "Cloud Architecture on AWS",
    instructor: "James Wilson",
    students: 750,
    duration: "16h",
    level: "Advanced",
    rating: 4.6,
    price: "$69.99",
  },
  {
    id: 5,
    title: "Machine Learning Basics",
    instructor: "Lisa Park",
    students: 1890,
    duration: "20h",
    level: "Intermediate",
    rating: 4.5,
    price: "$54.99",
  },
  {
    id: 6,
    title: "TypeScript Mastery",
    instructor: "David Kim",
    students: 1100,
    duration: "10h",
    level: "Intermediate",
    rating: 4.8,
    price: "$44.99",
  },
];

const levelColors: Record<string, "success" | "info" | "warning"> = {
  Beginner: "success",
  Intermediate: "info",
  Advanced: "warning",
};

export default function CoursePage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Courses" description="Browse and manage available courses." />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {courses.map((course) => (
          <Card key={course.id} className="overflow-hidden hover:shadow-dropdown transition-shadow">
            <div className="h-32 bg-muted flex items-center justify-center">
              <span className="text-muted-foreground text-xs">Course Thumbnail</span>
            </div>
            <CardContent className="p-5">
              <div className="flex items-center justify-between mb-2">
                <Badge variant={levelColors[course.level]}>{course.level}</Badge>
                <span className="text-xs text-muted-foreground">⭐ {course.rating}</span>
              </div>
              <h3 className="text-sm font-medium mb-1">{course.title}</h3>
              <p className="text-xs text-muted-foreground mb-3">by {course.instructor}</p>
              <div className="flex items-center gap-4 text-xs text-muted-foreground mb-3">
                <span className="flex items-center gap-1">
                  <Users className="h-3 w-3" /> {course.students.toLocaleString()}
                </span>
                <span className="flex items-center gap-1">
                  <Clock className="h-3 w-3" /> {course.duration}
                </span>
              </div>
              <div className="flex items-center justify-between">
                <span className="font-bold">{course.price}</span>
                <Button size="sm">Enroll</Button>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
