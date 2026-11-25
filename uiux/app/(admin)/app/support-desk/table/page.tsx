import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Plus } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Support Tickets - Table",
};

const tickets = [
  {
    id: "TKT-1042",
    subject: "Cannot login to dashboard",
    requester: "John Smith",
    assignee: "Sarah Chen",
    priority: "high",
    status: "open",
    created: "Aug 15, 2026",
  },
  {
    id: "TKT-1041",
    subject: "Payment processing error",
    requester: "Maria Garcia",
    assignee: "Mike Johnson",
    priority: "critical",
    status: "in-progress",
    created: "Aug 15, 2026",
  },
  {
    id: "TKT-1040",
    subject: "Feature request: Dark mode",
    requester: "David Lee",
    assignee: "Emma Davis",
    priority: "low",
    status: "open",
    created: "Aug 14, 2026",
  },
  {
    id: "TKT-1039",
    subject: "API rate limiting issue",
    requester: "Anna Wilson",
    assignee: "James Wilson",
    priority: "medium",
    status: "resolved",
    created: "Aug 14, 2026",
  },
  {
    id: "TKT-1038",
    subject: "Data export not working",
    requester: "Robert Taylor",
    assignee: "Lisa Park",
    priority: "high",
    status: "in-progress",
    created: "Aug 13, 2026",
  },
  {
    id: "TKT-1037",
    subject: "Email notifications delayed",
    requester: "Emily Brown",
    assignee: "Sarah Chen",
    priority: "medium",
    status: "resolved",
    created: "Aug 13, 2026",
  },
];

const priorityColors: Record<string, "destructive" | "warning" | "info" | "default"> = {
  critical: "destructive",
  high: "warning",
  medium: "info",
  low: "default",
};

const statusColors: Record<string, "info" | "warning" | "success" | "default"> = {
  open: "info",
  "in-progress": "warning",
  resolved: "success",
  closed: "default",
};

export default function SupportTablePage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Support Tickets"
        description="View all tickets in table format."
        actions={
          <Button>
            <Plus className="h-4 w-4" />
            New Ticket
          </Button>
        }
      />

      <Card>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="data-table w-full text-sm">
              <thead>
                <tr className="border-b border-border bg-muted/50">
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Ticket</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Subject</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                    Assignee
                  </th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                    Priority
                  </th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Created</th>
                </tr>
              </thead>
              <tbody>
                {tickets.map((ticket) => (
                  <tr
                    key={ticket.id}
                    className="border-b border-border last:border-0 hover:bg-muted/50"
                  >
                    <td className="px-4 py-3 font-medium">{ticket.id}</td>
                    <td className="px-4 py-3">{ticket.subject}</td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-2">
                        <Avatar name={ticket.assignee} size="sm" />
                        <span>{ticket.assignee}</span>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <Badge variant={priorityColors[ticket.priority]}>{ticket.priority}</Badge>
                    </td>
                    <td className="px-4 py-3">
                      <Badge variant={statusColors[ticket.status]}>{ticket.status}</Badge>
                    </td>
                    <td className="px-4 py-3 text-muted-foreground">{ticket.created}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
