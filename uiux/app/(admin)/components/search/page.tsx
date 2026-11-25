import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Search",
};

export default function SearchPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Search" description="Search input components and patterns." />

      <Card>
        <CardHeader>
          <CardTitle>Basic Search</CardTitle>
        </CardHeader>
        <CardContent>
          <input
            type="text"
            placeholder="Search..."
            className="w-full rounded-xl border border-border bg-transparent px-4 py-2.5 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
          />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>With Icon</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative">
            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
              🔍
            </span>
            <input
              type="text"
              placeholder="Search products, orders..."
              className="w-full rounded-xl border border-border bg-transparent py-2.5 pl-10 pr-4 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>With Suggestions</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative">
            <input
              type="text"
              placeholder="Type to search..."
              defaultValue="Dash"
              className="w-full rounded-xl border border-border bg-transparent px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
            <div className="mt-1 rounded-xl border border-border bg-card p-1 shadow-dropdown">
              <div className="rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                Dashboard
              </div>
              <div className="rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                Data Analytics
              </div>
              <div className="rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                Database Settings
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
