import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Form Validation",
};

export default function FormValidationPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Form Validation"
        description="Form inputs with validation states and feedback messages."
      />

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Validation States</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <Input
              label="Email (Valid)"
              type="email"
              defaultValue="user@example.com"
              className="border-green-500 focus-visible:ring-green-500"
              hint="Looks good!"
            />
            <Input
              label="Username (Error)"
              defaultValue="ab"
              error="Username must be at least 3 characters."
            />
            <Input
              label="Password (Warning)"
              type="password"
              defaultValue="weak"
              className="border-yellow-500 focus-visible:ring-yellow-500"
              hint="Password strength: weak"
            />
            <Input
              label="Phone (Required)"
              placeholder="Enter phone number"
              error="This field is required."
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Form Example</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <Input
              label="First Name"
              defaultValue="John"
              className="border-green-500 focus-visible:ring-green-500"
            />
            <Input label="Last Name" placeholder="Enter last name" error="Last name is required." />
            <Input
              label="Email"
              type="email"
              defaultValue="invalid-email"
              error="Please enter a valid email address."
            />
            <Input
              label="Website"
              type="url"
              defaultValue="https://example.com"
              className="border-green-500 focus-visible:ring-green-500"
              hint="Valid URL format."
            />
            <button className="w-full rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">
              Submit Form
            </button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
