import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Colored Links",
};

export default function ColoredLinksPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Colored Links"
        description="Links styled with different color utilities."
      />

      <Card>
        <CardHeader>
          <CardTitle>Link Colors</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <p>
              <a href="#" className="text-primary underline hover:text-primary/80">
                Primary link
              </a>
            </p>
            <p>
              <a href="#" className="text-green-600 underline hover:text-green-500">
                Success link
              </a>
            </p>
            <p>
              <a href="#" className="text-destructive underline hover:text-destructive/80">
                Destructive link
              </a>
            </p>
            <p>
              <a href="#" className="text-yellow-600 underline hover:text-yellow-500">
                Warning link
              </a>
            </p>
            <p>
              <a href="#" className="text-blue-600 underline hover:text-blue-500">
                Info link
              </a>
            </p>
            <p>
              <a href="#" className="text-muted-foreground underline hover:text-foreground">
                Muted link
              </a>
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Links in Context</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            This is a paragraph with an{" "}
            <a href="#" className="text-primary underline hover:text-primary/80">
              inline primary link
            </a>
            , a{" "}
            <a href="#" className="text-green-600 underline hover:text-green-500">
              success link
            </a>
            , and a{" "}
            <a href="#" className="text-destructive underline hover:text-destructive/80">
              destructive link
            </a>{" "}
            demonstrating links within text content.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
