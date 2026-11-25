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
import { customers } from "@/data";
import { cn, formatCurrency } from "@/lib/utils";
import type { Customer } from "@/types/modules";
import {
  BadgeDollarSign,
  Check,
  ChevronLeft,
  ChevronRight,
  Crown,
  Eye,
  Mail,
  RefreshCcw,
  Search,
  Trash2,
  Users,
  X,
} from "lucide-react";
import Link from "next/link";
import { useMemo, useState } from "react";

const PAGE_SIZE = 6;

const statusOptions: { id: "all" | Customer["status"] | "vip"; label: string }[] = [
  { id: "all", label: "All status" },
  { id: "active", label: "Active" },
  { id: "vip", label: "VIP" },
  { id: "inactive", label: "Inactive" },
];

const spentFilters: { id: "all" | "low" | "vip"; label: string }[] = [
  { id: "all", label: "All spend" },
  { id: "low", label: "Under $5k" },
  { id: "vip", label: "$5k+" },
];

function badgeFor(customer: Customer): { label: string; variant: "success" | "info" | "default" } {
  if (customer.spent >= 10000) return { label: "VIP", variant: "info" };
  if (customer.status === "active") return { label: "Active", variant: "success" };
  return { label: "Inactive", variant: "default" };
}

function gradient(name: string): string {
  const grads = [
    "from-emerald-400 to-teal-600",
    "from-fuchsia-400 to-purple-600",
    "from-orange-400 to-red-600",
    "from-indigo-400 to-blue-600",
    "from-rose-400 to-pink-600",
    "from-amber-400 to-orange-600",
  ];
  let h = 0;
  for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) >>> 0;
  return grads[h % grads.length];
}

export default function CustomersPage() {
  const [query, setQuery] = useState("");
  const [status, setStatus] = useState<"all" | Customer["status"] | "vip">("all");
  const [spend, setSpend] = useState<"all" | "low" | "vip">("all");
  const [page, setPage] = useState(1);
  const [removed, setRemoved] = useState<Set<string>>(new Set());
  const [banner, setBanner] = useState<{ type: "success" | "error"; text: string } | null>(null);

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    return customers.filter((c) => {
      if (removed.has(c.id)) return false;
      if (status === "vip" && c.spent < 10000) return false;
      if (status !== "all" && status !== "vip" && c.status !== status) return false;
      if (spend === "low" && c.spent >= 5000) return false;
      if (spend === "vip" && c.spent < 5000) return false;
      if (q && !c.name.toLowerCase().includes(q) && !c.email.toLowerCase().includes(q))
        return false;
      return true;
    });
  }, [query, status, spend, removed]);

  const pageCount = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const currentPage = Math.min(page, pageCount);
  const paged = filtered.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);

  const vipCount = customers.filter((c) => c.spent >= 10000).length;
  const activeCount = customers.filter((c) => c.status === "active").length;
  const totalSpent = customers.reduce((s, c) => s + c.spent, 0);

  const clearFilters = () => {
    setQuery("");
    setStatus("all");
    setSpend("all");
    setPage(1);
  };

  const onDelete = (id: string) => {
    setRemoved((prev) => new Set([...prev, id]));
    setBanner({ type: "success", text: "Customer removed." });
  };

  const stats = [
    {
      title: "Total customers",
      value: customers.length,
      icon: <Users className="h-4 w-4" />,
      variant: "primary" as const,
    },
    {
      title: "Active",
      value: activeCount,
      icon: <Check className="h-4 w-4" />,
      variant: "success" as const,
    },
    {
      title: "VIP customers",
      value: vipCount,
      icon: <Crown className="h-4 w-4" />,
      variant: "info" as const,
    },
    {
      title: "Lifetime value",
      value: formatCurrency(totalSpent),
      icon: <BadgeDollarSign className="h-4 w-4" />,
      variant: "warning" as const,
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader title="Customers" description="Manage your customer base and relationships." />

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
                  placeholder="Search customers..."
                />
              </div>
              <div className="flex flex-wrap items-center gap-2">
                <Select
                  aria-label="Filter by status"
                  value={status}
                  onChange={(e) => {
                    setStatus(e.target.value as typeof status);
                    setPage(1);
                  }}
                >
                  {statusOptions.map((o) => (
                    <option key={o.id} value={o.id}>
                      {o.label}
                    </option>
                  ))}
                </Select>
                <Select
                  aria-label="Filter by total spent"
                  value={spend}
                  onChange={(e) => {
                    setSpend(e.target.value as typeof spend);
                    setPage(1);
                  }}
                >
                  {spentFilters.map((o) => (
                    <option key={o.id} value={o.id}>
                      {o.label}
                    </option>
                  ))}
                </Select>
              </div>
            </div>
            {(query || status !== "all" || spend !== "all") && (
              <Button
                size="sm"
                variant="ghost"
                onClick={clearFilters}
                className="justify-start lg:justify-center"
              >
                <RefreshCcw className="h-4 w-4" />
                Reset
              </Button>
            )}
          </div>
        </CardContent>

        <CardContent className="p-0">
          {banner && (
            <div className="flex items-center justify-between gap-3 border-b border-border bg-success/5 px-5 py-3 text-sm text-success animate-fade-in">
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

          {paged.length === 0 ? (
            <EmptyState
              className="m-4"
              icon={<Users className="h-8 w-8" />}
              title="No customers found"
              description="Try adjusting your search or filters."
              action={{ label: "Clear filters", onClick: clearFilters }}
            />
          ) : (
            <div className="overflow-x-auto">
              <table className="data-table w-full text-sm">
                <thead>
                  <tr className="border-b border-border bg-muted/50">
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Customer
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Contact
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Orders
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Total Spent
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Last Order
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
                  {paged.map((customer) => {
                    const badge = badgeFor(customer);
                    return (
                      <tr
                        key={customer.id}
                        className="border-b border-border last:border-0 hover:bg-muted/50"
                      >
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-3">
                            <Avatar
                              name={customer.name}
                              size="sm"
                              className={cn("bg-gradient-to-br", gradient(customer.name))}
                            />
                            <div className="min-w-0">
                              <p className="truncate font-medium">{customer.name}</p>
                              <p className="text-xs text-muted-foreground">
                                Joined {customer.joined}
                              </p>
                            </div>
                          </div>
                        </td>
                        <td className="px-4 py-3">
                          <p className="flex items-center gap-1.5 text-muted-foreground">
                            <Mail className="h-3.5 w-3.5" />
                            <span className="truncate">{customer.email}</span>
                          </p>
                          <p className="text-xs text-muted-foreground">{customer.phone}</p>
                        </td>
                        <td className="px-4 py-3">{customer.orders}</td>
                        <td className="px-4 py-3 font-semibold">
                          {formatCurrency(customer.spent)}
                        </td>
                        <td className="px-4 py-3 text-muted-foreground">{customer.lastOrder}</td>
                        <td className="px-4 py-3">
                          <Badge variant={badge.variant}>{badge.label}</Badge>
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center justify-end gap-1">
                            <Link
                              href="/app/ecommerce/customers/details"
                              aria-label="View customer"
                              className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                              <Eye className="h-4 w-4" />
                            </Link>
                            <button
                              onClick={() => onDelete(customer.id)}
                              aria-label="Delete customer"
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
