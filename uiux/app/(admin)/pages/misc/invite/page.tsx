import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Invite People",
};

export default function InvitePage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Invite People" description="Send invitations to join your workspace." />

      <Card className="max-w-lg">
        <CardHeader>
          <CardTitle>Send Invitation</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <Input label="Email Address" type="email" placeholder="colleague@company.com" />
          <Select label="Role">
            <option>Viewer</option>
            <option>Editor</option>
            <option>Admin</option>
          </Select>
          <Input label="Personal Message (optional)" placeholder="Hey, join our team!" />
          <Button>Send Invite</Button>
        </CardContent>
      </Card>
    </div>
  );
}
