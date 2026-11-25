import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Register - Card",
};

export default function CardRegisterPage() {
  return (
    <Card className="w-full max-w-md shadow-dropdown">
      <CardHeader className="text-center">
        <AuthBrand className="mx-auto mb-2" />
        <CardTitle className="text-2xl">Create Account</CardTitle>
        <p className="text-sm text-muted-foreground">Sign up to get started</p>
      </CardHeader>
      <CardContent className="space-y-4">
        <Input label="Full Name" placeholder="John Doe" />
        <Input label="Email" type="email" placeholder="you@example.com" />
        <Input label="Password" type="password" placeholder="••••••••" />
        <Input label="Confirm Password" type="password" placeholder="••••••••" />
        <Button className="w-full">Create Account</Button>
        <p className="text-center text-sm text-muted-foreground">
          Already have an account?{" "}
          <Link href="/auth/card/login" className="text-primary hover:underline">
            Sign In
          </Link>
        </p>
      </CardContent>
    </Card>
  );
}
