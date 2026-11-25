import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Ticket Preview",
};

const replies = [
  {
    id: 1,
    user: "John Smith",
    role: "Customer",
    message:
      "I cannot log into the dashboard. It keeps showing an error after entering my credentials.",
    time: "Aug 15, 2026 10:30 AM",
  },
  {
    id: 2,
    user: "Sarah Chen",
    role: "Agent",
    message:
      "Hi John, I'm sorry about the inconvenience. Can you tell me which browser you're using and whether you see any specific error message?",
    time: "Aug 15, 2026 10:45 AM",
  },
  {
    id: 3,
    user: "John Smith",
    role: "Customer",
    message: "I'm using Chrome 120. The error says 'Authentication failed. Please try again.'",
    time: "Aug 15, 2026 11:00 AM",
  },
  {
    id: 4,
    user: "Sarah Chen",
    role: "Agent",
    message:
      "Thank you. I've checked your account and found the issue. Your session was stuck. I've cleared it. Please try logging in again.",
    time: "Aug 15, 2026 11:15 AM",
  },
];

export default function TicketPreviewPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Ticket #TKT-1042" description="Cannot login to dashboard" />

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Conversation</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {replies.map((reply) => (
                <div key={reply.id} className="rounded-lg border border-border p-4">
                  <div className="flex items-center justify-between mb-2">
                    <div className="flex items-center gap-2">
                      <Avatar name={reply.user} size="sm" />
                      <div>
                        <span className="text-sm font-medium">{reply.user}</span>
                        <Badge
                          variant={reply.role === "Agent" ? "info" : "default"}
                          className="ml-2 text-2xs"
                        >
                          {reply.role}
                        </Badge>
                      </div>
                    </div>
                    <span className="text-xs text-muted-foreground">{reply.time}</span>
                  </div>
                  <p className="text-sm text-muted-foreground">{reply.message}</p>
                </div>
              ))}
            </CardContent>
          </Card>
        </div>

        <Card className="h-fit">
          <CardHeader>
            <CardTitle>Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3 text-sm">
            <div className="flex justify-between">
              <span className="text-muted-foreground">Status</span>
              <Badge variant="info">Open</Badge>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">Priority</span>
              <Badge variant="warning">High</Badge>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">Assignee</span>
              <span>Sarah Chen</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">Created</span>
              <span>Aug 15, 2026</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">Category</span>
              <span>Authentication</span>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
