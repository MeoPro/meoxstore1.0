import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Alerts",
};

const alerts = [
  {
    variant: "info",
    label: "Info",
    bg: "bg-info/10",
    text: "text-info",
    border: "border-info/20",
    message: "This is an informational alert — check it out!",
  },
  {
    variant: "success",
    label: "Success",
    bg: "bg-success/10",
    text: "text-success",
    border: "border-success/20",
    message: "Well done! You successfully completed the action.",
  },
  {
    variant: "warning",
    label: "Warning",
    bg: "bg-warning/10",
    text: "text-warning",
    border: "border-warning/20",
    message: "Warning! Something needs your attention.",
  },
  {
    variant: "error",
    label: "Error",
    bg: "bg-destructive/10",
    text: "text-destructive",
    border: "border-destructive/20",
    message: "Oh snap! Something went wrong. Please try again.",
  },
];

export default function AlertsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Alerts" description="Contextual feedback messages for user actions." />

      <Card>
        <CardHeader>
          <CardTitle>Alert Variants</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {alerts.map((alert) => (
            <div
              key={alert.variant}
              className={`rounded-xl border px-4 py-3 ${alert.bg} ${alert.text} ${alert.border}`}
            >
              <p className="font-medium">{alert.label}</p>
              <p className="mt-1 text-sm opacity-90">{alert.message}</p>
            </div>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Dismissible Alert</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-between rounded-xl border border-info/20 bg-info/10 px-4 py-3 text-info">
            <p className="text-sm">This alert can be dismissed by clicking the X button.</p>
            <button className="ml-4 text-lg font-bold opacity-70 hover:opacity-100">×</button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
