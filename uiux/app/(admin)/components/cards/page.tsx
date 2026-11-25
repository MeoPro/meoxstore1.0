import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Cards",
};

export default function CardsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Cards" description="Card containers for grouping related content." />

      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle>Basic Card</CardTitle>
            <CardDescription>A simple card with header and content.</CardDescription>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground">
              This is the card body content. Cards are flexible containers for displaying grouped
              information.
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Card with Footer</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground">
              This card includes a footer with actions.
            </p>
          </CardContent>
          <CardFooter className="gap-2">
            <Button size="sm">Save</Button>
            <Button size="sm" variant="outline">
              Cancel
            </Button>
          </CardFooter>
        </Card>

        <Card>
          <div className="h-32 rounded-t-xl bg-gradient-to-br from-primary/20 to-primary/5" />
          <CardHeader>
            <CardTitle>Image Card</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground">
              Card with an image placeholder header area.
            </p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardContent className="flex flex-col gap-4 sm:flex-row sm:items-center">
          <div className="h-24 w-24 shrink-0 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5" />
          <div>
            <h3 className="font-semibold">Horizontal Card</h3>
            <p className="mt-1 text-sm text-muted-foreground">
              This card uses a horizontal layout, perfect for list-style items with thumbnails or
              icons alongside text content.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
