import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Starter Page",
};

export default function StarterPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Starter Page" description="A blank starting point for new pages." />

      <Card>
        <CardHeader>
          <CardTitle>Welcome</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-muted-foreground">
            Use this as a starting point for your pages. Add your content, components, and logic
            here.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
