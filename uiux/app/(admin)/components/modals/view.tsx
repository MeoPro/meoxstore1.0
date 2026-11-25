"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useState } from "react";

export default function ModalsPage() {
  const [activeModal, setActiveModal] = useState<string | null>(null);

  return (
    <div className="space-y-6">
      <PageHeader title="Modals" description="Dialog overlays for focused interactions." />

      <Card>
        <CardHeader>
          <CardTitle>Modal Triggers</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-3">
          <Button onClick={() => setActiveModal("basic")}>Basic Modal</Button>
          <Button variant="destructive" onClick={() => setActiveModal("confirm")}>
            Confirmation
          </Button>
          <Button variant="outline" onClick={() => setActiveModal("form")}>
            Form Modal
          </Button>
        </CardContent>
      </Card>

      {activeModal && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
          onClick={() => setActiveModal(null)}
        >
          <div
            className="mx-4 w-full max-w-md rounded-xl border border-border bg-card p-6 shadow-modal"
            onClick={(e) => e.stopPropagation()}
          >
            {activeModal === "basic" && (
              <>
                <h3 className="text-lg font-semibold">Basic Modal</h3>
                <p className="mt-2 text-sm text-muted-foreground">
                  This is a simple modal dialog with basic content and a close button.
                </p>
                <div className="mt-4 flex justify-end">
                  <Button size="sm" onClick={() => setActiveModal(null)}>
                    Close
                  </Button>
                </div>
              </>
            )}
            {activeModal === "confirm" && (
              <>
                <h3 className="text-lg font-semibold">Delete Item?</h3>
                <p className="mt-2 text-sm text-muted-foreground">
                  Are you sure you want to delete this item? This action cannot be undone.
                </p>
                <div className="mt-4 flex justify-end gap-2">
                  <Button size="sm" variant="outline" onClick={() => setActiveModal(null)}>
                    Cancel
                  </Button>
                  <Button size="sm" variant="destructive" onClick={() => setActiveModal(null)}>
                    Delete
                  </Button>
                </div>
              </>
            )}
            {activeModal === "form" && (
              <>
                <h3 className="text-lg font-semibold">Create New Item</h3>
                <div className="mt-4 space-y-3">
                  <input
                    type="text"
                    placeholder="Name"
                    className="w-full rounded-lg border border-border bg-transparent px-3 py-2 text-sm"
                  />
                  <input
                    type="email"
                    placeholder="Email"
                    className="w-full rounded-lg border border-border bg-transparent px-3 py-2 text-sm"
                  />
                </div>
                <div className="mt-4 flex justify-end gap-2">
                  <Button size="sm" variant="outline" onClick={() => setActiveModal(null)}>
                    Cancel
                  </Button>
                  <Button size="sm" onClick={() => setActiveModal(null)}>
                    Submit
                  </Button>
                </div>
              </>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
