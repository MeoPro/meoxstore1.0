import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";
import { LeafletMap } from "./leaflet-map";

export const metadata: Metadata = {
  title: "Maps",
};

export default function MapsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Maps"
        description="Interactive maps built with Leaflet and OpenStreetMap."
      />

      <Card>
        <CardHeader>
          <CardTitle>Leaflet Map</CardTitle>
        </CardHeader>
        <CardContent>
          <LeafletMap />
        </CardContent>
      </Card>

      <Card>
        <CardContent className="py-4">
          <p className="text-sm text-muted-foreground">
            Leaflet is a lightweight open-source mapping library. Tiles are served free by
            OpenStreetMap — no API key required. Customize markers, layers and interactions directly
            in{" "}
            <code className="rounded-md bg-muted px-1 py-0.5 text-xs">
              modules/maps/leaflet-map.tsx
            </code>
            .
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
