import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Privacy Policy",
};

export default function PrivacyPolicyPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Privacy Policy" description="How we handle your data." />

      <Card>
        <CardContent className="prose prose-sm max-w-none p-5 text-sm text-muted-foreground space-y-4">
          <h3 className="text-base font-semibold text-foreground">1. Information We Collect</h3>
          <p>
            We collect information you provide directly, including name, email address, and usage
            data when you interact with our services.
          </p>

          <h3 className="text-base font-semibold text-foreground">
            2. How We Use Your Information
          </h3>
          <p>
            Your information is used to provide, maintain, and improve our services, communicate
            with you, and ensure security of our platform.
          </p>

          <h3 className="text-base font-semibold text-foreground">3. Data Sharing</h3>
          <p>
            We do not sell your personal information. We may share data with service providers who
            assist in operating our platform under strict confidentiality agreements.
          </p>

          <h3 className="text-base font-semibold text-foreground">4. Data Security</h3>
          <p>
            We implement industry-standard security measures to protect your data, including
            encryption in transit and at rest.
          </p>

          <h3 className="text-base font-semibold text-foreground">5. Your Rights</h3>
          <p>
            You have the right to access, correct, or delete your personal data. Contact us at
            privacy@example.com for any requests.
          </p>

          <h3 className="text-base font-semibold text-foreground">6. Contact Us</h3>
          <p>
            If you have any questions about this Privacy Policy, please contact us at
            support@example.com.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
