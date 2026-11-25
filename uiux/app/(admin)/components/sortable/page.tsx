import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Sortable",
};

const items = [
  { id: 1, title: "Design Homepage", priority: "High" },
  { id: 2, title: "Fix Navigation Bug", priority: "Medium" },
  { id: 3, title: "Update Documentation", priority: "Low" },
  { id: 4, title: "Review Pull Requests", priority: "High" },
  { id: 5, title: "Deploy to Staging", priority: "Medium" },
];

export default function SortablePage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Sortable" description="Drag-and-drop sortable list interface." />

      <Card>
        <CardHeader>
          <CardTitle>Sortable List</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-2">
            {items.map((item) => (
              <div
                key={item.id}
                className="flex items-center gap-3 rounded-xl border border-border px-4 py-3 cursor-grab hover:bg-muted/50 transition-colors"
              >
                <span className="text-muted-foreground">⠿</span>
                <span className="flex-1 text-sm font-medium">{item.title}</span>
                <span
                  className={`text-xs px-2 py-0.5 rounded-full ${
                    item.priority === "High"
                      ? "bg-destructive/10 text-destructive"
                      : item.priority === "Medium"
                        ? "bg-warning/10 text-warning"
                        : "bg-muted text-muted-foreground"
                  }`}
                >
                  {item.priority}
                </span>
              </div>
            ))}
          </div>
          <p className="mt-4 text-xs text-muted-foreground">
            Drag items using the handle to reorder. Integrate with a drag-and-drop library for full
            functionality.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
