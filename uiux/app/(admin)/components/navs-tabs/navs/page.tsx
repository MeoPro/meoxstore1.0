import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Navigation",
};

export default function NavsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Navigation" description="Navigation pills and link styles." />

      <Card>
        <CardHeader>
          <CardTitle>Nav Pills</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex flex-wrap gap-1 rounded-xl bg-muted/50 p-1">
            <div className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">
              Dashboard
            </div>
            <div className="rounded-lg px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground cursor-pointer">
              Analytics
            </div>
            <div className="rounded-lg px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground cursor-pointer">
              Reports
            </div>
            <div className="rounded-lg px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground cursor-pointer">
              Settings
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Nav Links</CardTitle>
        </CardHeader>
        <CardContent>
          <nav className="flex flex-col gap-1">
            <div className="rounded-lg bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
              Overview
            </div>
            <div className="rounded-lg px-4 py-2 text-sm text-muted-foreground hover:bg-muted/50 cursor-pointer">
              Members
            </div>
            <div className="rounded-lg px-4 py-2 text-sm text-muted-foreground hover:bg-muted/50 cursor-pointer">
              Integrations
            </div>
            <div className="rounded-lg px-4 py-2 text-sm text-muted-foreground hover:bg-muted/50 cursor-pointer">
              Billing
            </div>
            <div className="rounded-lg px-4 py-2 text-sm text-muted-foreground opacity-50 cursor-not-allowed">
              Disabled
            </div>
          </nav>
        </CardContent>
      </Card>
    </div>
  );
}
