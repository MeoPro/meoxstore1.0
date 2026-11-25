import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Navbar Vertical Layout",
};

export default function NavbarVerticalPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Navbar Vertical"
        description="Default vertical sidebar navigation layout."
      />
      <Card>
        <CardContent className="flex flex-col items-center justify-center py-12">
          <Badge variant="success" className="mb-4">
            Active Layout
          </Badge>
          <h3 className="text-lg font-semibold">Vertical Navbar Layout</h3>
          <p className="mt-2 text-center text-sm text-muted-foreground">
            This is the current active layout. The sidebar is positioned vertically on the left side
            with collapsible navigation groups.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
