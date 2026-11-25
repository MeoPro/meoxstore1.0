import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Pricing - Alternative",
};

const plans = [
  {
    name: "Starter",
    monthly: 9,
    yearly: 89,
    features: ["5 Projects", "10GB Storage", "Email Support"],
  },
  {
    name: "Pro",
    monthly: 29,
    yearly: 289,
    features: ["Unlimited Projects", "100GB Storage", "Priority Support", "Analytics"],
  },
  {
    name: "Enterprise",
    monthly: 99,
    yearly: 989,
    features: ["Everything in Pro", "Custom Integrations", "Dedicated Account Manager", "SLA"],
  },
];

export default function PricingAltPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Pricing"
        description="Alternative pricing layout with monthly/yearly toggle."
      />

      <div className="text-center">
        <p className="text-sm text-muted-foreground">Toggle: Monthly | Yearly (save ~20%)</p>
      </div>

      <div className="grid gap-6 sm:grid-cols-3">
        {plans.map((plan) => (
          <Card key={plan.name} className="flex flex-col">
            <CardHeader className="text-center">
              <CardTitle>{plan.name}</CardTitle>
              <p className="text-3xl font-bold mt-2">
                ${plan.monthly}
                <span className="text-sm font-normal text-muted-foreground">/mo</span>
              </p>
              <p className="text-xs text-muted-foreground">or ${plan.yearly}/year</p>
            </CardHeader>
            <CardContent className="flex-1 space-y-3">
              <ul className="space-y-2 text-sm">
                {plan.features.map((f) => (
                  <li key={f} className="flex items-center gap-2">
                    <span className="text-primary">✓</span> {f}
                  </li>
                ))}
              </ul>
            </CardContent>
            <div className="p-6 pt-0">
              <Button className="w-full" variant={plan.name === "Pro" ? "default" : "outline"}>
                Choose {plan.name}
              </Button>
            </div>
          </Card>
        ))}
      </div>
    </div>
  );
}
