import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Sizing",
};

export default function SizingPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Sizing" description="Width and height utility classes with visual bars." />

      <Card>
        <CardHeader>
          <CardTitle>Width</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {[
            { cls: "w-1/4", label: "w-25 (25%)" },
            { cls: "w-1/2", label: "w-50 (50%)" },
            { cls: "w-3/4", label: "w-75 (75%)" },
            { cls: "w-full", label: "w-100 (100%)" },
          ].map((item) => (
            <div key={item.cls}>
              <div className={`${item.cls} rounded bg-primary/20 px-3 py-2 text-xs`}>
                {item.label}
              </div>
            </div>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Max Width</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {[
            { cls: "max-w-xs", label: "max-w-xs" },
            { cls: "max-w-sm", label: "max-w-sm" },
            { cls: "max-w-md", label: "max-w-md" },
            { cls: "max-w-lg", label: "max-w-lg" },
          ].map((item) => (
            <div key={item.cls} className={`${item.cls} rounded bg-green-500/20 px-3 py-2 text-xs`}>
              {item.label}
            </div>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Height</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-end gap-4">
            <div className="h-8 w-12 rounded bg-purple-500/20 text-center text-[10px] leading-8">
              h-8
            </div>
            <div className="h-12 w-12 rounded bg-purple-500/20 text-center text-[10px] leading-[3rem]">
              h-12
            </div>
            <div className="h-16 w-12 rounded bg-purple-500/20 text-center text-[10px] leading-[4rem]">
              h-16
            </div>
            <div className="h-20 w-12 rounded bg-purple-500/20 text-center text-[10px] leading-[5rem]">
              h-20
            </div>
            <div className="h-24 w-12 rounded bg-purple-500/20 text-center text-[10px] leading-[6rem]">
              h-24
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
