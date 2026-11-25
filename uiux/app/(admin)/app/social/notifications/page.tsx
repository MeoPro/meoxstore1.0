import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { AlertCircle, Bell, Heart, MessageCircle, UserPlus } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Notifications",
};

const notifications = [
  {
    id: 1,
    type: "mention",
    icon: "message",
    title: "Sarah Chen mentioned you",
    description: "in the Design Review thread",
    time: "5 min ago",
    read: false,
  },
  {
    id: 2,
    type: "like",
    icon: "heart",
    title: "Mike Johnson liked your post",
    description: '"Just shipped the new dashboard..."',
    time: "30 min ago",
    read: false,
  },
  {
    id: 3,
    type: "follow",
    icon: "user",
    title: "Emma Davis started following you",
    description: "Engineering Manager at DropX",
    time: "1 hour ago",
    read: false,
  },
  {
    id: 4,
    type: "alert",
    icon: "alert",
    title: "System alert",
    description: "Deployment to production completed successfully",
    time: "2 hours ago",
    read: true,
  },
  {
    id: 5,
    type: "mention",
    icon: "message",
    title: "James Wilson replied",
    description: "to your comment on PR #142",
    time: "3 hours ago",
    read: true,
  },
  {
    id: 6,
    type: "follow",
    icon: "user",
    title: "Lisa Park started following you",
    description: "DevOps Engineer at DropX",
    time: "5 hours ago",
    read: true,
  },
];

const iconMap = {
  message: MessageCircle,
  heart: Heart,
  user: UserPlus,
  alert: AlertCircle,
};

export default function NotificationsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Notifications"
        description="Stay updated on activity and mentions."
        actions={
          <Button variant="outline" size="sm">
            Mark all as read
          </Button>
        }
      />

      <Card>
        <CardContent className="p-0">
          <div className="divide-y divide-border">
            {notifications.map((notification) => {
              const Icon = iconMap[notification.icon as keyof typeof iconMap] || Bell;
              return (
                <div
                  key={notification.id}
                  className={`flex items-start gap-4 p-4 ${!notification.read ? "bg-primary/5" : ""}`}
                >
                  <div className="rounded-full bg-muted p-2">
                    <Icon className="h-4 w-4 text-muted-foreground" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className={`text-sm ${!notification.read ? "font-medium" : ""}`}>
                      {notification.title}
                    </p>
                    <p className="text-xs text-muted-foreground">{notification.description}</p>
                    <p className="text-xs text-muted-foreground mt-1">{notification.time}</p>
                  </div>
                  {!notification.read && (
                    <Badge variant="info" className="text-2xs">
                      New
                    </Badge>
                  )}
                </div>
              );
            })}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
