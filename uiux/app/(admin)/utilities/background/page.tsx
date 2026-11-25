import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Background",
};

export default function BackgroundPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Background"
        description="Background color utilities and gradient examples."
      />

      <Card>
        <CardHeader>
          <CardTitle>Background Colors</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            <div className="space-y-2 text-center">
              <div className="h-20 rounded-lg bg-primary" />
              <p className="text-xs text-muted-foreground">bg-primary</p>
            </div>
            <div className="space-y-2 text-center">
              <div className="h-20 rounded-lg bg-green-500" />
              <p className="text-xs text-muted-foreground">bg-success</p>
            </div>
            <div className="space-y-2 text-center">
              <div className="h-20 rounded-lg bg-yellow-500" />
              <p className="text-xs text-muted-foreground">bg-warning</p>
            </div>
            <div className="space-y-2 text-center">
              <div className="h-20 rounded-lg bg-destructive" />
              <p className="text-xs text-muted-foreground">bg-destructive</p>
            </div>
            <div className="space-y-2 text-center">
              <div className="h-20 rounded-lg bg-blue-500" />
              <p className="text-xs text-muted-foreground">bg-info</p>
            </div>
            <div className="space-y-2 text-center">
              <div className="h-20 rounded-lg bg-muted" />
              <p className="text-xs text-muted-foreground">bg-muted</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Gradients</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 md:grid-cols-3">
            <div className="h-20 rounded-lg bg-gradient-to-r from-primary to-blue-400" />
            <div className="h-20 rounded-lg bg-gradient-to-r from-green-500 to-emerald-300" />
            <div className="h-20 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500" />
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
