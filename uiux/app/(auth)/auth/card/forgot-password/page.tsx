import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Forgot Password - Card",
};

export default function CardForgotPasswordPage() {
  return (
    <Card className="w-full max-w-md shadow-dropdown">
      <CardHeader className="text-center">
        <AuthBrand className="mx-auto mb-2" />
        <CardTitle className="text-2xl">Forgot Password</CardTitle>
        <p className="text-sm text-muted-foreground">
          Enter your email and we&apos;ll send you a reset link
        </p>
      </CardHeader>
      <CardContent className="space-y-4">
        <Input label="Email" type="email" placeholder="you@example.com" />
        <Button className="w-full">Send Reset Link</Button>
        <p className="text-center text-sm text-muted-foreground">
          <Link href="/auth/card/login" className="text-primary hover:underline">
            Back to Login
          </Link>
        </p>
      </CardContent>
    </Card>
  );
}
