import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Timeline",
};

const events = [
  {
    time: "10:00 AM",
    title: "Project Kickoff",
    description: "Initial planning meeting with stakeholders.",
    color: "bg-primary",
  },
  {
    time: "11:30 AM",
    title: "Design Review",
    description: "Reviewed wireframes and UI mockups.",
    color: "bg-success",
  },
  {
    time: "2:00 PM",
    title: "Development Sprint",
    description: "Started implementing core features.",
    color: "bg-warning",
  },
  {
    time: "4:00 PM",
    title: "Code Review",
    description: "Pull request reviewed and merged.",
    color: "bg-info",
  },
  {
    time: "5:30 PM",
    title: "Deployment",
    description: "Deployed to staging environment.",
    color: "bg-destructive",
  },
];

export default function TimelinePage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Timeline"
        description="Vertical timeline for displaying chronological events."
      />

      <Card>
        <CardHeader>
          <CardTitle>Event Timeline</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative ml-4 space-y-6 border-l-2 border-border pl-6">
            {events.map((event, index) => (
              <div key={index} className="relative">
                <div
                  className={`absolute -left-[31px] top-1 h-3.5 w-3.5 rounded-full ${event.color} ring-4 ring-card`}
                />
                <div className="text-xs text-muted-foreground">{event.time}</div>
                <h4 className="mt-1 font-medium">{event.title}</h4>
                <p className="mt-0.5 text-sm text-muted-foreground">{event.description}</p>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
