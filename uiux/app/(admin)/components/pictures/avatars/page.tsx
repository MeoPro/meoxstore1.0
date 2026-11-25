import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Avatars",
};

export default function AvatarsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Avatars"
        description="User avatar components in various sizes and styles."
      />

      <Card>
        <CardHeader>
          <CardTitle>Sizes</CardTitle>
        </CardHeader>
        <CardContent className="flex items-end gap-4">
          <Avatar name="Alice Johnson" size="sm" />
          <Avatar name="Bob Smith" size="md" />
          <Avatar name="Carol White" size="lg" />
          <Avatar name="David Brown" size="xl" />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>With Images</CardTitle>
        </CardHeader>
        <CardContent className="flex items-center gap-4">
          <Avatar name="User 1" size="md" src="/images/avatars/avatar-1.svg" />
          <Avatar name="User 2" size="md" src="/images/avatars/avatar-2.svg" />
          <Avatar name="User 3" size="md" src="/images/avatars/avatar-3.svg" />
          <Avatar name="User 4" size="md" src="/images/avatars/avatar-4.svg" />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Avatar Group</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex -space-x-3">
            <Avatar name="Alice Johnson" size="md" className="ring-2 ring-card" />
            <Avatar name="Bob Smith" size="md" className="ring-2 ring-card" />
            <Avatar name="Carol White" size="md" className="ring-2 ring-card" />
            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-muted text-xs font-medium ring-2 ring-card">
              +5
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
