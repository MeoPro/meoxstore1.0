import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Colors",
};

const colors = [
  { name: "primary", class: "bg-primary", variable: "--primary" },
  { name: "secondary", class: "bg-secondary", variable: "--secondary" },
  { name: "destructive", class: "bg-destructive", variable: "--destructive" },
  { name: "muted", class: "bg-muted", variable: "--muted" },
  { name: "accent", class: "bg-accent", variable: "--accent" },
  { name: "card", class: "bg-card", variable: "--card" },
  { name: "popover", class: "bg-popover", variable: "--popover" },
  { name: "border", class: "bg-border", variable: "--border" },
];

const extendedColors = [
  { name: "blue-500", class: "bg-blue-500" },
  { name: "green-500", class: "bg-green-500" },
  { name: "yellow-500", class: "bg-yellow-500" },
  { name: "red-500", class: "bg-red-500" },
  { name: "purple-500", class: "bg-purple-500" },
  { name: "pink-500", class: "bg-pink-500" },
  { name: "orange-500", class: "bg-orange-500" },
  { name: "cyan-500", class: "bg-cyan-500" },
];

export default function ColorsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Colors"
        description="Full color palette with theme tokens and extended colors."
      />

      <Card>
        <CardHeader>
          <CardTitle>Theme Colors</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
            {colors.map((color) => (
              <div key={color.name} className="space-y-2 text-center">
                <div className={`h-16 rounded-lg border border-border ${color.class}`} />
                <p className="text-sm font-medium">{color.name}</p>
                <p className="text-xs text-muted-foreground">{color.variable}</p>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Extended Palette</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-8">
            {extendedColors.map((color) => (
              <div key={color.name} className="space-y-2 text-center">
                <div className={`h-16 rounded-lg ${color.class}`} />
                <p className="text-xs text-muted-foreground">{color.name}</p>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
