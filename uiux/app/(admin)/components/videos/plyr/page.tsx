import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Video Player",
};

const SAMPLE_VIDEO = "https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4";

const SAMPLE_AUDIO = "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3";

export default function VideoPlayerPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Video Player"
        description="Native HTML5 media player with full controls."
      />

      <Card>
        <CardHeader>
          <CardTitle>Video Player</CardTitle>
        </CardHeader>
        <CardContent>
          <video
            className="aspect-video w-full rounded-xl border border-border bg-black"
            controls
            preload="metadata"
            poster="https://storage.googleapis.com/gtv-videos-bucket/sample/images/BigBuckBunny.jpg"
            src={SAMPLE_VIDEO}
          />
          <p className="mt-2 text-xs text-muted-foreground">
            Sample video: &quot;Big Buck Bunny&quot; &copy; Blender Foundation, licensed under CC BY
            3.0.
          </p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Audio Player</CardTitle>
        </CardHeader>
        <CardContent>
          <audio className="w-full" controls preload="none" src={SAMPLE_AUDIO} />
          <p className="mt-2 text-xs text-muted-foreground">
            Sample audio: &quot;SoundHelix Song 1&quot; by T. Schürger, SoundHelix.com.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
