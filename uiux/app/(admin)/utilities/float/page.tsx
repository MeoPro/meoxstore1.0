import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Float",
};

export default function FloatPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Float" description="Float utilities for wrapping text around elements." />

      <Card>
        <CardHeader>
          <CardTitle>Float Left</CardTitle>
        </CardHeader>
        <CardContent className="overflow-hidden">
          <div className="float-left mr-4 mb-2 h-20 w-20 rounded-lg bg-primary/20" />
          <p className="text-sm text-muted-foreground">
            This text wraps around the floated element on the left side. Float utilities position
            elements to the left or right of their container, allowing surrounding content to flow
            around them naturally. This is a classic layout pattern used for images and pull quotes.
          </p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Float Right</CardTitle>
        </CardHeader>
        <CardContent className="overflow-hidden">
          <div className="float-right ml-4 mb-2 h-20 w-20 rounded-lg bg-green-500/20" />
          <p className="text-sm text-muted-foreground">
            This text wraps around the floated element on the right side. The element is pushed to
            the right edge of the container, and text flows on the left. This pattern is commonly
            used for sidebar elements or decorative content alongside text blocks.
          </p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Float None</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="float-none mx-auto h-20 w-20 rounded-lg bg-yellow-500/20" />
          <p className="mt-4 text-center text-sm text-muted-foreground">
            Float none removes float behavior. The element stays in normal document flow.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
