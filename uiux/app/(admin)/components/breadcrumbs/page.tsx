import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Breadcrumbs",
};

export default function BreadcrumbsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Breadcrumbs" description="Navigation breadcrumb trail examples." />

      <Card>
        <CardHeader>
          <CardTitle>Simple Breadcrumb</CardTitle>
        </CardHeader>
        <CardContent>
          <nav className="flex text-sm">
            <span className="text-primary hover:underline cursor-pointer">Home</span>
            <span className="mx-2 text-muted-foreground">/</span>
            <span className="text-primary hover:underline cursor-pointer">Components</span>
            <span className="mx-2 text-muted-foreground">/</span>
            <span className="text-muted-foreground">Breadcrumbs</span>
          </nav>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>With Icons</CardTitle>
        </CardHeader>
        <CardContent>
          <nav className="flex items-center text-sm">
            <span className="flex items-center gap-1 text-primary hover:underline cursor-pointer">
              🏠 Home
            </span>
            <span className="mx-2 text-muted-foreground">›</span>
            <span className="flex items-center gap-1 text-primary hover:underline cursor-pointer">
              📦 Products
            </span>
            <span className="mx-2 text-muted-foreground">›</span>
            <span className="text-muted-foreground">Details</span>
          </nav>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Custom Separator</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <nav className="flex items-center text-sm">
            <span className="text-primary hover:underline cursor-pointer">Dashboard</span>
            <span className="mx-2 text-muted-foreground">→</span>
            <span className="text-primary hover:underline cursor-pointer">Settings</span>
            <span className="mx-2 text-muted-foreground">→</span>
            <span className="text-muted-foreground">Account</span>
          </nav>
          <nav className="flex items-center text-sm">
            <span className="text-primary hover:underline cursor-pointer">Home</span>
            <span className="mx-2 text-muted-foreground">•</span>
            <span className="text-primary hover:underline cursor-pointer">Library</span>
            <span className="mx-2 text-muted-foreground">•</span>
            <span className="text-muted-foreground">Data</span>
          </nav>
        </CardContent>
      </Card>
    </div>
  );
}
