import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Checkbox } from "@/components/ui/checkbox";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Login - Card",
};

export default function CardLoginPage() {
  return (
    <Card className="w-full max-w-md shadow-dropdown">
      <CardHeader className="text-center">
        <AuthBrand className="mx-auto mb-2" />
        <CardTitle className="text-2xl">Welcome Back</CardTitle>
        <p className="text-sm text-muted-foreground">Sign in to your account</p>
      </CardHeader>
      <CardContent className="space-y-4">
        <Input label="Email" type="email" placeholder="you@example.com" />
        <Input label="Password" type="password" placeholder="••••••••" />
        <div className="flex items-center justify-between text-sm">
          <Checkbox label="Remember me" />
          <Link href="/auth/card/forgot-password" className="text-primary hover:underline">
            Forgot password?
          </Link>
        </div>
        <Button className="w-full">Sign In</Button>
        <p className="text-center text-sm text-muted-foreground">
          Don&apos;t have an account?{" "}
          <Link href="/auth/card/register" className="text-primary hover:underline">
            Sign Up
          </Link>
        </p>
      </CardContent>
    </Card>
  );
}
