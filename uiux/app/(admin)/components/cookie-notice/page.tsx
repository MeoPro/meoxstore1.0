import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Cookie Notice",
};

export default function CookieNoticePage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Cookie Notice" description="Cookie consent banner and card examples." />

      <Card>
        <CardHeader>
          <CardTitle>Bottom Bar Style</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="rounded-xl border border-border bg-card p-4">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <p className="text-sm text-muted-foreground">
                We use cookies to improve your experience. By continuing to use this site, you agree
                to our cookie policy.
              </p>
              <div className="flex gap-2">
                <Button size="sm">Accept All</Button>
                <Button size="sm" variant="outline">
                  Manage
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Floating Card Style</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="mx-auto max-w-sm rounded-xl border border-border bg-card p-6 shadow-dropdown">
            <div className="mb-1 text-lg">🍪</div>
            <h4 className="font-semibold">Cookie Preferences</h4>
            <p className="mt-2 text-sm text-muted-foreground">
              This website uses cookies to ensure you get the best experience on our website.
            </p>
            <div className="mt-4 space-y-2">
              <div className="flex items-center justify-between text-sm">
                <span>Essential</span>
                <span className="text-xs text-muted-foreground">Always on</span>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span>Analytics</span>
                <div className="h-5 w-9 rounded-full bg-primary/20 p-0.5">
                  <div className="h-4 w-4 rounded-full bg-primary" />
                </div>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span>Marketing</span>
                <div className="h-5 w-9 rounded-full bg-muted p-0.5">
                  <div className="h-4 w-4 rounded-full bg-muted-foreground/50" />
                </div>
              </div>
            </div>
            <div className="mt-4 flex gap-2">
              <Button size="sm" className="flex-1">
                Save
              </Button>
              <Button size="sm" variant="outline" className="flex-1">
                Decline
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
