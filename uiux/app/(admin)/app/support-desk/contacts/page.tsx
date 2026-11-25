import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Plus } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Support Contacts",
};

const contacts = [
  {
    id: 1,
    name: "John Smith",
    email: "john@example.com",
    company: "Acme Corp",
    tickets: 5,
    status: "active",
  },
  {
    id: 2,
    name: "Maria Garcia",
    email: "maria@startup.io",
    company: "Startup.io",
    tickets: 3,
    status: "active",
  },
  {
    id: 3,
    name: "David Lee",
    email: "david@enterprise.com",
    company: "Enterprise Co",
    tickets: 8,
    status: "active",
  },
  {
    id: 4,
    name: "Anna Wilson",
    email: "anna@tech.co",
    company: "TechCo",
    tickets: 2,
    status: "inactive",
  },
  {
    id: 5,
    name: "Robert Taylor",
    email: "robert@bigcorp.com",
    company: "BigCorp",
    tickets: 12,
    status: "active",
  },
  {
    id: 6,
    name: "Emily Brown",
    email: "emily@agency.com",
    company: "Creative Agency",
    tickets: 1,
    status: "inactive",
  },
];

export default function SupportContactsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Contacts"
        description="Manage support contacts."
        actions={
          <Button>
            <Plus className="h-4 w-4" />
            Add Contact
          </Button>
        }
      />

      <Card>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="data-table w-full text-sm">
              <thead>
                <tr className="border-b border-border bg-muted/50">
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Contact</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Company</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Tickets</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                </tr>
              </thead>
              <tbody>
                {contacts.map((contact) => (
                  <tr
                    key={contact.id}
                    className="border-b border-border last:border-0 hover:bg-muted/50"
                  >
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-3">
                        <Avatar name={contact.name} size="sm" />
                        <div>
                          <p className="font-medium">{contact.name}</p>
                          <p className="text-xs text-muted-foreground">{contact.email}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-3">{contact.company}</td>
                    <td className="px-4 py-3">{contact.tickets}</td>
                    <td className="px-4 py-3">
                      <Badge variant={contact.status === "active" ? "success" : "default"}>
                        {contact.status}
                      </Badge>
                    </td>
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
