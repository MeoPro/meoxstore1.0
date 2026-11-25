"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useState } from "react";

export default function CollapsePage() {
  const [openSections, setOpenSections] = useState<Record<string, boolean>>({ section1: true });

  const toggle = (key: string) => setOpenSections((prev) => ({ ...prev, [key]: !prev[key] }));

  return (
    <div className="space-y-6">
      <PageHeader
        title="Collapse"
        description="Collapsible content panels that toggle visibility."
      />

      <Card>
        <CardHeader>
          <CardTitle>Toggle Sections</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          <div className="rounded-xl border border-border">
            <Button
              variant="ghost"
              className="w-full justify-between"
              onClick={() => toggle("section1")}
            >
              Section 1 — Introduction
              <span>{openSections.section1 ? "−" : "+"}</span>
            </Button>
            {openSections.section1 && (
              <div className="border-t border-border px-4 py-3 text-sm text-muted-foreground">
                This is the first collapsible section. Click the button above to hide this content.
                Collapse components are useful for FAQs, settings panels, and progressive
                disclosure.
              </div>
            )}
          </div>

          <div className="rounded-xl border border-border">
            <Button
              variant="ghost"
              className="w-full justify-between"
              onClick={() => toggle("section2")}
            >
              Section 2 — Details
              <span>{openSections.section2 ? "−" : "+"}</span>
            </Button>
            {openSections.section2 && (
              <div className="border-t border-border px-4 py-3 text-sm text-muted-foreground">
                Here are the detailed contents of section 2. Multiple sections can be open
                simultaneously, unlike a traditional accordion.
              </div>
            )}
          </div>

          <div className="rounded-xl border border-border">
            <Button
              variant="ghost"
              className="w-full justify-between"
              onClick={() => toggle("section3")}
            >
              Section 3 — More Info
              <span>{openSections.section3 ? "−" : "+"}</span>
            </Button>
            {openSections.section3 && (
              <div className="border-t border-border px-4 py-3 text-sm text-muted-foreground">
                Additional information goes here. This section demonstrates that each collapse
                operates independently.
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
