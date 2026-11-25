import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Forgot Password",
};

export default function SimpleForgotPasswordPage() {
  return (
    <div className="w-full max-w-sm space-y-6">
      <AuthBrand className="mx-auto" />
      <div className="text-center">
        <h1 className="text-2xl font-bold">Forgot Password</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Enter your email and we&apos;ll send you a reset link
        </p>
      </div>

      <div className="space-y-4">
        <Input label="Email" type="email" placeholder="you@example.com" />
        <Button className="w-full">Send Reset Link</Button>
      </div>

      <p className="text-center text-sm text-muted-foreground">
        Remember your password?{" "}
        <Link href="/auth/simple/login" className="text-primary hover:underline">
          Sign In
        </Link>
      </p>
    </div>
  );
}
