import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Vertical Align",
};

export default function VerticalAlignPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Vertical Align"
        description="Vertical alignment utilities for inline and table-cell elements."
      />

      <Card>
        <CardHeader>
          <CardTitle>Inline Alignment</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="rounded-lg border border-border p-4 leading-[4rem]">
            <span className="inline-block h-16 w-0 align-middle" />
            <span className="inline-block align-top rounded bg-primary/20 px-2 py-1 text-xs leading-normal">
              top
            </span>{" "}
            <span className="inline-block align-middle rounded bg-green-500/20 px-2 py-1 text-xs leading-normal">
              middle
            </span>{" "}
            <span className="inline-block align-bottom rounded bg-blue-500/20 px-2 py-1 text-xs leading-normal">
              bottom
            </span>{" "}
            <span className="inline-block align-baseline rounded bg-yellow-500/20 px-2 py-1 text-xs leading-normal">
              baseline
            </span>{" "}
            <span className="inline-block align-text-top rounded bg-purple-500/20 px-2 py-1 text-xs leading-normal">
              text-top
            </span>{" "}
            <span className="inline-block align-text-bottom rounded bg-pink-500/20 px-2 py-1 text-xs leading-normal">
              text-bottom
            </span>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Table Cell Alignment</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-3 gap-4">
            <div className="inline-grid h-32 place-items-start rounded-lg border border-border p-3">
              <div className="rounded bg-primary/20 px-2 py-1 text-xs">align-top</div>
            </div>
            <div className="inline-grid h-32 place-items-center rounded-lg border border-border p-3">
              <div className="rounded bg-green-500/20 px-2 py-1 text-xs">align-middle</div>
            </div>
            <div className="inline-grid h-32 place-items-end rounded-lg border border-border p-3">
              <div className="rounded bg-blue-500/20 px-2 py-1 text-xs">align-bottom</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
