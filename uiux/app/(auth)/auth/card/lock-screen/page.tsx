import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Lock Screen - Card",
};

export default function CardLockScreenPage() {
  return (
    <Card className="w-full max-w-md shadow-dropdown text-center">
      <CardHeader>
        <AuthBrand className="mx-auto mb-4" />
        <div className="mx-auto h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center text-2xl font-bold text-primary">
          JD
        </div>
        <CardTitle className="mt-3">John Doe</CardTitle>
        <p className="text-sm text-muted-foreground">Your session is locked</p>
      </CardHeader>
      <CardContent className="space-y-4">
        <Input label="Password" type="password" placeholder="Enter your password" />
        <Button className="w-full">Unlock</Button>
      </CardContent>
    </Card>
  );
}
