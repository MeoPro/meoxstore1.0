import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Pagination",
};

export default function PaginationPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Pagination" description="Page navigation for multi-page content." />

      <Card>
        <CardHeader>
          <CardTitle>Basic Pagination</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex gap-1">
            {[1, 2, 3, 4, 5].map((page) => (
              <button
                key={page}
                className={`h-9 w-9 rounded-lg text-sm font-medium transition-colors ${
                  page === 1
                    ? "bg-primary text-primary-foreground"
                    : "border border-border hover:bg-muted/50"
                }`}
              >
                {page}
              </button>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>With Prev/Next</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center gap-1">
            <button className="h-9 rounded-lg border border-border px-3 text-sm text-muted-foreground hover:bg-muted/50">
              ← Prev
            </button>
            {[1, 2, 3, "...", 8, 9, 10].map((page, i) => (
              <button
                key={i}
                className={`h-9 w-9 rounded-lg text-sm font-medium transition-colors ${
                  page === 3
                    ? "bg-primary text-primary-foreground"
                    : "border border-border hover:bg-muted/50"
                } ${page === "..." ? "pointer-events-none border-none" : ""}`}
              >
                {page}
              </button>
            ))}
            <button className="h-9 rounded-lg border border-border px-3 text-sm text-muted-foreground hover:bg-muted/50">
              Next →
            </button>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Simple Navigation</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-between">
            <button className="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted/50">
              Previous
            </button>
            <span className="text-sm text-muted-foreground">Page 3 of 10</span>
            <button className="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted/50">
              Next
            </button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
