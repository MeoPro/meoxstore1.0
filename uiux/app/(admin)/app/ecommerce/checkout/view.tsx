"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { cartItems } from "@/data";
import { cn, formatCurrency } from "@/lib/utils";
import { BadgeDollarSign, CreditCard, Lock, ShieldCheck, Sparkles, Truck, X } from "lucide-react";
import { useMemo, useState } from "react";

export default function CheckoutPage() {
  const [form, setForm] = useState({
    firstName: "",
    lastName: "",
    email: "",
    address: "",
    city: "",
    state: "",
    zip: "",
    cardNumber: "",
    expiry: "",
    cvc: "",
    nameOnCard: "",
  });
  const [method, setMethod] = useState<"card" | "paypal">("card");
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [placed, setPlaced] = useState(false);
  const [placing, setPlacing] = useState(false);

  const set = (key: keyof typeof form) => (e: React.ChangeEvent<HTMLInputElement>) => {
    setForm((f) => ({ ...f, [key]: e.target.value }));
    setErrors((prev) => {
      const next = { ...prev };
      delete next[key];
      return next;
    });
  };

  const subtotal = cartItems.reduce((s, it) => s + it.price * it.quantity, 0);
  const shipping = subtotal >= 149.99 ? 0 : 9.99;
  const total = subtotal + shipping;

  const summary = useMemo(() => {
    const lines = cartItems.map((it) => ({
      name: it.name,
      qty: it.quantity,
      amount: it.price * it.quantity,
    }));
    return lines;
  }, []);

  const validate = () => {
    const e: Record<string, string> = {};
    if (!form.firstName.trim()) e.firstName = "Required";
    if (!form.lastName.trim()) e.lastName = "Required";
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) e.email = "Enter a valid email";
    if (!form.address.trim()) e.address = "Required";
    if (!form.city.trim()) e.city = "Required";
    if (!form.state.trim()) e.state = "Required";
    if (!/^\d{5}(-\d{4})?$/.test(form.zip.trim())) e.zip = "Invalid ZIP";
    if (method === "card") {
      if (!/^[\d ]{13,19}$/.test(form.cardNumber.trim())) e.cardNumber = "Invalid card number";
      if (!/^\d{2}\/?\d{2}$/.test(form.expiry.trim())) e.expiry = "MM/YY";
      if (!/^\d{3,4}$/.test(form.cvc.trim())) e.cvc = "3–4 digits";
      if (!form.nameOnCard.trim()) e.nameOnCard = "Required";
    }
    return e;
  };

  const placeOrder = () => {
    if (placed) return;
    const e = validate();
    setErrors(e);
    if (Object.keys(e).length > 0) return;
    setPlacing(true);
    setTimeout(() => {
      setPlacing(false);
      setPlaced(true);
    }, 900);
  };

  return (
    <div className="space-y-6">
      <PageHeader title="Checkout" description="Complete your purchase securely." />

      {placed && (
        <div className="flex items-center justify-between gap-3 rounded-lg border border-success/30 bg-success/5 px-4 py-3 text-sm text-success animate-fade-in">
          <div className="flex items-center gap-2">
            <Sparkles className="h-4 w-4" />
            Order placed successfully! A confirmation email is on its way.
          </div>
          <button
            onClick={() => setPlaced(false)}
            className="text-muted-foreground hover:text-foreground"
            aria-label="Dismiss"
          >
            <X className="h-4 w-4" />
          </button>
        </div>
      )}

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Contact &amp; Shipping</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-4 sm:grid-cols-2">
                <Input
                  label="First Name"
                  value={form.firstName}
                  onChange={set("firstName")}
                  error={errors.firstName}
                  placeholder="John"
                />
                <Input
                  label="Last Name"
                  value={form.lastName}
                  onChange={set("lastName")}
                  error={errors.lastName}
                  placeholder="Smith"
                />
              </div>
              <Input
                label="Email"
                type="email"
                value={form.email}
                onChange={set("email")}
                error={errors.email}
                placeholder="john@example.com"
              />
              <Input
                label="Address"
                value={form.address}
                onChange={set("address")}
                error={errors.address}
                placeholder="123 Main Street"
              />
              <div className="grid gap-4 sm:grid-cols-3">
                <Input
                  label="City"
                  value={form.city}
                  onChange={set("city")}
                  error={errors.city}
                  placeholder="New York"
                />
                <Input
                  label="State"
                  value={form.state}
                  onChange={set("state")}
                  error={errors.state}
                  placeholder="NY"
                />
                <Input
                  label="ZIP Code"
                  value={form.zip}
                  onChange={set("zip")}
                  error={errors.zip}
                  placeholder="10001"
                />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <div className="flex items-center gap-2">
                <CreditCard className="h-5 w-5 text-muted-foreground" />
                <CardTitle>Payment Method</CardTitle>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex gap-2">
                {(["card", "paypal"] as const).map((m) => (
                  <button
                    key={m}
                    onClick={() => setMethod(m)}
                    className={cn(
                      "flex-1 rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors",
                      method === m
                        ? "border-primary bg-primary/5 text-primary"
                        : "border-border text-muted-foreground hover:bg-muted hover:text-foreground",
                    )}
                  >
                    {m === "card" ? "Credit / Debit Card" : "PayPal"}
                  </button>
                ))}
              </div>

              {method === "card" ? (
                <>
                  <Input
                    label="Card Number"
                    value={form.cardNumber}
                    onChange={set("cardNumber")}
                    error={errors.cardNumber}
                    placeholder="4242 4242 4242 4242"
                  />
                  <div className="grid gap-4 sm:grid-cols-3">
                    <Input
                      label="Expiry (MM/YY)"
                      value={form.expiry}
                      onChange={set("expiry")}
                      error={errors.expiry}
                      placeholder="08/28"
                    />
                    <Input
                      label="CVC"
                      value={form.cvc}
                      onChange={set("cvc")}
                      error={errors.cvc}
                      placeholder="123"
                    />
                    <Input
                      label="Name on Card"
                      value={form.nameOnCard}
                      onChange={set("nameOnCard")}
                      error={errors.nameOnCard}
                      placeholder="JOHN SMITH"
                    />
                  </div>
                </>
              ) : (
                <div className="flex items-center gap-2 rounded-lg border border-border p-4 text-sm text-muted-foreground">
                  <ShieldCheck className="h-5 w-5" />
                  You&apos;ll be redirected to PayPal to complete your payment.
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card className="h-fit">
            <CardHeader>
              <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              {summary.map((line) => (
                <div key={line.name} className="flex justify-between">
                  <span className="text-muted-foreground">
                    {line.name} × {line.qty}
                  </span>
                  <span className="font-medium">{formatCurrency(line.amount)}</span>
                </div>
              ))}
              <div className="flex justify-between">
                <span className="text-muted-foreground">Shipping</span>
                <span className="font-medium">
                  {shipping === 0 ? "Free" : formatCurrency(shipping)}
                </span>
              </div>
              <div className="flex items-center justify-between border-t border-border pt-3 text-base font-semibold">
                <span>Total</span>
                <span>{formatCurrency(total)}</span>
              </div>
              <Button
                className="mt-2 w-full"
                onClick={placeOrder}
                disabled={placed}
                isLoading={placing}
              >
                <Lock className="h-4 w-4" />
                {placed ? "Order Placed" : "Place Order"}
              </Button>
              <p className="flex items-center justify-center gap-1 pt-1 text-xs text-muted-foreground">
                <ShieldCheck className="h-3.5 w-3.5" />
                Secured with 256-bit SSL encryption
              </p>
            </CardContent>
          </Card>

          <Card className="h-fit border-border/60 bg-muted/30">
            <CardContent className="space-y-3 p-5 text-sm">
              <div className="flex items-center gap-2 font-medium">
                <Truck className="h-4 w-4 text-primary" />
                {shipping === 0 ? "Free shipping applied" : "Standard shipping"}
              </div>
              <div className="flex items-center gap-2 text-muted-foreground">
                <BadgeDollarSign className="h-4 w-4" />
                Money-back guarantee within 30 days.
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
