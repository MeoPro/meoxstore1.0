import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Floating Labels",
};

export default function FloatingLabelsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Floating Labels"
        description="Form inputs with animated floating label effect using peer classes."
      />

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Text Inputs</CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="relative">
              <input
                type="text"
                id="float-name"
                placeholder=" "
                className="peer flex h-12 w-full rounded-lg border border-input bg-transparent px-3 pt-4 pb-1 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
              <label
                htmlFor="float-name"
                className="absolute left-3 top-1 text-2xs font-medium text-muted-foreground transition-all peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-sm peer-focus:top-1 peer-focus:text-2xs peer-focus:text-primary"
              >
                Full Name
              </label>
            </div>

            <div className="relative">
              <input
                type="email"
                id="float-email"
                placeholder=" "
                className="peer flex h-12 w-full rounded-lg border border-input bg-transparent px-3 pt-4 pb-1 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
              <label
                htmlFor="float-email"
                className="absolute left-3 top-1 text-2xs font-medium text-muted-foreground transition-all peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-sm peer-focus:top-1 peer-focus:text-2xs peer-focus:text-primary"
              >
                Email Address
              </label>
            </div>

            <div className="relative">
              <input
                type="password"
                id="float-password"
                placeholder=" "
                className="peer flex h-12 w-full rounded-lg border border-input bg-transparent px-3 pt-4 pb-1 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
              <label
                htmlFor="float-password"
                className="absolute left-3 top-1 text-2xs font-medium text-muted-foreground transition-all peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-sm peer-focus:top-1 peer-focus:text-2xs peer-focus:text-primary"
              >
                Password
              </label>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Other Inputs</CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="relative">
              <input
                type="tel"
                id="float-phone"
                placeholder=" "
                className="peer flex h-12 w-full rounded-lg border border-input bg-transparent px-3 pt-4 pb-1 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
              <label
                htmlFor="float-phone"
                className="absolute left-3 top-1 text-2xs font-medium text-muted-foreground transition-all peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-sm peer-focus:top-1 peer-focus:text-2xs peer-focus:text-primary"
              >
                Phone Number
              </label>
            </div>

            <div className="relative">
              <textarea
                id="float-message"
                placeholder=" "
                rows={4}
                className="peer flex w-full rounded-lg border border-input bg-transparent px-3 pt-5 pb-2 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
              <label
                htmlFor="float-message"
                className="absolute left-3 top-1 text-2xs font-medium text-muted-foreground transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-1 peer-focus:text-2xs peer-focus:text-primary"
              >
                Message
              </label>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
