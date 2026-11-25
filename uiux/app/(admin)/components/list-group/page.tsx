import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "List Group",
};

export default function ListGroupPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="List Group" description="Grouped list items in various styles." />

      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Basic List</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="rounded-xl border border-border divide-y divide-border">
              <div className="px-4 py-3 text-sm">First item</div>
              <div className="px-4 py-3 text-sm">Second item</div>
              <div className="px-4 py-3 text-sm">Third item</div>
              <div className="px-4 py-3 text-sm">Fourth item</div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>With Badges</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="rounded-xl border border-border divide-y divide-border">
              <div className="flex items-center justify-between px-4 py-3 text-sm">
                <span>Inbox</span>
                <Badge variant="default">14</Badge>
              </div>
              <div className="flex items-center justify-between px-4 py-3 text-sm">
                <span>Sent</span>
                <Badge variant="secondary">2</Badge>
              </div>
              <div className="flex items-center justify-between px-4 py-3 text-sm">
                <span>Drafts</span>
                <Badge variant="warning">1</Badge>
              </div>
              <div className="flex items-center justify-between px-4 py-3 text-sm">
                <span>Spam</span>
                <Badge variant="destructive">99+</Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>With Actions</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="rounded-xl border border-border divide-y divide-border">
              <div className="flex items-center justify-between px-4 py-3 text-sm hover:bg-muted/50 cursor-pointer">
                <span>Project Alpha</span>
                <span className="text-muted-foreground">→</span>
              </div>
              <div className="flex items-center justify-between px-4 py-3 text-sm hover:bg-muted/50 cursor-pointer">
                <span>Project Beta</span>
                <span className="text-muted-foreground">→</span>
              </div>
              <div className="flex items-center justify-between px-4 py-3 text-sm hover:bg-muted/50 cursor-pointer">
                <span>Project Gamma</span>
                <span className="text-muted-foreground">→</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Flush (No Border)</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="divide-y divide-border">
              <div className="px-4 py-3 text-sm">Flush item one</div>
              <div className="px-4 py-3 text-sm bg-muted/30">Flush item two (active)</div>
              <div className="px-4 py-3 text-sm">Flush item three</div>
              <div className="px-4 py-3 text-sm">Flush item four</div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
