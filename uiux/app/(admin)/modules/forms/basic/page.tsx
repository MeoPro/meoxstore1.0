import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Basic Forms",
};

export default function BasicFormsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Basic Forms"
        description="Showcase of standard form elements and inputs."
      />

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Text Inputs</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <Input label="Full Name" placeholder="Enter your name" />
            <Input label="Email Address" type="email" placeholder="you@example.com" />
            <Input label="Password" type="password" placeholder="••••••••" />
            <Input label="Disabled Input" placeholder="Cannot edit" disabled />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Select & Textarea</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <Select label="Country" defaultValue="">
              <option value="">Select a country</option>
              <option value="us">United States</option>
              <option value="uk">United Kingdom</option>
              <option value="ca">Canada</option>
              <option value="au">Australia</option>
            </Select>
            <Textarea label="Message" rows={4} placeholder="Write your message..." />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Checkboxes & Radios</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <p className="text-sm font-medium text-foreground">Preferences</p>
              {["Email notifications", "SMS alerts", "Push notifications"].map((item) => (
                <Checkbox key={item} label={item} />
              ))}
            </div>
            <div className="space-y-2">
              <p className="text-sm font-medium text-foreground">Plan</p>
              {["Free", "Pro", "Enterprise"].map((plan) => (
                <label key={plan} className="flex items-center gap-2 text-sm">
                  <input type="radio" name="plan" className="h-4 w-4 border-input" />
                  {plan}
                </label>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
