import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import { Clock } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Support Tickets - Cards",
};

const tickets = [
  {
    id: "TKT-1042",
    subject: "Cannot login to dashboard",
    requester: "John Smith",
    assignee: "Sarah Chen",
    priority: "high",
    status: "open",
    time: "2h ago",
  },
  {
    id: "TKT-1041",
    subject: "Payment processing error",
    requester: "Maria Garcia",
    assignee: "Mike Johnson",
    priority: "critical",
    status: "in-progress",
    time: "4h ago",
  },
  {
    id: "TKT-1040",
    subject: "Feature request: Dark mode",
    requester: "David Lee",
    assignee: "Emma Davis",
    priority: "low",
    status: "open",
    time: "6h ago",
  },
  {
    id: "TKT-1039",
    subject: "API rate limiting issue",
    requester: "Anna Wilson",
    assignee: "James Wilson",
    priority: "medium",
    status: "resolved",
    time: "8h ago",
  },
  {
    id: "TKT-1038",
    subject: "Data export not working",
    requester: "Robert Taylor",
    assignee: "Lisa Park",
    priority: "high",
    status: "in-progress",
    time: "12h ago",
  },
  {
    id: "TKT-1037",
    subject: "Email notifications delayed",
    requester: "Emily Brown",
    assignee: "Sarah Chen",
    priority: "medium",
    status: "resolved",
    time: "1 day ago",
  },
];

const priorityColors: Record<string, "destructive" | "warning" | "info" | "default"> = {
  critical: "destructive",
  high: "warning",
  medium: "info",
  low: "default",
};

export default function SupportCardsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Support Tickets" description="View tickets in card format." />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {tickets.map((ticket) => (
          <Card key={ticket.id} className="hover:shadow-dropdown transition-shadow cursor-pointer">
            <CardContent className="p-5">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-medium text-muted-foreground">{ticket.id}</span>
                <Badge variant={priorityColors[ticket.priority]}>{ticket.priority}</Badge>
              </div>
              <h3 className="text-sm font-medium mb-3">{ticket.subject}</h3>
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Avatar name={ticket.assignee} size="sm" />
                  <span className="text-xs text-muted-foreground">{ticket.assignee}</span>
                </div>
                <span className="flex items-center gap-1 text-xs text-muted-foreground">
                  <Clock className="h-3 w-3" /> {ticket.time}
                </span>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
