import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Lightbox",
};

export default function LightboxPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Lightbox" description="Image gallery with fullscreen preview." />

      <Card>
        <CardHeader>
          <CardTitle>Gallery Grid</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            {Array.from({ length: 8 }).map((_, i) => (
              <div
                key={i}
                className="group relative aspect-square cursor-pointer overflow-hidden rounded-xl border border-border"
              >
                <div
                  className={`h-full w-full bg-gradient-to-br ${
                    [
                      "from-primary/20 to-primary/5",
                      "from-success/20 to-success/5",
                      "from-warning/20 to-warning/5",
                      "from-info/20 to-info/5",
                    ][i % 4]
                  }`}
                />
                <div className="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors group-hover:bg-black/40">
                  <span className="text-xl text-white opacity-0 transition-opacity group-hover:opacity-100">
                    🔍
                  </span>
                </div>
              </div>
            ))}
          </div>
          <p className="mt-4 text-xs text-muted-foreground">
            Click any image to open in fullscreen lightbox view. Use arrow keys to navigate between
            images.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
