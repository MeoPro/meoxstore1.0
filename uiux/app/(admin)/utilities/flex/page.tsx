import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Flex",
};

export default function FlexPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Flex" description="Flexbox utility classes for layout control." />

      <Card>
        <CardHeader>
          <CardTitle>Direction</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex gap-2">
            <div className="rounded bg-primary/20 px-3 py-2 text-xs">Row 1</div>
            <div className="rounded bg-primary/20 px-3 py-2 text-xs">Row 2</div>
            <div className="rounded bg-primary/20 px-3 py-2 text-xs">Row 3</div>
          </div>
          <div className="flex flex-col gap-2">
            <div className="rounded bg-blue-500/20 px-3 py-2 text-xs">Col 1</div>
            <div className="rounded bg-blue-500/20 px-3 py-2 text-xs">Col 2</div>
            <div className="rounded bg-blue-500/20 px-3 py-2 text-xs">Col 3</div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Justify Content</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {[
            "justify-start",
            "justify-center",
            "justify-end",
            "justify-between",
            "justify-around",
          ].map((cls) => (
            <div key={cls} className={`flex ${cls} gap-2 rounded border border-border p-2`}>
              <div className="h-8 w-8 rounded bg-green-500/30" />
              <div className="h-8 w-8 rounded bg-green-500/30" />
              <div className="h-8 w-8 rounded bg-green-500/30" />
            </div>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Align Items</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {["items-start", "items-center", "items-end", "items-stretch"].map((cls) => (
            <div key={cls} className={`flex ${cls} h-20 gap-2 rounded border border-border p-2`}>
              <div className="w-10 rounded bg-purple-500/30 p-1 text-[10px]">A</div>
              <div className="w-10 rounded bg-purple-500/30 p-1 text-[10px]">B</div>
              <div className="w-10 rounded bg-purple-500/30 p-1 text-[10px]">C</div>
            </div>
          ))}
        </CardContent>
      </Card>
    </div>
  );
}
