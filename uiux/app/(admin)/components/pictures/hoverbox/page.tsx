import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Hoverbox",
};

export default function HoverboxPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Hoverbox" description="Image containers with hover overlay effects." />

      <Card>
        <CardHeader>
          <CardTitle>Hover Effects</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div className="group relative overflow-hidden rounded-xl">
              <div className="h-48 bg-gradient-to-br from-primary/30 to-primary/10 transition-transform duration-300 group-hover:scale-110" />
              <div className="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors duration-300 group-hover:bg-black/50">
                <span className="text-sm font-medium text-white opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                  Zoom Effect
                </span>
              </div>
            </div>

            <div className="group relative overflow-hidden rounded-xl">
              <div className="h-48 bg-gradient-to-br from-success/30 to-success/10" />
              <div className="absolute inset-x-0 bottom-0 translate-y-full bg-gradient-to-t from-black/80 to-transparent p-4 transition-transform duration-300 group-hover:translate-y-0">
                <span className="text-sm font-medium text-white">Slide Up Overlay</span>
              </div>
            </div>

            <div className="group relative overflow-hidden rounded-xl">
              <div className="h-48 bg-gradient-to-br from-warning/30 to-warning/10 transition-all duration-300 group-hover:blur-sm" />
              <div className="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                <span className="rounded-lg bg-card px-4 py-2 text-sm font-medium shadow-dropdown">
                  Blur Effect
                </span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
