import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Buttons",
};

export default function ButtonsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Buttons" description="Button styles, variants, and sizes." />

      <Card>
        <CardHeader>
          <CardTitle>Variants</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-3">
          <Button variant="default">Default</Button>
          <Button variant="secondary">Secondary</Button>
          <Button variant="destructive">Destructive</Button>
          <Button variant="outline">Outline</Button>
          <Button variant="ghost">Ghost</Button>
          <Button variant="link">Link</Button>
          <Button variant="success">Success</Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Sizes</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-wrap items-center gap-3">
          <Button size="sm">Small</Button>
          <Button size="default">Default</Button>
          <Button size="lg">Large</Button>
          <Button size="xl">Extra Large</Button>
          <Button size="icon">⚡</Button>
          <Button size="icon-sm">✓</Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>States</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-wrap items-center gap-3">
          <Button>Normal</Button>
          <Button disabled>Disabled</Button>
          <Button isLoading>Loading</Button>
        </CardContent>
      </Card>
    </div>
  );
}
