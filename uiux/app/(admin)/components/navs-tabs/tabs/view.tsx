"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useState } from "react";

const tabs = ["Overview", "Features", "Pricing"];
const tabContent: Record<string, string> = {
  Overview:
    "This is the overview tab content. Use tabs to organize related content into separate views that users can switch between.",
  Features:
    "Feature list goes here. Tabs help reduce visual clutter by showing only one section at a time while keeping navigation clear.",
  Pricing:
    "Pricing information would be displayed here. Each tab panel can contain any type of content.",
};

export default function TabsPage() {
  const [activeTab, setActiveTab] = useState("Overview");

  return (
    <div className="space-y-6">
      <PageHeader title="Tabs" description="Tabbed content panels for organizing information." />

      <Card>
        <CardHeader>
          <CardTitle>Basic Tabs</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="border-b border-border">
            <div className="flex gap-0">
              {tabs.map((tab) => (
                <button
                  key={tab}
                  onClick={() => setActiveTab(tab)}
                  className={`border-b-2 px-4 py-2 text-sm font-medium transition-colors ${
                    activeTab === tab
                      ? "border-primary text-primary"
                      : "border-transparent text-muted-foreground hover:text-foreground"
                  }`}
                >
                  {tab}
                </button>
              ))}
            </div>
          </div>
          <div className="mt-4 text-sm text-muted-foreground">{tabContent[activeTab]}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Pill Tabs</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex gap-1 rounded-xl bg-muted/50 p-1">
            {tabs.map((tab) => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`rounded-lg px-4 py-2 text-sm font-medium transition-colors ${
                  activeTab === tab
                    ? "bg-card shadow-card text-foreground"
                    : "text-muted-foreground"
                }`}
              >
                {tab}
              </button>
            ))}
          </div>
          <div className="mt-4 text-sm text-muted-foreground">{tabContent[activeTab]}</div>
        </CardContent>
      </Card>
    </div>
  );
}
