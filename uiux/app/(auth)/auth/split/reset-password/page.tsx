import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Reset Password - Split",
};

export default function SplitResetPasswordPage() {
  return (
    <div className="grid w-full self-stretch lg:grid-cols-2">
      <div className="hidden lg:flex flex-col items-center justify-center bg-primary p-8 text-primary-foreground">
        <h2 className="text-3xl font-bold">New Password</h2>
        <p className="mt-2 max-w-sm text-center text-primary-foreground/80">
          Choose a strong password to secure your account.
        </p>
      </div>
      <div className="flex items-center justify-center p-8">
        <div className="w-full max-w-sm space-y-6">
          <AuthBrand />
          <div>
            <h1 className="text-2xl font-bold">Reset Password</h1>
            <p className="mt-1 text-sm text-muted-foreground">Enter your new password below</p>
          </div>
          <div className="space-y-4">
            <Input label="New Password" type="password" placeholder="••••••••" />
            <Input label="Confirm Password" type="password" placeholder="••••••••" />
            <Button className="w-full">Reset Password</Button>
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
