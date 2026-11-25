"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { formatCurrency } from "@/lib/utils";
import { Check, Download, Printer, X } from "lucide-react";
import { useState } from "react";

const invoice = {
  id: "INV-2045",
  date: "Aug 1, 2026",
  due: "Aug 31, 2026",
  status: "paid" as const,
  from: {
    name: "DropX Inc.",
    address: "123 Business Ave",
    city: "San Francisco, CA 94102",
  },
  to: {
    name: "John Smith",
    address: "123 Main Street, New York, NY 10001",
    email: "john@example.com",
  },
  items: [
    { description: "Wireless Headphones Pro", qty: 1, rate: 149.99 },
    { description: "USB-C Hub 7-in-1", qty: 2, rate: 59.99 },
    { description: "Desk Pad XL", qty: 1, rate: 34.99 },
  ],
  taxRate: 0.08,
  discount: 0.1,
};

export default function InvoicePage() {
  const [downloaded, setDownloaded] = useState(false);
  const [paid, setPaid] = useState(invoice.status === "paid");

  const subtotal = invoice.items.reduce((s, it) => s + it.rate * it.qty, 0);
  const discount = invoice.discount * subtotal;
  const taxed = (subtotal - discount) * invoice.taxRate;
  const total = subtotal - discount + taxed;

  const handlePrint = () => {
    if (typeof window !== "undefined") window.print();
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Invoice"
        description={`Invoice ${invoice.id}`}
        actions={
          <div className="flex flex-wrap gap-2">
            <Button variant="outline" onClick={handlePrint}>
              <Printer className="h-4 w-4" />
              Print
            </Button>
            <Button onClick={() => setDownloaded(true)}>
              <Download className="h-4 w-4" />
              Download PDF
            </Button>
          </div>
        }
      />

      {downloaded && (
        <div className="flex items-center justify-between gap-3 rounded-lg border border-success/30 bg-success/5 px-4 py-3 text-sm text-success animate-fade-in">
          <div className="flex items-center gap-2">
            <Download className="h-4 w-4" />
            Invoice {invoice.id} downloaded as PDF.
          </div>
          <button
            onClick={() => setDownloaded(false)}
            className="text-muted-foreground hover:text-foreground"
            aria-label="Dismiss"
          >
            <X className="h-4 w-4" />
          </button>
        </div>
      )}

      <Card>
        <CardContent className="p-6 sm:p-8">
          <div className="flex flex-col justify-between gap-6 sm:flex-row sm:items-start">
            <div>
              <div className="flex items-center gap-2">
                <p className="text-2xl font-bold">{invoice.from.name}</p>
                {paid ? (
                  <Badge variant="success">
                    <Check className="mr-1 h-3 w-3" /> Paid
                  </Badge>
                ) : (
                  <Badge variant="warning">Pending</Badge>
                )}
              </div>
              <p className="text-sm text-muted-foreground">{invoice.from.address}</p>
              <p className="text-sm text-muted-foreground">{invoice.from.city}</p>
            </div>
            <div className="text-left sm:text-right">
              <p className="text-lg font-semibold">Invoice {invoice.id}</p>
              <p className="text-sm text-muted-foreground">
                Issued: {invoice.date} · Due: {invoice.due}
              </p>
            </div>
          </div>

          <div className="mt-8 grid gap-6 sm:grid-cols-2">
            <div className="rounded-lg border border-border bg-muted/30 p-4">
              <p className="text-xs font-medium text-muted-foreground">BILL TO</p>
              <p className="mt-1 font-semibold">{invoice.to.name}</p>
              <p className="text-sm text-muted-foreground">{invoice.to.address}</p>
              <p className="text-sm text-muted-foreground">{invoice.to.email}</p>
            </div>
            <div className="rounded-lg border border-border bg-muted/30 p-4">
              <p className="text-xs font-medium text-muted-foreground">ORDER SUMMARY</p>
              <p className="mt-1 font-semibold">#{invoice.id.replace("INV-", "ORD-")}</p>
              <p className="text-sm text-muted-foreground">Payment via Visa •••• 4242</p>
              <div className={paid ? "mt-1" : "mt-2 flex items-center gap-2"}>
                {!paid && (
                  <Button size="sm" variant="success" onClick={() => setPaid(true)}>
                    Mark as paid
                  </Button>
                )}
              </div>
            </div>
          </div>

          <div className="mt-8 overflow-x-auto">
            <table className="data-table w-full text-sm">
              <thead>
                <tr className="border-b border-border">
                  <th className="py-2 text-left font-medium text-muted-foreground">Description</th>
                  <th className="py-2 text-center font-medium text-muted-foreground">Qty</th>
                  <th className="py-2 text-right font-medium text-muted-foreground">Rate</th>
                  <th className="py-2 text-right font-medium text-muted-foreground">Amount</th>
                </tr>
              </thead>
              <tbody>
                {invoice.items.map((item) => (
                  <tr key={item.description} className="border-b border-border">
                    <td className="py-3 font-medium">{item.description}</td>
                    <td className="py-3 text-center">{item.qty}</td>
                    <td className="py-3 text-right text-muted-foreground">
                      {formatCurrency(item.rate)}
                    </td>
                    <td className="py-3 text-right font-medium">
                      {formatCurrency(item.rate * item.qty)}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="mt-6 flex justify-end">
            <div className="w-full space-y-2 sm:w-72">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Subtotal</span>
                <span>{formatCurrency(subtotal)}</span>
              </div>
              {discount > 0 && (
                <div className="flex justify-between text-sm text-success">
                  <span>Discount</span>
                  <span>-{formatCurrency(discount)}</span>
                </div>
              )}
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">
                  Tax ({(invoice.taxRate * 100).toFixed(0)}%)
                </span>
                <span>{formatCurrency(taxed)}</span>
              </div>
              <div className="flex justify-between border-t border-border pt-2 text-base font-semibold">
                <span>Total</span>
                <span>{formatCurrency(total)}</span>
              </div>
            </div>
          </div>

          <p className="mt-8 text-center text-xs text-muted-foreground">
            Thank you for your business. For questions about this invoice, contact{" "}
            <span className="font-medium text-foreground">billing@dropx.io</span>
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
