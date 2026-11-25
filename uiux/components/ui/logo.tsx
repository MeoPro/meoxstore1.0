import { cn } from "@/lib/utils";
import Image from "next/image";

interface LogoMarkProps {
  className?: string;
  showWordmark?: boolean;
}

export function LogoMark({ className, showWordmark = true }: LogoMarkProps) {
  return (
    <div className={cn("flex items-center gap-2.5", className)}>
      {/* Both icons ship in the HTML; the theme class decides which one shows */}
      <span className="relative flex h-9 w-9 shrink-0 items-center justify-center">
        <Image
          src="/brand/datadropx-icon-color.png"
          alt=""
          width={36}
          height={33}
          priority
          className="h-auto w-9 dark:hidden"
        />
        <Image
          src="/brand/datadropx-icon-white.png"
          alt=""
          width={36}
          height={33}
          priority
          className="hidden h-auto w-9 dark:block"
        />
      </span>
      {showWordmark && (
        <div className="min-w-0">
          <p className="truncate text-[15px] font-bold leading-none text-foreground">DropX Admin</p>
          <p className="mt-1 truncate text-2xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
            by DataDropX
          </p>
        </div>
      )}
    </div>
  );
}
