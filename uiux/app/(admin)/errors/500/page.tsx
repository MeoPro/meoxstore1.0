import { Button } from "@/components/ui/button";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "500 - Server Error",
};

export default function Error500Page() {
  return (
    <div className="flex min-h-[60vh] flex-col items-center justify-center text-center">
      <h1 className="text-7xl font-bold text-destructive">500</h1>
      <h2 className="mt-4 text-2xl font-semibold">Internal Server Error</h2>
      <p className="mt-2 max-w-md text-muted-foreground">
        Something went wrong on our end. Please try again later or contact support if the problem
        persists.
      </p>
      <Link href="/" className="mt-6">
        <Button size="lg">Go Home</Button>
      </Link>
    </div>
  );
}
