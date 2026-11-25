import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Forgot Password - Split",
};

export default function SplitForgotPasswordPage() {
  return (
    <div className="grid w-full self-stretch lg:grid-cols-2">
      <div className="hidden lg:flex flex-col items-center justify-center bg-primary p-8 text-primary-foreground">
        <h2 className="text-3xl font-bold">Forgot Password?</h2>
        <p className="mt-2 max-w-sm text-center text-primary-foreground/80">
          No worries, we&apos;ll help you reset it in no time.
        </p>
      </div>
      <div className="flex items-center justify-center p-8">
        <div className="w-full max-w-sm space-y-6">
          <AuthBrand />
          <div>
            <h1 className="text-2xl font-bold">Forgot Password</h1>
            <p className="mt-1 text-sm text-muted-foreground">
              Enter your email and we&apos;ll send you a reset link
            </p>
          </div>
          <div className="space-y-4">
            <Input label="Email" type="email" placeholder="you@example.com" />
            <Button className="w-full">Send Reset Link</Button>
          </div>
          <p className="text-sm text-muted-foreground">
            <Link href="/auth/split/login" className="text-primary hover:underline">
              Back to Login
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
