import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Checkbox } from "@/components/ui/checkbox";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Login - Split",
};

export default function SplitLoginPage() {
  return (
    <div className="grid w-full self-stretch lg:grid-cols-2">
      <div className="hidden lg:flex flex-col items-center justify-center bg-primary p-8 text-primary-foreground">
        <h2 className="text-3xl font-bold">Welcome Back</h2>
        <p className="mt-2 max-w-sm text-center text-primary-foreground/80">
          Sign in to access your dashboard and manage your projects.
        </p>
      </div>
      <div className="flex items-center justify-center p-8">
        <div className="w-full max-w-sm space-y-6">
          <AuthBrand />
          <div>
            <h1 className="text-2xl font-bold">Sign In</h1>
            <p className="mt-1 text-sm text-muted-foreground">Enter your credentials below</p>
          </div>
          <div className="space-y-4">
            <Input label="Email" type="email" placeholder="you@example.com" />
            <Input label="Password" type="password" placeholder="••••••••" />
            <div className="flex items-center justify-between text-sm">
              <Checkbox label="Remember me" />
              <Link href="/auth/split/forgot-password" className="text-primary hover:underline">
                Forgot password?
              </Link>
            </div>
            <Button className="w-full">Sign In</Button>
          </div>
          <p className="text-sm text-muted-foreground">
            Don&apos;t have an account?{" "}
            <Link href="/auth/split/register" className="text-primary hover:underline">
              Sign Up
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
