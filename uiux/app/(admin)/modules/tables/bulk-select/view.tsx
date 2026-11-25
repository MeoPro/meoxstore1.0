"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useState } from "react";

const employees = [
  { id: 1, name: "Anna Smith", department: "Engineering", position: "Frontend Developer" },
  { id: 2, name: "Brian Jones", department: "Design", position: "UI Designer" },
  { id: 3, name: "Clara Park", department: "Engineering", position: "Backend Developer" },
  { id: 4, name: "Daniel Kim", department: "Marketing", position: "Content Lead" },
  { id: 5, name: "Elena Ross", department: "Engineering", position: "DevOps Engineer" },
  { id: 6, name: "Frank Wu", department: "Product", position: "Product Manager" },
];

export default function BulkSelectTablePage() {
  const [selected, setSelected] = useState<number[]>([]);

  const toggleAll = () => {
    setSelected(selected.length === employees.length ? [] : employees.map((e) => e.id));
  };

  const toggleOne = (id: number) => {
    setSelected((prev) => (prev.includes(id) ? prev.filter((i) => i !== id) : [...prev, id]));
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Bulk Select Table"
        description="Table with row selection and bulk action controls."
      />

      {selected.length > 0 && (
        <div className="flex items-center gap-3 rounded-lg border border-primary/20 bg-primary/5 px-4 py-2">
          <span className="text-sm font-medium">{selected.length} selected</span>
          <button className="rounded-lg bg-primary px-3 py-1 text-xs font-medium text-primary-foreground">
            Export
          </button>
          <button className="rounded-lg bg-destructive px-3 py-1 text-xs font-medium text-destructive-foreground">
            Delete
          </button>
          <button
            onClick={() => setSelected([])}
            className="ml-auto text-xs text-muted-foreground hover:text-foreground"
          >
            Clear selection
          </button>
        </div>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Employees</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="data-table w-full text-sm">
              <thead>
                <tr className="border-b border-border bg-muted/50">
                  <th className="px-4 py-3 text-left">
                    <input
                      type="checkbox"
                      checked={selected.length === employees.length}
                      onChange={toggleAll}
                      className="h-4 w-4 rounded-md border-input"
                    />
                  </th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Name</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                    Department
                  </th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                    Position
                  </th>
                </tr>
              </thead>
              <tbody>
                {employees.map((emp) => (
                  <tr
                    key={emp.id}
                    className={`border-b border-border last:border-0 hover:bg-muted/50 ${
                      selected.includes(emp.id) ? "bg-primary/5" : ""
                    }`}
                  >
                    <td className="px-4 py-3">
                      <input
                        type="checkbox"
                        checked={selected.includes(emp.id)}
                        onChange={() => toggleOne(emp.id)}
                        className="h-4 w-4 rounded-md border-input"
                      />
                    </td>
                    <td className="px-4 py-3 font-medium">{emp.name}</td>
                    <td className="px-4 py-3">{emp.department}</td>
                    <td className="px-4 py-3 text-muted-foreground">{emp.position}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
