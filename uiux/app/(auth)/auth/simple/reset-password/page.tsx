import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Reset Password",
};

export default function SimpleResetPasswordPage() {
  return (
    <div className="w-full max-w-sm space-y-6">
      <AuthBrand className="mx-auto" />
      <div className="text-center">
        <h1 className="text-2xl font-bold">Reset Password</h1>
        <p className="mt-1 text-sm text-muted-foreground">Enter your new password below</p>
      </div>

      <div className="space-y-4">
        <Input label="New Password" type="password" placeholder="••••••••" />
        <Input label="Confirm Password" type="password" placeholder="••••••••" />
        <Button className="w-full">Reset Password</Button>
      </div>

      <p className="text-center text-sm text-muted-foreground">
        <Link href="/auth/simple/login" className="text-primary hover:underline">
          Back to Login
        </Link>
      </p>
    </div>
  );
}
