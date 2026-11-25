import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Register - Split",
};

export default function SplitRegisterPage() {
  return (
    <div className="grid w-full self-stretch lg:grid-cols-2">
      <div className="hidden lg:flex flex-col items-center justify-center bg-primary p-8 text-primary-foreground">
        <h2 className="text-3xl font-bold">Get Started</h2>
        <p className="mt-2 max-w-sm text-center text-primary-foreground/80">
          Create your account and start building amazing projects today.
        </p>
      </div>
      <div className="flex items-center justify-center p-8">
        <div className="w-full max-w-sm space-y-6">
          <AuthBrand />
          <div>
            <h1 className="text-2xl font-bold">Create Account</h1>
            <p className="mt-1 text-sm text-muted-foreground">Fill in your details to sign up</p>
          </div>
          <div className="space-y-4">
            <Input label="Full Name" placeholder="John Doe" />
            <Input label="Email" type="email" placeholder="you@example.com" />
            <Input label="Password" type="password" placeholder="••••••••" />
            <Input label="Confirm Password" type="password" placeholder="••••••••" />
            <Button className="w-full">Create Account</Button>
          </div>
          <p className="text-sm text-muted-foreground">
            Already have an account?{" "}
            <Link href="/auth/split/login" className="text-primary hover:underline">
              Sign In
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
