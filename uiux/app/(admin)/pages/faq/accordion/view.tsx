"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent } from "@/components/ui/card";
import { useState } from "react";

const faqs = [
  {
    q: "What is this platform?",
    a: "A comprehensive admin dashboard template built with Next.js, Tailwind CSS, and modern UI components.",
  },
  {
    q: "How do I get started?",
    a: "Clone the repository, install dependencies with npm install, and run the development server with npm run dev.",
  },
  {
    q: "Is there a free trial?",
    a: "Yes, you can explore all features for 14 days without any commitment or credit card required.",
  },
  {
    q: "Can I cancel anytime?",
    a: "Absolutely. You can cancel your subscription at any time from your account settings page.",
  },
  {
    q: "Do you offer technical support?",
    a: "We provide email support for all plans, and priority support with live chat for Pro and Enterprise plans.",
  },
  {
    q: "What payment methods are accepted?",
    a: "We accept all major credit cards, PayPal, and bank transfers for annual plans.",
  },
];

export default function FaqAccordionPage() {
  const [openIndex, setOpenIndex] = useState<number | null>(null);

  return (
    <div className="space-y-6">
      <PageHeader title="FAQ" description="Frequently asked questions in accordion format." />

      <Card>
        <CardContent className="p-5 divide-y divide-border">
          {faqs.map((faq, i) => (
            <div key={i} className="py-3 first:pt-0 last:pb-0">
              <button
                className="flex w-full items-center justify-between text-left text-sm font-medium"
                onClick={() => setOpenIndex(openIndex === i ? null : i)}
              >
                {faq.q}
                <span className="ml-2 text-muted-foreground">{openIndex === i ? "−" : "+"}</span>
              </button>
              {openIndex === i && <p className="mt-2 text-sm text-muted-foreground">{faq.a}</p>}
            </div>
          ))}
        </CardContent>
      </Card>
    </div>
  );
}
