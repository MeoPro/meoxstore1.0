"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { EmptyState } from "@/components/ui/empty-state";
import { GradientCard } from "@/components/ui/gradient-card";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { orders } from "@/data";
import { cn, formatCurrency } from "@/lib/utils";
import type { OrderStatus, PaymentStatus } from "@/types/modules";
import {
  BadgeDollarSign,
  Check,
  ChevronLeft,
  ChevronRight,
  CircleDollarSign,
  Clock,
  Eye,
  PackageCheck,
  RefreshCcw,
  Search,
  ShoppingCart,
  Trash2,
  X,
} from "lucide-react";
import Link from "next/link";
import { useMemo, useState } from "react";

const PAGE_SIZE = 5;

const statusTabs: { id: "all" | OrderStatus; label: string; dot: string }[] = [
  { id: "all", label: "All", dot: "bg-muted-foreground" },
  { id: "pending", label: "Pending", dot: "bg-warning" },
  { id: "processing", label: "Processing", dot: "bg-info" },
  { id: "shipped", label: "Shipped", dot: "bg-primary" },
  { id: "delivered", label: "Delivered", dot: "bg-success" },
  { id: "cancelled", label: "Cancelled", dot: "bg-destructive" },
];

const paymentOptions: { id: "all" | PaymentStatus; label: string }[] = [
  { id: "all", label: "All payments" },
  { id: "paid", label: "Paid" },
  { id: "pending", label: "Pending" },
  { id: "refunded", label: "Refunded" },
];

function statusBadge(
  status: OrderStatus,
): "warning" | "info" | "default" | "success" | "destructive" {
  const map: Record<OrderStatus, "warning" | "info" | "default" | "success" | "destructive"> = {
    pending: "warning",
    processing: "info",
    shipped: "default",
    delivered: "success",
    cancelled: "destructive",
  };
  return map[status];
}

function paymentBadge(payment: PaymentStatus): "success" | "warning" | "destructive" {
  const map: Record<PaymentStatus, "success" | "warning" | "destructive"> = {
    paid: "success",
    pending: "warning",
    refunded: "destructive",
  };
  return map[payment];
}

export default function OrdersPage() {
  const [query, setQuery] = useState("");
  const [status, setStatus] = useState<"all" | OrderStatus>("all");
  const [payment, setPayment] = useState<"all" | PaymentStatus>("all");
  const [page, setPage] = useState(1);
  const [selected, setSelected] = useState<Set<string>>(new Set());
  const [removed, setRemoved] = useState<Set<string>>(new Set());
  const [banner, setBanner] = useState<{ type: "success" | "error"; text: string } | null>(null);

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    return orders.filter((o) => {
      if (removed.has(o.id)) return false;
      if (status !== "all" && o.status !== status) return false;
      if (payment !== "all" && o.payment !== payment) return false;
      if (
        q &&
        !o.customer.toLowerCase().includes(q) &&
        !o.orderNo.toLowerCase().includes(q) &&
        !o.email.toLowerCase().includes(q)
      )
        return false;
      return true;
    });
  }, [query, status, payment, removed]);

  const pageCount = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const currentPage = Math.min(page, pageCount);
  const paged = filtered.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);

  const revenue = filtered.reduce((s, o) => s + o.total, 0);
  const pending = filtered.filter((o) => o.status === "pending").length;
  const delivered = filtered.filter((o) => o.status === "delivered").length;

  const clearFilters = () => {
    setQuery("");
    setStatus("all");
    setPayment("all");
    setPage(1);
  };

  const onDeleteOne = (id: string) => {
    setRemoved((prev) => new Set([...prev, id]));
    setBanner({ type: "success", text: "Order removed from the list." });
  };

  const onBatchDelete = () => {
    const ids = [...selected];
    if (ids.length === 0) return;
    setRemoved((prev) => new Set([...prev, ...ids]));
    setBanner({
      type: "success",
      text: `${ids.length} order${ids.length > 1 ? "s" : ""} removed.`,
    });
    setSelected(new Set());
  };

  const onToggleOne = (id: string) => {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const stats = [
    {
      title: "Total revenue",
      value: formatCurrency(revenue),
      icon: <CircleDollarSign className="h-4 w-4" />,
      variant: "primary" as const,
    },
    {
      title: "Total orders",
      value: filtered.length,
      icon: <ShoppingCart className="h-4 w-4" />,
      variant: "info" as const,
    },
    {
      title: "Pending",
      value: pending,
      icon: <Clock className="h-4 w-4" />,
      variant: "warning" as const,
    },
    {
      title: "Delivered",
      value: delivered,
      icon: <PackageCheck className="h-4 w-4" />,
      variant: "success" as const,
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Orders"
        description="Track and manage customer orders."
        actions={
          <Button
            variant="outline"
            onClick={() => setBanner({ type: "success", text: "Order report exported." })}
          >
            <BadgeDollarSign className="h-4 w-4" />
            Export
          </Button>
        }
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map((s) => (
          <GradientCard key={s.title} {...s} />
        ))}
      </div>

      <Card>
        <CardContent className="p-5">
          <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center">
              <div className="lg:w-64">
                <Input
                  icon={<Search className="h-4 w-4" />}
                  value={query}
                  onChange={(e) => {
                    setQuery(e.target.value);
                    setPage(1);
                  }}
                  placeholder="Search orders..."
                />
              </div>
              <Select
                aria-label="Filter by payment status"
                value={payment}
                onChange={(e) => {
                  setPayment(e.target.value as "all" | PaymentStatus);
                  setPage(1);
                }}
              >
                {paymentOptions.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.label}
                  </option>
                ))}
              </Select>
            </div>
            {(query || status !== "all" || payment !== "all") && (
              <Button size="sm" variant="ghost" onClick={clearFilters}>
                <RefreshCcw className="h-4 w-4" />
                Reset
              </Button>
            )}
          </div>

          <div className="mt-4 flex flex-wrap items-center gap-1 border-b border-border pb-4">
            {statusTabs.map((tab) => {
              const count =
                tab.id === "all"
                  ? filtered.length
                  : filtered.filter((o) => o.status === tab.id).length;
              return (
                <button
                  key={tab.id}
                  onClick={() => {
                    setStatus(tab.id);
                    setPage(1);
                  }}
                  className={cn(
                    "flex h-8 items-center gap-1.5 rounded-md px-3 text-sm font-medium transition-colors",
                    status === tab.id
                      ? "bg-primary text-primary-foreground"
                      : "text-muted-foreground hover:bg-muted hover:text-foreground",
                  )}
                >
                  <span className={cn("h-2 w-2 rounded-full", tab.dot)} />
                  {tab.label}
                  <span
                    className={cn(
                      "rounded-full px-1.5 text-2xs font-semibold",
                      status === tab.id ? "bg-primary-foreground/20" : "bg-muted",
                    )}
                  >
                    {count}
                  </span>
                </button>
              );
            })}
          </div>
        </CardContent>

        <CardContent className="p-0">
          {banner && (
            <div
              className={cn(
                "flex items-center justify-between gap-3 border-b border-border px-5 py-3 text-sm animate-fade-in",
                banner.type === "success"
                  ? "bg-success/5 text-success"
                  : "bg-destructive/5 text-destructive",
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

          {selected.size > 0 && (
            <div className="flex items-center justify-between gap-3 border-b border-border bg-primary/5 px-5 py-3 text-sm animate-fade-in">
              <span>
                <strong>{selected.size}</strong> selected
              </span>
              <Button size="sm" variant="destructive" onClick={onBatchDelete}>
                <Trash2 className="h-4 w-4" />
                Delete selected
              </Button>
            </div>
          )}

          {paged.length === 0 ? (
            <EmptyState
              className="m-4"
              icon={<ShoppingCart className="h-8 w-8" />}
              title="No orders found"
              description="Try adjusting your search or filters to find what you're looking for."
              action={{ label: "Clear filters", onClick: clearFilters }}
            />
          ) : (
            <div className="overflow-x-auto">
              <table className="data-table w-full text-sm">
                <thead>
                  <tr className="border-b border-border bg-muted/50">
                    <th className="w-10 px-4 py-3">
                      <input
                        type="checkbox"
                        className="h-4 w-4 accent-primary"
                        checked={paged.length > 0 && selected.size === paged.length}
                        onChange={(e) =>
                          setSelected(
                            e.target.checked ? new Set(paged.map((o) => o.id)) : new Set(),
                          )
                        }
                        aria-label="Select all"
                      />
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Order</th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Customer
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Items</th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Total</th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Payment
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Status
                    </th>
                    <th className="py-3 pr-4 text-right font-medium text-muted-foreground">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {paged.map((order) => {
                    const isSelected = selected.has(order.id);
                    return (
                      <tr
                        key={order.id}
                        className={cn(
                          "border-b border-border last:border-0",
                          isSelected ? "bg-muted/60" : "hover:bg-muted/50",
                        )}
                      >
                        <td className="px-4 py-3">
                          <input
                            type="checkbox"
                            className="h-4 w-4 accent-primary"
                            checked={isSelected}
                            onChange={() => onToggleOne(order.id)}
                            aria-label={`Select ${order.orderNo}`}
                          />
                        </td>
                        <td className="px-4 py-3">
                          <Link
                            href="/app/ecommerce/orders/details"
                            className="font-semibold transition-colors hover:text-primary"
                          >
                            {order.orderNo}
                          </Link>
                          <p className="text-xs text-muted-foreground">{order.date}</p>
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-2">
                            <Avatar
                              name={order.customer}
                              size="sm"
                              className="bg-gradient-to-br from-sky-500 to-blue-600"
                            />
                            <div className="min-w-0">
                              <p className="truncate font-medium">{order.customer}</p>
                              <p className="truncate text-xs text-muted-foreground">
                                {order.email}
                              </p>
                            </div>
                          </div>
                        </td>
                        <td className="px-4 py-3">{order.items}</td>
                        <td className="px-4 py-3 font-semibold">{formatCurrency(order.total)}</td>
                        <td className="px-4 py-3">
                          <Badge variant={paymentBadge(order.payment)}>{order.payment}</Badge>
                        </td>
                        <td className="px-4 py-3">
                          <Badge variant={statusBadge(order.status)}>{order.status}</Badge>
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center justify-end gap-1">
                            <Link
                              href="/app/ecommerce/orders/details"
                              aria-label="View order"
                              className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                              <Eye className="h-4 w-4" />
                            </Link>
                            <button
                              onClick={() => onDeleteOne(order.id)}
                              aria-label="Delete order"
                              className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                            >
                              <Trash2 className="h-4 w-4" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </CardContent>

        {pageCount > 1 && paged.length > 0 && (
          <div className="flex flex-col items-center justify-between gap-3 border-t border-border px-5 py-3 sm:flex-row">
            <p className="text-sm text-muted-foreground">
              Showing{" "}
              <span className="font-medium text-foreground">
                {(currentPage - 1) * PAGE_SIZE + 1}
              </span>
              –
              <span className="font-medium text-foreground">
                {Math.min(currentPage * PAGE_SIZE, filtered.length)}
              </span>{" "}
              of <span className="font-medium text-foreground">{filtered.length}</span>
            </p>
            <div className="flex items-center gap-1">
              <Button
                size="icon-sm"
                variant="outline"
                disabled={currentPage <= 1}
                onClick={() => setPage(currentPage - 1)}
                aria-label="Previous page"
              >
                <ChevronLeft className="h-4 w-4" />
              </Button>
              {Array.from({ length: pageCount }, (_, i) => i + 1).map((n) => (
                <button
                  key={n}
                  onClick={() => setPage(n)}
                  className={cn(
                    "flex h-8 w-8 items-center justify-center rounded-md text-sm font-medium transition-colors",
                    n === currentPage
                      ? "bg-primary text-primary-foreground"
                      : "text-muted-foreground hover:bg-muted hover:text-foreground",
                  )}
                >
                  {n}
                </button>
              ))}
              <Button
                size="icon-sm"
                variant="outline"
                disabled={currentPage >= pageCount}
                onClick={() => setPage(currentPage + 1)}
                aria-label="Next page"
              >
                <ChevronRight className="h-4 w-4" />
              </Button>
            </div>
          </div>
        )}
      </Card>
    </div>
  );
}
