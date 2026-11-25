"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { cn, formatCurrency } from "@/lib/utils";
import { Check, CreditCard, Download, FileText, Plus, Sparkles, Trash2, X } from "lucide-react";
import { useState } from "react";

const plans = [
  {
    id: "starter",
    name: "Starter",
    price: 49,
    description: "For individuals and small stores just getting started.",
    features: ["Up to 100 products", "2 staff accounts", "Basic analytics", "Email support"],
  },
  {
    id: "pro",
    name: "Pro",
    price: 99,
    description: "For growing stores that need more power and control.",
    features: [
      "Unlimited products",
      "10 staff accounts",
      "Advanced analytics",
      "Priority support",
      "Custom domains",
    ],
    popular: true,
  },
  {
    id: "enterprise",
    name: "Enterprise",
    price: 299,
    description: "For large operations with advanced requirements.",
    features: [
      "Unlimited everything",
      "Unlimited staff accounts",
      "Dedicated account manager",
      "SLA & custom integrations",
    ],
  },
];

const billingHistory = [
  { id: "INV-2045", date: "Aug 1, 2026", amount: 99.0, status: "paid" as const },
  { id: "INV-1980", date: "Jul 1, 2026", amount: 99.0, status: "paid" as const },
  { id: "INV-1912", date: "Jun 1, 2026", amount: 79.0, status: "paid" as const },
  { id: "INV-1845", date: "May 1, 2026", amount: 79.0, status: "pending" as const },
  { id: "INV-1779", date: "Apr 1, 2026", amount: 79.0, status: "paid" as const },
];

export default function BillingPage() {
  const [activePlan, setActivePlan] = useState("pro");
  const [pickPlan, setPickPlan] = useState<string | null>(null);
  const [banner, setBanner] = useState<{ type: "success" | "error"; text: string } | null>(null);
  const [cards, setCards] = useState([
    { id: "c1", brand: "Visa", last4: "4242", exp: "12/28", default: true },
  ]);
  const [showCardForm, setShowCardForm] = useState(false);
  const [newCard, setNewCard] = useState({ number: "", exp: "", cvc: "" });
  const [cardError, setCardError] = useState<string | null>(null);

  const plan = plans.find((p) => p.id === activePlan) ?? plans[1];

  const switchPlan = (id: string) => {
    setActivePlan(id);
    setPickPlan(null);
    setBanner({
      type: "success",
      text: `Plan upgraded to ${plans.find((p) => p.id === id)?.name}.`,
    });
  };

  const addCard = () => {
    if (!/^[\d ]{13,19}$/.test(newCard.number.trim())) {
      setCardError("Enter a valid card number.");
      return;
    }
    setCards((prev) => [
      ...prev.map((c) => ({ ...c, default: false })),
      {
        id: `c${Date.now()}`,
        brand: "Visa",
        last4: newCard.number.replace(/[^\d]/g, "").slice(-4),
        exp: newCard.exp,
        default: true,
      },
    ]);
    setNewCard({ number: "", exp: "", cvc: "" });
    setShowCardForm(false);
    setCardError(null);
    setBanner({ type: "success", text: "Payment method added." });
  };

  const removeCard = (id: string) => {
    setCards((prev) => {
      const rest = prev.filter((c) => c.id !== id);
      if (rest.length === 0) return prev;
      if (rest.every((c) => !c.default)) rest[0].default = true;
      return rest;
    });
    setBanner({ type: "success", text: "Payment method removed." });
  };

  return (
    <div className="space-y-6">
      <PageHeader title="Billing" description="Manage your plan, invoices, and payment methods." />

      {banner && (
        <div
          className={cn(
            "flex items-center justify-between gap-3 rounded-lg border px-4 py-3 text-sm animate-fade-in",
            banner.type === "success"
              ? "border-success/30 bg-success/5 text-success"
              : "border-destructive/30 bg-destructive/5 text-destructive",
          )}
        >
          <div className="flex items-center gap-2">
            <Check className="h-4 w-4" />
            {banner.text}
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

      <Card>
        <CardContent className="p-5">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-4">
              <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-purple-500 text-white">
                <Sparkles className="h-6 w-6" />
              </div>
              <div>
                <div className="flex items-center gap-2">
                  <p className="text-lg font-bold">{plan.name} Plan</p>
                  <Badge variant="info">Current</Badge>
                </div>
                <p className="text-sm text-muted-foreground">
                  {formatCurrency(plan.price)}/month · Next billing date: Sep 1, 2026
                </p>
              </div>
            </div>
            <Button
              variant="outline"
              onClick={() => setPickPlan(pickPlan === "pro" ? null : "pro")}
            >
              {pickPlan === "pro" ? "Close" : "Change Plan"}
            </Button>
          </div>
        </CardContent>
      </Card>

      {pickPlan && (
        <div className="grid gap-4 animate-fade-in md:grid-cols-3">
          {plans.map((p) => (
            <Card
              key={p.id}
              className={cn(
                "relative flex flex-col",
                p.popular && "border-primary ring-2 ring-primary/20",
                activePlan === p.id && "opacity-70",
              )}
            >
              {p.popular && (
                <span className="absolute -top-2.5 left-4 rounded-full bg-primary px-2.5 py-0.5 text-2xs font-semibold text-white">
                  POPULAR
                </span>
              )}
              <CardContent className="flex flex-1 flex-col gap-4 p-5">
                <div>
                  <p className="text-sm font-semibold">{p.name}</p>
                  <p className="mt-1 text-2xl font-bold">
                    {formatCurrency(p.price)}
                    <span className="text-sm font-normal text-muted-foreground">/month</span>
                  </p>
                  <p className="mt-1 text-xs text-muted-foreground">{p.description}</p>
                </div>
                <ul className="flex-1 space-y-2">
                  {p.features.map((f) => (
                    <li key={f} className="flex items-start gap-2 text-sm">
                      <Check className="mt-0.5 h-4 w-4 shrink-0 text-success" />
                      {f}
                    </li>
                  ))}
                </ul>
                <Button
                  variant={activePlan === p.id ? "outline" : p.popular ? "default" : "outline"}
                  disabled={activePlan === p.id}
                  onClick={() => switchPlan(p.id)}
                >
                  {activePlan === p.id ? "Current Plan" : "Switch to " + p.name}
                </Button>
              </CardContent>
            </Card>
          ))}
        </div>
      )}

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Billing History</CardTitle>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setBanner({ type: "success", text: "Invoice downloaded." })}
                >
                  <Download className="h-4 w-4" />
                  Download all
                </Button>
              </div>
            </CardHeader>
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <table className="data-table w-full text-sm">
                  <thead>
                    <tr className="border-b border-border bg-muted/50">
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Invoice
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Date
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Amount
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Status
                      </th>
                      <th className="py-3 pr-4 text-right font-medium text-muted-foreground">
                        Download
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {billingHistory.map((inv) => (
                      <tr
                        key={inv.id}
                        className="border-b border-border last:border-0 hover:bg-muted/50"
                      >
                        <td className="px-4 py-3 font-medium">{inv.id}</td>
                        <td className="px-4 py-3 text-muted-foreground">{inv.date}</td>
                        <td className="px-4 py-3">{formatCurrency(inv.amount)}</td>
                        <td className="px-4 py-3">
                          <Badge variant={inv.status === "paid" ? "success" : "warning"}>
                            {inv.status}
                          </Badge>
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex justify-end">
                            <Button
                              variant="ghost"
                              size="icon-sm"
                              aria-label="Download invoice"
                              onClick={() =>
                                setBanner({ type: "success", text: `${inv.id} downloaded.` })
                              }
                            >
                              <FileText className="h-4 w-4" />
                            </Button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card className="h-fit">
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle>Payment Methods</CardTitle>
              <Button variant="outline" size="sm" onClick={() => setShowCardForm((v) => !v)}>
                <Plus className="h-4 w-4" />
                Add
              </Button>
            </div>
          </CardHeader>
          <CardContent className="space-y-3">
            {cards.map((card) => (
              <div
                key={card.id}
                className="flex items-center gap-3 rounded-lg border border-border p-3"
              >
                <div className="flex h-9 w-12 items-center justify-center rounded-md bg-gradient-to-br from-indigo-500 to-purple-600 text-2xs font-bold text-white">
                  {card.brand.toUpperCase().slice(0, 4)}
                </div>
                <div className="min-w-0 flex-1">
                  <p className="text-sm font-medium">•••• •••• •••• {card.last4}</p>
                  <p className="text-xs text-muted-foreground">
                    Expires {card.exp}
                    {card.default && " · Default"}
                  </p>
                </div>
                <button
                  onClick={() => removeCard(card.id)}
                  aria-label="Remove card"
                  className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                >
                  <Trash2 className="h-4 w-4" />
                </button>
              </div>
            ))}

            {showCardForm && (
              <div className="space-y-3 rounded-lg border border-border bg-muted/30 p-3 animate-fade-in">
                <input
                  value={newCard.number}
                  onChange={(e) => setNewCard((c) => ({ ...c, number: e.target.value }))}
                  placeholder="Card number"
                  className="h-9 w-full rounded-lg border border-input bg-transparent px-3 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                />
                <div className="grid grid-cols-2 gap-2">
                  <input
                    value={newCard.exp}
                    onChange={(e) => setNewCard((c) => ({ ...c, exp: e.target.value }))}
                    placeholder="MM/YY"
                    className="h-9 w-full rounded-lg border border-input bg-transparent px-3 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                  />
                  <input
                    value={newCard.cvc}
                    onChange={(e) => setNewCard((c) => ({ ...c, cvc: e.target.value }))}
                    placeholder="CVC"
                    className="h-9 w-full rounded-lg border border-input bg-transparent px-3 text-sm shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                  />
                </div>
                {cardError && <p className="text-xs text-destructive">{cardError}</p>}
                <Button size="sm" className="w-full" onClick={addCard}>
                  <Check className="h-4 w-4" />
                  Add card
                </Button>
              </div>
            )}

            <div className="flex items-center gap-2 rounded-lg border border-border bg-muted/30 p-3 text-xs text-muted-foreground">
              <CreditCard className="h-4 w-4" />
              Securely stored with 256-bit encryption.
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
