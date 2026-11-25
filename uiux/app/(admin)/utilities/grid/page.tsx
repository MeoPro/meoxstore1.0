import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Grid",
};

export default function GridPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Grid" description="CSS Grid layout utilities with responsive columns." />

      <Card>
        <CardHeader>
          <CardTitle>2 Columns</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 gap-3">
            <div className="rounded-lg bg-primary/20 p-4 text-center text-sm">1</div>
            <div className="rounded-lg bg-primary/20 p-4 text-center text-sm">2</div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>3 Columns</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-3 gap-3">
            <div className="rounded-lg bg-green-500/20 p-4 text-center text-sm">1</div>
            <div className="rounded-lg bg-green-500/20 p-4 text-center text-sm">2</div>
            <div className="rounded-lg bg-green-500/20 p-4 text-center text-sm">3</div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>4 Columns</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-4 gap-3">
            <div className="rounded-lg bg-purple-500/20 p-4 text-center text-sm">1</div>
            <div className="rounded-lg bg-purple-500/20 p-4 text-center text-sm">2</div>
            <div className="rounded-lg bg-purple-500/20 p-4 text-center text-sm">3</div>
            <div className="rounded-lg bg-purple-500/20 p-4 text-center text-sm">4</div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Responsive Grid</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            {Array.from({ length: 8 }, (_, i) => (
              <div key={i} className="rounded-lg bg-blue-500/20 p-4 text-center text-sm">
                {i + 1}
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
