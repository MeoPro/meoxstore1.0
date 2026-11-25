import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Landing Page",
};

const features = [
  {
    title: "Fast Performance",
    description: "Static rendering and lean bundles keep every page quick, even on slow networks.",
  },
  {
    title: "Secure by Default",
    description: "Sensible defaults and typed data flows help you avoid common security mistakes.",
  },
  {
    title: "Easy Integration",
    description: "Connect your own API in minutes thanks to a clear, typed data layer.",
  },
];

export default function LandingPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Landing Page" description="Preview of a simple landing page layout." />

      {/* Hero Section */}
      <Card>
        <CardContent className="p-8 text-center">
          <h2 className="text-3xl font-bold">Build Something Amazing</h2>
          <p className="mt-2 text-muted-foreground max-w-md mx-auto">
            A modern platform to ship products faster with beautiful UI components and seamless
            developer experience.
          </p>
          <div className="mt-6 flex justify-center gap-3">
            <Button>Get Started</Button>
            <Button variant="outline">Learn More</Button>
          </div>
        </CardContent>
      </Card>

      {/* Features Grid */}
      <div className="grid gap-4 sm:grid-cols-3">
        {features.map((feature) => (
          <Card key={feature.title}>
            <CardHeader>
              <CardTitle className="text-base">{feature.title}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground">{feature.description}</p>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* CTA */}
      <Card>
        <CardContent className="p-5 text-center">
          <h3 className="text-xl font-semibold">Ready to get started?</h3>
          <p className="mt-1 text-sm text-muted-foreground">
            Join thousands of users already on the platform.
          </p>
          <Button className="mt-4" size="lg">
            Sign Up Free
          </Button>
        </CardContent>
      </Card>
    </div>
  );
}
