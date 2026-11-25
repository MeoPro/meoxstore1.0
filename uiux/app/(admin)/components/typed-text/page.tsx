import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Typed Text",
};

export default function TypedTextPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Typed Text" description="Typewriter text animation effects." />

      <Card>
        <CardHeader>
          <CardTitle>Typewriter Effect</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="rounded-xl border border-border bg-muted/30 p-8 text-center">
            <p className="text-2xl font-bold">
              We build{" "}
              <span className="inline-block border-r-2 border-primary text-primary animate-pulse">
                amazing products
              </span>
            </p>
            <p className="mt-4 text-sm text-muted-foreground">
              The cursor blinks to simulate a typewriter effect. In production, use a typed.js or
              similar library to cycle through words.
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Animated Phrases</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div className="rounded-lg border border-border px-4 py-3">
              <p className="text-sm text-muted-foreground">Cycling through:</p>
              <div className="mt-2 flex flex-wrap gap-2">
                {["Developer", "Designer", "Creator", "Innovator"].map((word) => (
                  <span
                    key={word}
                    className="rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary"
                  >
                    {word}
                  </span>
                ))}
              </div>
            </div>
            <p className="text-xs text-muted-foreground">
              These words would cycle with a typing animation. Integrate with typed.js,
              typewriter-effect, or a custom hook for the full effect.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
