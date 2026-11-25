import { cn } from "@/lib/utils";
import { Nfc } from "lucide-react";

type CreditCardVariant =
  "primary" | "success" | "warning" | "danger" | "info" | "purple" | "pink" | "ocean" | "sunset";

interface CreditCardProps {
  variant?: CreditCardVariant;
  balance: string;
  cardNumber: string;
  holderName: string;
  expiry: string;
  brand?: "visa" | "mastercard" | "amex";
  className?: string;
}

function BrandLogo({ brand }: { brand: NonNullable<CreditCardProps["brand"]> }) {
  if (brand === "visa") {
    return <span className="text-lg font-extrabold italic tracking-wider">VISA</span>;
  }
  if (brand === "amex") {
    return <span className="text-lg font-bold tracking-wider">AMEX</span>;
  }
  return (
    <div className="flex">
      <span className="h-5 w-5 rounded-full bg-red-500" />
      <span className="-ml-2 h-5 w-5 rounded-full bg-yellow-400 opacity-90" />
    </div>
  );
}

export function CreditCard({
  variant = "primary",
  balance,
  cardNumber,
  holderName,
  expiry,
  brand = "visa",
  className,
}: CreditCardProps) {
  return (
    <div
      className={cn(
        `gradient-${variant} relative flex aspect-[8/5] w-full flex-col overflow-hidden rounded-2xl p-5 text-white shadow-dropdown ring-1 ring-black/5`,
        className,
      )}
    >
      <div className="absolute -right-8 -top-10 h-40 w-40 rounded-full bg-white/10" />
      <div className="absolute -bottom-14 -right-6 h-48 w-48 rounded-full bg-white/5" />

      <div className="relative flex h-full flex-col justify-between">
        <div className="flex items-start justify-between">
          <span className="text-sm font-semibold tracking-wide">DropX</span>
          <BrandLogo brand={brand} />
        </div>

        <div>
          <div className="flex items-center gap-3">
            <div className="relative h-7 w-10 rounded-md bg-gradient-to-br from-yellow-200 to-yellow-500">
              <div className="absolute inset-x-0 top-1/2 h-px bg-black/20" />
              <div className="absolute inset-x-1.5 top-1/2 -translate-y-1/2 space-y-0.5">
                <span className="block h-px bg-black/25" />
                <span className="block h-px bg-black/25" />
              </div>
            </div>
            <Nfc className="h-5 w-5 text-white/70" />
          </div>

          <p className="mt-3 text-2xs font-medium uppercase tracking-wider text-white/60">
            Balance
          </p>
          <p className="text-2xl font-bold tracking-tight">{balance}</p>
        </div>

        <div className="space-y-3">
          <p className="font-mono text-base tracking-[0.12em]">{cardNumber}</p>
          <div className="flex items-end justify-between text-xs">
            <div>
              <p className="font-medium uppercase tracking-wider text-white/60">Card Holder</p>
              <p className="mt-0.5 font-semibold">{holderName}</p>
            </div>
            <div>
              <p className="font-medium uppercase tracking-wider text-white/60">Expires</p>
              <p className="mt-0.5 font-semibold">{expiry}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
