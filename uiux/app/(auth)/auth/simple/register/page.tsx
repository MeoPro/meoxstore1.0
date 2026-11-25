import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Register",
};

export default function SimpleRegisterPage() {
  return (
    <div className="w-full max-w-sm space-y-6">
      <AuthBrand className="mx-auto" />
      <div className="text-center">
        <h1 className="text-2xl font-bold">Create Account</h1>
        <p className="mt-1 text-sm text-muted-foreground">Sign up to get started</p>
      </div>

      <div className="space-y-4">
        <Input label="Full Name" placeholder="John Doe" />
        <Input label="Email" type="email" placeholder="you@example.com" />
        <Input label="Password" type="password" placeholder="••••••••" />
        <Input label="Confirm Password" type="password" placeholder="••••••••" />
        <Button className="w-full">Create Account</Button>
      </div>

      <p className="text-center text-sm text-muted-foreground">
        Already have an account?{" "}
        <Link href="/auth/simple/login" className="text-primary hover:underline">
          Sign In
        </Link>
      </p>
    </div>
  );
}
