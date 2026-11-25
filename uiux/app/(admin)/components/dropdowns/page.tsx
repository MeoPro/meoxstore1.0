import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Dropdowns",
};

export default function DropdownsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Dropdowns" description="Dropdown menu examples and patterns." />

      <div className="grid gap-6 md:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle>Basic Dropdown</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="rounded-xl border border-border">
              <div className="border-b border-border px-4 py-2 text-sm font-medium text-muted-foreground">
                Menu
              </div>
              <div className="p-1">
                <div className="rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                  Action
                </div>
                <div className="rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                  Another action
                </div>
                <div className="rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                  Something else
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>With Dividers</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="rounded-xl border border-border p-1">
              <div className="rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                Edit
              </div>
              <div className="rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                Duplicate
              </div>
              <div className="my-1 border-t border-border" />
              <div className="rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                Archive
              </div>
              <div className="my-1 border-t border-border" />
              <div className="rounded-lg px-3 py-2 text-sm text-destructive hover:bg-destructive/10 cursor-pointer">
                Delete
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>With Icons</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="rounded-xl border border-border p-1">
              <div className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                ✏️ Edit
              </div>
              <div className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                📋 Copy
              </div>
              <div className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                📁 Move to
              </div>
              <div className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-muted/50 cursor-pointer">
                ⭐ Favorite
              </div>
              <div className="my-1 border-t border-border" />
              <div className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-destructive hover:bg-destructive/10 cursor-pointer">
                🗑️ Delete
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
