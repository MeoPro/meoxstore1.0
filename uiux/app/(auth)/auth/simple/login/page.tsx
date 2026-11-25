import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Checkbox } from "@/components/ui/checkbox";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Login",
};

export default function SimpleLoginPage() {
  return (
    <div className="w-full max-w-sm space-y-6">
      <AuthBrand className="mx-auto" />
      <div className="text-center">
        <h1 className="text-2xl font-bold">Welcome Back</h1>
        <p className="mt-1 text-sm text-muted-foreground">Sign in to your account</p>
      </div>

      <div className="space-y-4">
        <Input label="Email" type="email" placeholder="you@example.com" />
        <Input label="Password" type="password" placeholder="••••••••" />
        <div className="flex items-center justify-between text-sm">
          <Checkbox label="Remember me" />
          <Link href="/auth/simple/forgot-password" className="text-primary hover:underline">
            Forgot password?
          </Link>
        </div>
        <Button className="w-full">Sign In</Button>
      </div>

      <p className="text-center text-sm text-muted-foreground">
        Don&apos;t have an account?{" "}
        <Link href="/auth/simple/register" className="text-primary hover:underline">
          Sign Up
        </Link>
      </p>
    </div>
  );
}
