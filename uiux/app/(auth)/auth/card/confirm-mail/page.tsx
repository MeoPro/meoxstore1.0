import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Confirm Email - Card",
};

export default function CardConfirmMailPage() {
  return (
    <Card className="w-full max-w-md shadow-dropdown text-center">
      <CardHeader>
        <AuthBrand className="mx-auto mb-4" />
        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
          <span className="text-2xl">✉️</span>
        </div>
        <CardTitle className="text-2xl mt-4">Check Your Email</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <p className="text-sm text-muted-foreground">
          We&apos;ve sent a confirmation link to your email address. Please check your inbox and
          click the link to verify your account.
        </p>
        <Button className="w-full" variant="outline">
          Resend Email
        </Button>
        <Link href="/auth/card/login" className="block text-sm text-primary hover:underline">
          Back to Login
        </Link>
      </CardContent>
    </Card>
  );
}
