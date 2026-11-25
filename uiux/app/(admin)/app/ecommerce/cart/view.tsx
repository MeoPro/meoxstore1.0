"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { EmptyState } from "@/components/ui/empty-state";
import { cartItems } from "@/data";
import { cn, formatCurrency } from "@/lib/utils";
import {
  ArrowRight,
  Check,
  CreditCard,
  Headphones,
  Laptop,
  Minus,
  Plus,
  ShoppingCart,
  Tag,
  Trash2,
  Truck,
  X,
} from "lucide-react";
import Link from "next/link";
import { useState } from "react";

function visualFor(name: string): { icon: typeof Laptop; gradient: string } {
  if (name.includes("Headphone"))
    return { icon: Headphones, gradient: "from-violet-500 to-purple-600" };
  if (name.includes("Hub") || name.includes("Stand") || name.includes("Pad"))
    return { icon: Laptop, gradient: "from-amber-500 to-orange-600" };
  return { icon: Laptop, gradient: "from-emerald-500 to-teal-600" };
}

interface CartLine {
  id: string;
  name: string;
  price: number;
  quantity: number;
  image: string;
}

export default function CartPage() {
  const [items, setItems] = useState<CartLine[]>(cartItems.map((c) => ({ ...c })));
  const [promo, setPromo] = useState("");
  const [appliedPromo, setAppliedPromo] = useState<string | null>(null);
  const [promoError, setPromoError] = useState<string | null>(null);
  const [banner, setBanner] = useState<string | null>(null);

  const updateQty = (id: string, delta: number) => {
    setItems((prev) =>
      prev
        .map((it) => (it.id === id ? { ...it, quantity: Math.max(0, it.quantity + delta) } : it))
        .filter((it) => it.quantity > 0),
    );
  };

  const removeItem = (id: string) => {
    setItems((prev) => prev.filter((it) => it.id !== id));
    setBanner("Item removed from your cart.");
  };

  const applyPromo = () => {
    const code = promo.trim().toUpperCase();
    if (!code) return;
    if (code === "SAVE10" || code === "DROPPX") {
      setAppliedPromo(code);
      setPromoError(null);
    } else {
      setPromoError("Invalid promo code. Try SAVE10.");
      setAppliedPromo(null);
    }
  };

  const subtotal = items.reduce((s, it) => s + it.price * it.quantity, 0);
  const discount = appliedPromo ? subtotal * 0.1 : 0;
  const shipping = subtotal === 0 || subtotal - discount >= 149.99 ? 0 : 9.99;
  const total = subtotal - discount + shipping;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Shopping Cart"
        description="Review your items before checkout."
        actions={
          <Link href="/app/ecommerce/products">
            <Button variant="outline">
              <ArrowRight className="h-4 w-4" />
              Continue shopping
            </Button>
          </Link>
        }
      />

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Card>
            <CardContent className="p-0">
              {banner && (
                <div className="flex items-center justify-between gap-3 border-b border-border bg-success/5 px-5 py-3 text-sm text-success animate-fade-in">
                  <div className="flex items-center gap-2">
                    <Check className="h-4 w-4" />
                    {banner}
                  </div>
                  <button
                    onClick={() => setBanner(null)}
                    className="text-muted-foreground hover:text-foreground"
                    aria-label="Dismiss"
                  >
                    <X className="h-4 w-4" />
                  </button>
                </div>
              )}

              {items.length === 0 ? (
                <EmptyState
                  className="m-4"
                  icon={<ShoppingCart className="h-8 w-8" />}
                  title="Your cart is empty"
                  description="Browse the catalog and add some products to get started."
                  action={{ label: "Browse products", onClick: () => {} }}
                />
              ) : (
                <div className="divide-y divide-border">
                  {items.map((item) => {
                    const vis = visualFor(item.name);
                    return (
                      <div
                        key={item.id}
                        className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:gap-4"
                      >
                        <div
                          className={cn(
                            "flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br text-white",
                            vis.gradient,
                          )}
                        >
                          <vis.icon className="h-7 w-7" />
                        </div>
                        <div className="min-w-0 flex-1">
                          <Link
                            href="/app/ecommerce/products/details"
                            className="truncate text-sm font-semibold transition-colors hover:text-primary"
                          >
                            {item.name}
                          </Link>
                          <p className="mt-0.5 text-xs text-muted-foreground">
                            {formatCurrency(item.price)} each
                          </p>
                        </div>
                        <div className="flex items-center gap-3">
                          <div className="flex items-center rounded-md border border-border">
                            <button
                              onClick={() => updateQty(item.id, -1)}
                              aria-label="Decrease quantity"
                              className="flex h-8 w-8 items-center justify-center rounded-l-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                              <Minus className="h-3.5 w-3.5" />
                            </button>
                            <span className="w-8 text-center text-sm font-medium">
                              {item.quantity}
                            </span>
                            <button
                              onClick={() => updateQty(item.id, 1)}
                              aria-label="Increase quantity"
                              className="flex h-8 w-8 items-center justify-center rounded-r-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                              <Plus className="h-3.5 w-3.5" />
                            </button>
                          </div>
                          <span className="w-20 text-right text-sm font-semibold">
                            {formatCurrency(item.price * item.quantity)}
                          </span>
                          <button
                            onClick={() => removeItem(item.id)}
                            aria-label="Remove item"
                            className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-5">
              <p className="text-sm font-semibold">Apply promo code</p>
              <div className="mt-2 flex flex-col gap-2 sm:flex-row">
                <input
                  value={promo}
                  onChange={(e) => setPromo(e.target.value)}
                  placeholder="e.g. SAVE10"
                  className="flex h-9 w-full rounded-lg border border-input bg-transparent px-3 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring sm:max-w-xs"
                />
                <Button variant="outline" onClick={applyPromo}>
                  <Tag className="h-4 w-4" />
                  Apply
                </Button>
              </div>
              {appliedPromo && (
                <div className="mt-3 flex items-center gap-2 rounded-lg border border-success/30 bg-success/5 px-3 py-2 text-sm text-success animate-fade-in">
                  <Check className="h-4 w-4" />
                  Promo {appliedPromo} applied — 10% off.
                </div>
              )}
              {promoError && <p className="mt-2 text-xs text-destructive">{promoError}</p>}
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">
                  Subtotal ({items.reduce((s, it) => s + it.quantity, 0)} items)
                </span>
                <span className="font-medium">{formatCurrency(subtotal)}</span>
              </div>
              {discount > 0 && (
                <div className="flex justify-between text-sm text-success">
                  <span>Discount (10%)</span>
                  <span className="font-medium">-{formatCurrency(discount)}</span>
                </div>
              )}
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Shipping</span>
                <span className="font-medium">
                  {shipping === 0 ? "Free" : formatCurrency(shipping)}
                </span>
              </div>
              <div className="flex justify-between border-t border-border pt-3 text-base font-semibold">
                <span>Total</span>
                <span>{formatCurrency(total)}</span>
              </div>
              <div className="flex items-center gap-2 text-xs text-muted-foreground">
                <Truck className="h-4 w-4" />
                {shipping === 0
                  ? "You qualify for free shipping."
                  : `Free shipping on orders over ${formatCurrency(149.99)}.`}
              </div>
              <Link href="/app/ecommerce/checkout">
                <Button className="mt-2 w-full">
                  <CreditCard className="h-4 w-4" />
                  Proceed to Checkout
                </Button>
              </Link>
              <div className="flex items-center justify-center gap-1 pt-1">
                <Badge variant="secondary">Visa</Badge>
                <Badge variant="secondary">Mastercard</Badge>
                <Badge variant="secondary">PayPal</Badge>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-gradient-to-br from-primary to-purple-600 text-white">
            <CardContent className="p-5">
              <p className="text-sm font-semibold text-white/90">Need help?</p>
              <p className="mt-1 text-xs text-white/70">
                Our support team is available 24/7 to help with your order.
              </p>
              <Button
                variant="secondary"
                size="sm"
                className="mt-3 bg-white/15 text-white hover:bg-white/25"
              >
                Contact support
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
