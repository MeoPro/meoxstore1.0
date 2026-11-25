import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Embedded Videos",
};

export default function EmbedVideoPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Embedded Videos" description="Responsive video embed wrappers." />

      <Card>
        <CardHeader>
          <CardTitle>16:9 Aspect Ratio</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative aspect-video w-full overflow-hidden rounded-xl border border-border bg-muted/30">
            <div className="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground">
              <span className="text-4xl">▶</span>
              <p className="mt-2 text-sm">Video Embed (16:9)</p>
              <p className="text-xs">Replace with an iframe src to embed a video</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>4:3 Aspect Ratio</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative aspect-[4/3] w-full max-w-lg overflow-hidden rounded-xl border border-border bg-muted/30">
            <div className="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground">
              <span className="text-4xl">▶</span>
              <p className="mt-2 text-sm">Video Embed (4:3)</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Square (1:1)</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative aspect-square w-full max-w-xs overflow-hidden rounded-xl border border-border bg-muted/30">
            <div className="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground">
              <span className="text-4xl">▶</span>
              <p className="mt-2 text-sm">Video Embed (1:1)</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
