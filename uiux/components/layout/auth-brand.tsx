import { LogoMark } from "@/components/ui/logo";
import { cn } from "@/lib/utils";
import Link from "next/link";

export function AuthBrand({ className }: { className?: string }) {
  return (
    <Link
      href="/dashboard"
      aria-label="DropX Admin dashboard"
      className={cn("inline-flex", className)}
    >
      <LogoMark />
    </Link>
  );
}
