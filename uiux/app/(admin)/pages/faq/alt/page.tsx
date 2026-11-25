import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "FAQ - Categorized",
};

const categories = [
  {
    name: "General",
    questions: [
      {
        q: "What is this platform?",
        a: "A workspace that brings your sales, customers, and team activity together in one dashboard.",
      },
      {
        q: "How do I get started?",
        a: "Create an account, invite your team, and connect your first data source from the settings page.",
      },
      {
        q: "Is there a free trial?",
        a: "Yes. Every plan includes a 14-day free trial with full access and no credit card required.",
      },
    ],
  },
  {
    name: "Billing",
    questions: [
      {
        q: "What payment methods do you accept?",
        a: "We accept all major credit cards, PayPal, and bank transfers for annual plans.",
      },
      {
        q: "Can I cancel anytime?",
        a: "Yes. You can cancel from the billing page and keep access until the end of your billing period.",
      },
      {
        q: "How do refunds work?",
        a: "Request a refund within 30 days of payment and we will return the full amount to your original method.",
      },
    ],
  },
  {
    name: "Technical",
    questions: [
      {
        q: "What browsers are supported?",
        a: "The latest two versions of Chrome, Firefox, Safari, and Edge are fully supported.",
      },
      {
        q: "Do you offer an API?",
        a: "Yes. A REST API with token authentication is available on all paid plans.",
      },
      {
        q: "How do I integrate with my app?",
        a: "Use the API or webhooks to sync data, or install one of the ready-made integrations.",
      },
    ],
  },
];

export default function FaqAltPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="FAQ" description="Frequently asked questions organized by category." />

      <div className="grid gap-6 lg:grid-cols-4">
        {/* Sidebar */}
        <Card className="lg:col-span-1">
          <CardHeader>
            <CardTitle className="text-base">Categories</CardTitle>
          </CardHeader>
          <CardContent className="space-y-1">
            {categories.map((cat) => (
              <div
                key={cat.name}
                className="rounded-md px-3 py-2 text-sm hover:bg-accent cursor-pointer"
              >
                {cat.name}
              </div>
            ))}
          </CardContent>
        </Card>

        {/* Questions */}
        <div className="lg:col-span-3 space-y-4">
          {categories.map((cat) => (
            <Card key={cat.name}>
              <CardHeader>
                <CardTitle className="text-base">{cat.name}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                {cat.questions.map(({ q, a }) => (
                  <div key={q} className="border-b border-border pb-3 last:border-0 last:pb-0">
                    <p className="font-medium text-sm">{q}</p>
                    <p className="mt-1 text-sm text-muted-foreground">{a}</p>
                  </div>
                ))}
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </div>
  );
}
