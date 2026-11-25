import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Reset Password - Card",
};

export default function CardResetPasswordPage() {
  return (
    <Card className="w-full max-w-md shadow-dropdown">
      <CardHeader className="text-center">
        <AuthBrand className="mx-auto mb-2" />
        <CardTitle className="text-2xl">Reset Password</CardTitle>
        <p className="text-sm text-muted-foreground">Enter your new password below</p>
      </CardHeader>
      <CardContent className="space-y-4">
        <Input label="New Password" type="password" placeholder="••••••••" />
        <Input label="Confirm Password" type="password" placeholder="••••••••" />
        <Button className="w-full">Reset Password</Button>
        <p className="text-center text-sm text-muted-foreground">
          <Link href="/auth/card/login" className="text-primary hover:underline">
            Back to Login
          </Link>
        </p>
      </CardContent>
    </Card>
  );
}
