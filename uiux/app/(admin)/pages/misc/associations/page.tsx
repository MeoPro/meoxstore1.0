import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Associations",
};

const members = [
  { name: "Alice Johnson", role: "President", email: "alice@example.com" },
  { name: "Bob Smith", role: "Vice President", email: "bob@example.com" },
  { name: "Carol Williams", role: "Secretary", email: "carol@example.com" },
  { name: "David Brown", role: "Treasurer", email: "david@example.com" },
  { name: "Eva Martinez", role: "Member", email: "eva@example.com" },
  { name: "Frank Lee", role: "Member", email: "frank@example.com" },
];

export default function AssociationsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Associations"
        description="Team associations and member directory."
        actions={<Button>Add Member</Button>}
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {members.map((member) => (
          <Card key={member.email}>
            <CardContent className="p-5 text-center">
              <div className="mx-auto h-14 w-14 rounded-full bg-primary/10 flex items-center justify-center text-lg font-bold text-primary">
                {member.name
                  .split(" ")
                  .map((n) => n[0])
                  .join("")}
              </div>
              <h3 className="mt-3 font-semibold">{member.name}</h3>
              <p className="text-sm text-muted-foreground">{member.role}</p>
              <p className="mt-1 text-xs text-muted-foreground">{member.email}</p>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
