import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Check } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Pricing",
};

const plans = [
  {
    name: "Starter",
    price: "$29",
    period: "/month",
    description: "Perfect for small teams getting started.",
    features: ["5 team members", "10GB storage", "Basic analytics", "Email support", "API access"],
    popular: false,
  },
  {
    name: "Professional",
    price: "$79",
    period: "/month",
    description: "For growing businesses that need more.",
    features: [
      "25 team members",
      "100GB storage",
      "Advanced analytics",
      "Priority support",
      "API access",
      "Custom integrations",
      "SSO",
    ],
    popular: true,
  },
  {
    name: "Enterprise",
    price: "$199",
    period: "/month",
    description: "For large organizations with custom needs.",
    features: [
      "Unlimited members",
      "1TB storage",
      "Custom analytics",
      "24/7 support",
      "API access",
      "Custom integrations",
      "SSO",
      "Dedicated manager",
      "SLA guarantee",
    ],
    popular: false,
  },
];

export default function PricingPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Pricing" description="Choose the plan that's right for you." />

      <div className="grid gap-6 md:grid-cols-3">
        {plans.map((plan) => (
          <Card
            key={plan.name}
            className={`relative ${plan.popular ? "border-primary shadow-dropdown" : ""}`}
          >
            {plan.popular && (
              <Badge className="absolute -top-2.5 left-1/2 -translate-x-1/2">Most Popular</Badge>
            )}
            <CardContent className="pt-6">
              <h3 className="text-lg font-semibold">{plan.name}</h3>
              <p className="mt-1 text-sm text-muted-foreground">{plan.description}</p>
              <div className="mt-4 flex items-baseline">
                <span className="text-4xl font-bold">{plan.price}</span>
                <span className="ml-1 text-muted-foreground">{plan.period}</span>
              </div>
              <Button className="mt-6 w-full" variant={plan.popular ? "default" : "outline"}>
                Get Started
              </Button>
              <ul className="mt-6 space-y-3">
                {plan.features.map((feature) => (
                  <li key={feature} className="flex items-center gap-2 text-sm">
                    <Check className="h-4 w-4 text-success" />
                    {feature}
                  </li>
                ))}
              </ul>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
