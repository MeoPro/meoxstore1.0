import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Typography",
};

export default function TypographyPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Typography"
        description="Typography scale, font weights, and text transforms."
      />

      <Card>
        <CardHeader>
          <CardTitle>Headings</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          <h1 className="text-4xl font-bold tracking-tight">Heading 1</h1>
          <h2 className="text-3xl font-semibold tracking-tight">Heading 2</h2>
          <h3 className="text-2xl font-semibold">Heading 3</h3>
          <h4 className="text-xl font-semibold">Heading 4</h4>
          <h5 className="text-lg font-medium">Heading 5</h5>
          <h6 className="text-base font-medium">Heading 6</h6>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Font Weights</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          <p className="font-thin">font-thin (100)</p>
          <p className="font-light">font-light (300)</p>
          <p className="font-normal">font-normal (400)</p>
          <p className="font-medium">font-medium (500)</p>
          <p className="font-semibold">font-semibold (600)</p>
          <p className="font-bold">font-bold (700)</p>
          <p className="font-black">font-black (900)</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Text Transform & Style</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          <p className="uppercase text-sm">uppercase text</p>
          <p className="lowercase text-sm">LOWERCASE TEXT</p>
          <p className="capitalize text-sm">capitalize each word</p>
          <p className="italic text-sm">italic text style</p>
          <p className="line-through text-sm text-muted-foreground">strikethrough text</p>
          <p className="underline text-sm">underlined text</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Body & Small Text</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          <p className="text-base">
            Body text (base) — The quick brown fox jumps over the lazy dog.
          </p>
          <p className="text-sm text-muted-foreground">
            Small text — Secondary information and captions.
          </p>
          <p className="text-xs text-muted-foreground">Extra small — Fine print and labels.</p>
        </CardContent>
      </Card>
    </div>
  );
}
