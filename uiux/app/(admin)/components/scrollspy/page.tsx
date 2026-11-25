import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Scrollspy",
};

export default function ScrollspyPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Scrollspy"
        description="Automatically highlight navigation based on scroll position."
      />

      <div className="grid gap-6 lg:grid-cols-4">
        <Card className="lg:col-span-1">
          <CardContent>
            <nav className="space-y-1">
              <div className="rounded-lg bg-primary/10 px-3 py-2 text-sm font-medium text-primary">
                Introduction
              </div>
              <div className="rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-muted/50 cursor-pointer">
                Getting Started
              </div>
              <div className="rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-muted/50 cursor-pointer">
                Configuration
              </div>
              <div className="rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-muted/50 cursor-pointer">
                Advanced
              </div>
              <div className="rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-muted/50 cursor-pointer">
                API Reference
              </div>
            </nav>
          </CardContent>
        </Card>

        <Card className="lg:col-span-3">
          <CardContent className="space-y-8">
            <section>
              <h3 className="text-lg font-semibold">Introduction</h3>
              <p className="mt-2 text-sm text-muted-foreground">
                Scrollspy tracks the scroll position and highlights the corresponding navigation
                link. As you scroll through sections, the active nav item updates automatically.
              </p>
            </section>
            <section>
              <h3 className="text-lg font-semibold">Getting Started</h3>
              <p className="mt-2 text-sm text-muted-foreground">
                Set up scrollspy by assigning IDs to your content sections and configuring the
                navigation to observe scroll events. The component uses IntersectionObserver for
                performance.
              </p>
            </section>
            <section>
              <h3 className="text-lg font-semibold">Configuration</h3>
              <p className="mt-2 text-sm text-muted-foreground">
                Configure offset, threshold, and smooth scrolling behavior. Customize the active
                class and transition effects to match your design system.
              </p>
            </section>
            <section>
              <h3 className="text-lg font-semibold">Advanced</h3>
              <p className="mt-2 text-sm text-muted-foreground">
                Advanced patterns include nested scrollspy, dynamic sections, and integration with
                routing for single-page applications.
              </p>
            </section>
            <section>
              <h3 className="text-lg font-semibold">API Reference</h3>
              <p className="mt-2 text-sm text-muted-foreground">
                Full API documentation including props, events, methods, and TypeScript interfaces
                for the scrollspy component.
              </p>
            </section>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
