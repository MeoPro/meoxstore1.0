import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { Clock, Inbox, Ticket } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Contact Detail",
};

const recentTickets = [
  { id: "TKT-1042", subject: "Cannot login to dashboard", status: "open", date: "Aug 15, 2026" },
  { id: "TKT-1030", subject: "Slow loading times", status: "resolved", date: "Aug 10, 2026" },
  { id: "TKT-1015", subject: "Password reset issue", status: "resolved", date: "Aug 5, 2026" },
];

const statusColors: Record<string, "info" | "success"> = {
  open: "info",
  resolved: "success",
};

export default function ContactDetailPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Contact Detail" description="View contact support history." />

      <div className="grid gap-6 lg:grid-cols-3">
        <Card>
          <CardContent className="p-5 text-center">
            <Avatar name="John Smith" size="lg" />
            <h2 className="mt-3 text-lg font-semibold">John Smith</h2>
            <p className="text-sm text-muted-foreground">john@example.com</p>
            <p className="text-sm text-muted-foreground">Acme Corp</p>
            <Badge variant="success" className="mt-2">
              Active
            </Badge>
            <div className="mt-4 pt-4 border-t border-border text-left space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-muted-foreground">Phone</span>
                <span>+1 555-0101</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Plan</span>
                <span>Enterprise</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Since</span>
                <span>Jan 2024</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="lg:col-span-2 space-y-6">
          <div className="grid gap-4 sm:grid-cols-3">
            <GradientCard
              title="Total Tickets"
              value="5"
              variant="primary"
              icon={<Ticket className="h-4 w-4" />}
            />
            <GradientCard
              title="Open"
              value="1"
              change={-50}
              variant="warning"
              icon={<Inbox className="h-4 w-4" />}
            />
            <GradientCard
              title="Avg. Resolution"
              value="4.2h"
              change={-12}
              variant="ocean"
              icon={<Clock className="h-4 w-4" />}
            />
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Recent Tickets</CardTitle>
            </CardHeader>
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <table className="data-table w-full text-sm">
                  <thead>
                    <tr className="border-b border-border bg-muted/50">
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Ticket
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Subject
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Status
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Date
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {recentTickets.map((ticket) => (
                      <tr
                        key={ticket.id}
                        className="border-b border-border last:border-0 hover:bg-muted/50"
                      >
                        <td className="px-4 py-3 font-medium">{ticket.id}</td>
                        <td className="px-4 py-3">{ticket.subject}</td>
                        <td className="px-4 py-3">
                          <Badge variant={statusColors[ticket.status]}>{ticket.status}</Badge>
                        </td>
                        <td className="px-4 py-3 text-muted-foreground">{ticket.date}</td>
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
