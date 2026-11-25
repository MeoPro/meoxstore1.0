import { AuthBrand } from "@/components/layout/auth-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Lock Screen - Split",
};

export default function SplitLockScreenPage() {
  return (
    <div className="grid w-full self-stretch lg:grid-cols-2">
      <div className="hidden lg:flex flex-col items-center justify-center bg-primary p-8 text-primary-foreground">
        <h2 className="text-3xl font-bold">Session Locked</h2>
        <p className="mt-2 max-w-sm text-center text-primary-foreground/80">
          Enter your password to resume your session.
        </p>
      </div>
      <div className="flex items-center justify-center p-8">
        <div className="w-full max-w-sm space-y-6 text-center">
          <AuthBrand className="mx-auto" />
          <div className="mx-auto h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center text-2xl font-bold text-primary">
            JD
          </div>
          <div>
            <h1 className="text-lg font-semibold">John Doe</h1>
            <p className="text-sm text-muted-foreground">Your session is locked</p>
          </div>
          <div className="space-y-4">
            <Input label="Password" type="password" placeholder="Enter your password" />
            <Button className="w-full">Unlock</Button>
          </div>
        </div>
      </div>
    </div>
  );
}
