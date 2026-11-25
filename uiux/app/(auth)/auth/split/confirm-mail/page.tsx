import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Confirm Email - Split",
};

export default function SplitConfirmMailPage() {
  return (
    <div className="grid w-full self-stretch lg:grid-cols-2">
      <div className="hidden lg:flex flex-col items-center justify-center bg-primary p-8 text-primary-foreground">
        <h2 className="text-3xl font-bold">Almost There!</h2>
        <p className="mt-2 max-w-sm text-center text-primary-foreground/80">
          Just one more step to activate your account.
        </p>
      </div>
      <div className="flex items-center justify-center p-8">
        <div className="w-full max-w-sm space-y-6 text-center">
          <AuthBrand className="mx-auto" />
          <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
            <span className="text-2xl">✉️</span>
          </div>
          <div>
            <h1 className="text-2xl font-bold">Check Your Email</h1>
            <p className="mt-2 text-sm text-muted-foreground">
              We&apos;ve sent a confirmation link to your email. Click the link to verify your
              account.
            </p>
          </div>
          <div className="space-y-3">
            <Button className="w-full" variant="outline">
              Resend Email
            </Button>
            <Link href="/auth/split/login" className="block text-sm text-primary hover:underline">
              Back to Login
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
