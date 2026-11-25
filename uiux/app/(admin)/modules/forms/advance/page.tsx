import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Advanced Forms",
};

export default function AdvancedFormsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Advanced Forms"
        description="Advanced form elements including date pickers, sliders, and file uploads."
      />

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Date Picker</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1.5">
              <label className="text-sm font-medium leading-none text-foreground">Start Date</label>
              <input
                type="date"
                className="flex h-9 w-full rounded-lg border border-input bg-transparent px-3 py-1 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
            </div>
            <div className="space-y-1.5">
              <label className="text-sm font-medium leading-none text-foreground">
                Date & Time
              </label>
              <input
                type="datetime-local"
                className="flex h-9 w-full rounded-lg border border-input bg-transparent px-3 py-1 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Range Slider</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1.5">
              <label className="text-sm font-medium leading-none text-foreground">
                Price Range
              </label>
              <input
                type="range"
                min={0}
                max={1000}
                defaultValue={500}
                className="w-full accent-primary"
              />
              <div className="flex justify-between text-xs text-muted-foreground">
                <span>$0</span>
                <span>$1,000</span>
              </div>
            </div>
            <div className="space-y-1.5">
              <label className="text-sm font-medium leading-none text-foreground">Volume</label>
              <input
                type="range"
                min={0}
                max={100}
                defaultValue={75}
                className="w-full accent-primary"
              />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>File Upload</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1.5">
              <label className="text-sm font-medium leading-none text-foreground">
                Upload Document
              </label>
              <div className="flex items-center justify-center rounded-lg border-2 border-dashed border-input p-6 text-center">
                <div className="space-y-1">
                  <p className="text-sm text-muted-foreground">
                    Drag & drop files here, or click to browse
                  </p>
                  <p className="text-xs text-muted-foreground">PNG, JPG, PDF up to 10MB</p>
                </div>
              </div>
              <input
                type="file"
                className="text-sm text-muted-foreground file:mr-4 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground"
              />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Tags Input</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1.5">
              <label className="text-sm font-medium leading-none text-foreground">Tags</label>
              <div className="flex flex-wrap gap-2 rounded-lg border border-input p-2">
                {["React", "Next.js", "TypeScript"].map((tag) => (
                  <span
                    key={tag}
                    className="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-1 text-xs font-medium"
                  >
                    {tag}
                    <button className="text-muted-foreground hover:text-foreground">×</button>
                  </span>
                ))}
                <input
                  type="text"
                  placeholder="Add tag..."
                  className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                />
              </div>
              <p className="text-xs text-muted-foreground">Press Enter to add a tag</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
