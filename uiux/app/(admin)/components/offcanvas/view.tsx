"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useState } from "react";

export default function OffcanvasPage() {
  const [open, setOpen] = useState(false);

  return (
    <div className="space-y-6">
      <PageHeader title="Offcanvas" description="Slide-in panels for secondary content." />

      <Card>
        <CardHeader>
          <CardTitle>Offcanvas Panel</CardTitle>
        </CardHeader>
        <CardContent>
          <Button onClick={() => setOpen(true)}>Open Side Panel</Button>
        </CardContent>
      </Card>

      {open && (
        <div className="fixed inset-0 z-50 flex" onClick={() => setOpen(false)}>
          <div className="flex-1 bg-black/50" />
          <div
            className="w-80 bg-card border-l border-border p-6 shadow-modal overflow-y-auto"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold">Side Panel</h3>
              <button
                onClick={() => setOpen(false)}
                className="text-muted-foreground hover:text-foreground text-lg"
              >
                ×
              </button>
            </div>
            <p className="text-sm text-muted-foreground mb-4">
              This offcanvas panel slides in from the right side of the screen. It&apos;s useful for
              filters, settings, or detail views.
            </p>
            <div className="space-y-3">
              <div className="rounded-lg border border-border p-3">
                <p className="text-sm font-medium">Menu Item 1</p>
                <p className="text-xs text-muted-foreground">Description text</p>
              </div>
              <div className="rounded-lg border border-border p-3">
                <p className="text-sm font-medium">Menu Item 2</p>
                <p className="text-xs text-muted-foreground">Description text</p>
              </div>
              <div className="rounded-lg border border-border p-3">
                <p className="text-sm font-medium">Menu Item 3</p>
                <p className="text-xs text-muted-foreground">Description text</p>
              </div>
            </div>
            <div className="mt-6">
              <Button className="w-full" onClick={() => setOpen(false)}>
                Close Panel
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
