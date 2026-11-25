import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Badges",
};

export default function BadgesPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Badges" description="Small status labels and indicators." />

      <Card>
        <CardHeader>
          <CardTitle>All Variants</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-3">
          <Badge variant="default">Default</Badge>
          <Badge variant="success">Success</Badge>
          <Badge variant="destructive">Destructive</Badge>
          <Badge variant="warning">Warning</Badge>
          <Badge variant="info">Info</Badge>
          <Badge variant="secondary">Secondary</Badge>
          <Badge variant="outline">Outline</Badge>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Badges with Context</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          <div className="flex items-center justify-between rounded-lg border border-border px-4 py-3">
            <span className="text-sm font-medium">Notifications</span>
            <Badge variant="destructive">12</Badge>
          </div>
          <div className="flex items-center justify-between rounded-lg border border-border px-4 py-3">
            <span className="text-sm font-medium">Tasks Completed</span>
            <Badge variant="success">Done</Badge>
          </div>
          <div className="flex items-center justify-between rounded-lg border border-border px-4 py-3">
            <span className="text-sm font-medium">Build Status</span>
            <Badge variant="warning">In Progress</Badge>
          </div>
          <div className="flex items-center justify-between rounded-lg border border-border px-4 py-3">
            <span className="text-sm font-medium">Server Region</span>
            <Badge variant="info">US-East-1</Badge>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
