import { Button } from "@/components/ui/button";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "404 - Page Not Found",
};

export default function Error404Page() {
  return (
    <div className="flex min-h-[60vh] flex-col items-center justify-center text-center">
      <h1 className="text-7xl font-bold text-primary">404</h1>
      <h2 className="mt-4 text-2xl font-semibold">Page Not Found</h2>
      <p className="mt-2 max-w-md text-muted-foreground">
        The page you are looking for might have been removed, had its name changed, or is
        temporarily unavailable.
      </p>
      <Link href="/" className="mt-6">
        <Button size="lg">Go Home</Button>
      </Link>
    </div>
  );
}
