import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Activity Log",
};

const activities = [
  {
    id: 1,
    user: "Sarah Chen",
    action: "pushed 3 commits to",
    target: "feature/dashboard",
    type: "code",
    time: "5 min ago",
  },
  {
    id: 2,
    user: "Mike Johnson",
    action: "commented on",
    target: "PR #142 - Auth refactor",
    type: "comment",
    time: "15 min ago",
  },
  {
    id: 3,
    user: "Emma Davis",
    action: "merged",
    target: "PR #138 - Fix memory leak",
    type: "merge",
    time: "1 hour ago",
  },
  {
    id: 4,
    user: "James Wilson",
    action: "created issue",
    target: "#256 - API timeout errors",
    type: "issue",
    time: "2 hours ago",
  },
  {
    id: 5,
    user: "Lisa Park",
    action: "deployed",
    target: "v2.4.1 to production",
    type: "deploy",
    time: "3 hours ago",
  },
  {
    id: 6,
    user: "Sarah Chen",
    action: "assigned",
    target: "Design review to @emma",
    type: "assign",
    time: "4 hours ago",
  },
  {
    id: 7,
    user: "Mike Johnson",
    action: "closed issue",
    target: "#248 - Login redirect bug",
    type: "close",
    time: "5 hours ago",
  },
  {
    id: 8,
    user: "Emma Davis",
    action: "updated wiki page",
    target: "Onboarding Guide",
    type: "docs",
    time: "6 hours ago",
  },
];

const typeColors: Record<
  string,
  "info" | "success" | "warning" | "destructive" | "default" | "secondary"
> = {
  code: "info",
  comment: "default",
  merge: "success",
  issue: "warning",
  deploy: "success",
  assign: "secondary",
  close: "destructive",
  docs: "info",
};

export default function ActivityLogPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Activity Log" description="Track all team activities and changes." />

      <Card>
        <CardContent className="p-0">
          <div className="divide-y divide-border">
            {activities.map((activity) => (
              <div key={activity.id} className="flex items-center gap-4 p-4">
                <Avatar name={activity.user} size="sm" />
                <div className="flex-1 min-w-0">
                  <p className="text-sm">
                    <span className="font-medium">{activity.user}</span> {activity.action}{" "}
                    <span className="font-medium">{activity.target}</span>
                  </p>
                  <p className="text-xs text-muted-foreground">{activity.time}</p>
                </div>
                <Badge variant={typeColors[activity.type]}>{activity.type}</Badge>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
