"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import { useState } from "react";

const steps = ["Account", "Personal", "Confirm"];

export default function FormWizardPage() {
  const [currentStep, setCurrentStep] = useState(0);
  const [form, setForm] = useState({
    username: "",
    email: "",
    password: "",
    firstName: "",
    lastName: "",
    phone: "",
  });

  const update = (key: keyof typeof form) => (value: string) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  const isLastStep = currentStep === steps.length - 1;

  return (
    <div className="space-y-6">
      <PageHeader title="Form Wizard" description="Multi-step form with progress indicators." />

      <Card>
        <CardContent className="py-6">
          {/* Step Indicators */}
          <div className="mb-8 flex items-center justify-center gap-2">
            {steps.map((step, index) => (
              <div key={step} className="flex items-center gap-2">
                <div
                  className={cn(
                    "flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium",
                    index <= currentStep
                      ? "bg-primary text-primary-foreground"
                      : "bg-muted text-muted-foreground",
                  )}
                >
                  {index + 1}
                </div>
                <span className="text-sm font-medium">{step}</span>
                {index < steps.length - 1 && (
                  <div
                    className={cn("h-0.5 w-12", index < currentStep ? "bg-primary" : "bg-muted")}
                  />
                )}
              </div>
            ))}
          </div>

          {/* Step Content */}
          <div className="mx-auto max-w-md space-y-4">
            {currentStep === 0 && (
              <>
                <Input
                  label="Username"
                  placeholder="Choose a username"
                  value={form.username}
                  onChange={(e) => update("username")(e.target.value)}
                />
                <Input
                  label="Email"
                  type="email"
                  placeholder="you@example.com"
                  value={form.email}
                  onChange={(e) => update("email")(e.target.value)}
                />
                <Input
                  label="Password"
                  type="password"
                  placeholder="••••••••"
                  value={form.password}
                  onChange={(e) => update("password")(e.target.value)}
                />
              </>
            )}
            {currentStep === 1 && (
              <>
                <Input
                  label="First Name"
                  placeholder="John"
                  value={form.firstName}
                  onChange={(e) => update("firstName")(e.target.value)}
                />
                <Input
                  label="Last Name"
                  placeholder="Doe"
                  value={form.lastName}
                  onChange={(e) => update("lastName")(e.target.value)}
                />
                <Input
                  label="Phone"
                  type="tel"
                  placeholder="+1 (555) 000-0000"
                  value={form.phone}
                  onChange={(e) => update("phone")(e.target.value)}
                />
              </>
            )}
            {currentStep === 2 && (
              <div className="rounded-lg bg-muted/50 p-4 text-sm text-muted-foreground">
                <p className="mb-2 font-medium text-foreground">Review your details</p>
                <p>Username: {form.username || "—"}</p>
                <p>Email: {form.email || "—"}</p>
                <p>Name: {`${form.firstName} ${form.lastName}`.trim() || "—"}</p>
                <p>Phone: {form.phone || "—"}</p>
              </div>
            )}
          </div>

          {/* Navigation */}
          <div className="mt-8 flex justify-between">
            <Button
              variant="outline"
              onClick={() => setCurrentStep(Math.max(0, currentStep - 1))}
              disabled={currentStep === 0}
            >
              Previous
            </Button>
            <Button
              onClick={() => setCurrentStep(Math.min(steps.length - 1, currentStep + 1))}
              disabled={isLastStep}
            >
              {isLastStep ? "Submit" : "Next"}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
