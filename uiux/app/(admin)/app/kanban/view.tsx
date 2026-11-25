"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Plus } from "lucide-react";

const columns = [
  {
    id: "todo",
    title: "To Do",
    color: "default" as const,
    tasks: [
      { id: 1, title: "Design new landing page", assignee: "Sarah Chen", priority: "high" },
      { id: 2, title: "Write API documentation", assignee: "Mike Johnson", priority: "medium" },
      { id: 3, title: "Set up CI/CD pipeline", assignee: "Emma Davis", priority: "low" },
    ],
  },
  {
    id: "progress",
    title: "In Progress",
    color: "info" as const,
    tasks: [
      { id: 4, title: "Implement auth module", assignee: "James Wilson", priority: "high" },
      { id: 5, title: "Database migration", assignee: "Lisa Park", priority: "medium" },
    ],
  },
  {
    id: "review",
    title: "In Review",
    color: "warning" as const,
    tasks: [
      { id: 6, title: "Payment integration", assignee: "Sarah Chen", priority: "high" },
      { id: 7, title: "Unit tests for cart", assignee: "Mike Johnson", priority: "medium" },
    ],
  },
  {
    id: "done",
    title: "Done",
    color: "success" as const,
    tasks: [
      { id: 8, title: "User profile page", assignee: "Emma Davis", priority: "low" },
      { id: 9, title: "Email notifications", assignee: "James Wilson", priority: "medium" },
    ],
  },
];

const priorityColors: Record<string, "destructive" | "warning" | "default"> = {
  high: "destructive",
  medium: "warning",
  low: "default",
};

export default function KanbanPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Kanban Board"
        description="Manage tasks with drag and drop."
        actions={
          <Button>
            <Plus className="h-4 w-4" />
            Add Task
          </Button>
        }
      />

      <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        {columns.map((column) => (
          <Card key={column.id}>
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle>
                  <Badge variant={column.color} className="mr-2">
                    {column.tasks.length}
                  </Badge>
                  {column.title}
                </CardTitle>
              </div>
            </CardHeader>
            <CardContent className="space-y-3">
              {column.tasks.map((task) => (
                <div
                  key={task.id}
                  className="rounded-lg border border-border p-3 hover:shadow-card transition-shadow cursor-grab"
                >
                  <p className="text-sm font-medium mb-2">{task.title}</p>
                  <div className="flex items-center justify-between">
                    <Avatar name={task.assignee} size="sm" />
                    <Badge variant={priorityColors[task.priority]} className="text-2xs">
                      {task.priority}
                    </Badge>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
