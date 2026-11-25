import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Tree View",
};

export default function TreeViewPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Tree View" description="Hierarchical tree structure with nested items." />

      <Card>
        <CardHeader>
          <CardTitle>File Explorer</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-0.5 text-sm">
            <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 font-medium">📁 src</div>
            <div className="ml-4 space-y-0.5">
              <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 font-medium">
                📁 components
              </div>
              <div className="ml-4 space-y-0.5">
                <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-muted-foreground hover:bg-muted/50 cursor-pointer">
                  📄 Button.tsx
                </div>
                <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-muted-foreground hover:bg-muted/50 cursor-pointer">
                  📄 Card.tsx
                </div>
                <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-muted-foreground hover:bg-muted/50 cursor-pointer">
                  📄 Modal.tsx
                </div>
              </div>
              <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 font-medium">
                📁 pages
              </div>
              <div className="ml-4 space-y-0.5">
                <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-muted-foreground hover:bg-muted/50 cursor-pointer">
                  📄 index.tsx
                </div>
                <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-muted-foreground hover:bg-muted/50 cursor-pointer">
                  📄 about.tsx
                </div>
              </div>
              <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 font-medium">
                📁 utils
              </div>
              <div className="ml-4 space-y-0.5">
                <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-muted-foreground hover:bg-muted/50 cursor-pointer">
                  📄 helpers.ts
                </div>
                <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-muted-foreground hover:bg-muted/50 cursor-pointer">
                  📄 constants.ts
                </div>
              </div>
            </div>
            <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-muted-foreground hover:bg-muted/50 cursor-pointer">
              📄 package.json
            </div>
            <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-muted-foreground hover:bg-muted/50 cursor-pointer">
              📄 tsconfig.json
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
