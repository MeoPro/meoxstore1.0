import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "FAQ",
};

const faqs = [
  {
    question: "How do I reset my password?",
    answer:
      "Go to Settings > Security and click 'Update Password'. You'll need to enter your current password and then your new password twice to confirm.",
  },
  {
    question: "Can I export my data?",
    answer:
      "Yes, you can export data from any module. Look for the export button (usually in the top-right area of tables) to download your data in CSV or Excel format.",
  },
  {
    question: "How do I invite team members?",
    answer:
      "Navigate to Settings > Team and click 'Invite Member'. Enter their email address and assign a role. They'll receive an invitation email to join your workspace.",
  },
  {
    question: "What payment methods are supported?",
    answer:
      "We support all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers for enterprise plans.",
  },
  {
    question: "Is my data secure?",
    answer:
      "Absolutely. We use industry-standard encryption (AES-256) for data at rest and TLS 1.3 for data in transit. All data is stored in SOC 2 compliant data centers.",
  },
  {
    question: "Can I customize the dashboard?",
    answer:
      "Yes, you can rearrange widgets, choose which metrics to display, and save custom views. Premium plans offer additional customization options.",
  },
];

export default function FAQPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="FAQ" description="Frequently asked questions." />

      <div className="space-y-4">
        {faqs.map((faq, index) => (
          <Card key={index}>
            <CardContent className="pt-6">
              <h3 className="font-semibold">{faq.question}</h3>
              <p className="mt-2 text-sm text-muted-foreground">{faq.answer}</p>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
