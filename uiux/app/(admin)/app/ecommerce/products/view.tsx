"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { EmptyState } from "@/components/ui/empty-state";
import { GradientCard } from "@/components/ui/gradient-card";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { products } from "@/data";
import { cn, formatCurrency } from "@/lib/utils";
import type { Product } from "@/types/modules";
import {
  AlertTriangle,
  BadgeDollarSign,
  Boxes,
  Check,
  ChevronLeft,
  ChevronRight,
  Eye,
  Headphones,
  Keyboard,
  Laptop,
  Lightbulb,
  Package,
  Pencil,
  Plus,
  RefreshCcw,
  Search,
  SlidersHorizontal,
  Trash2,
  Watch,
  X,
  Zap,
} from "lucide-react";
import Link from "next/link";
import { useMemo, useState } from "react";

type BadgeVariant = "success" | "warning" | "destructive" | "default" | "secondary" | "outline";

const PAGE_SIZE = 6;

const statusFilterOptions = [
  { id: "all", label: "All status" },
  { id: "active", label: "Active" },
  { id: "draft", label: "Draft" },
  { id: "archived", label: "Archived" },
  { id: "low", label: "Low stock" },
  { id: "out", label: "Out of stock" },
] as const;

type StatusFilterId = (typeof statusFilterOptions)[number]["id"];

const categoryFilters = ["All categories", ...Array.from(new Set(products.map((p) => p.category)))];

function categoryVisualFor(category: string): { icon: typeof Package; gradient: string } {
  switch (category) {
    case "Audio":
      return { icon: Headphones, gradient: "from-violet-500 to-purple-600" };
    case "Wearables":
      return { icon: Watch, gradient: "from-sky-500 to-blue-600" };
    case "Peripherals":
      return { icon: Keyboard, gradient: "from-emerald-500 to-teal-600" };
    case "Lighting":
      return { icon: Lightbulb, gradient: "from-yellow-400 to-amber-500" };
    case "Power":
      return { icon: Zap, gradient: "from-rose-500 to-pink-600" };
    default:
      return { icon: Laptop, gradient: "from-amber-500 to-orange-600" };
  }
}

function stockBadge(product: Product): { label: string; variant: BadgeVariant } {
  if (product.stock === 0) return { label: "Out of Stock", variant: "destructive" };
  if (product.stock < 50) return { label: "Low Stock", variant: "warning" };
  return { label: product.status, variant: product.status === "active" ? "success" : "default" };
}

export default function ProductsPage() {
  const [query, setQuery] = useState("");
  const [category, setCategory] = useState("All categories");
  const [statusFilter, setStatusFilter] = useState<StatusFilterId>("all");
  const [advancedOpen, setAdvancedOpen] = useState(false);
  const [minPrice, setMinPrice] = useState("");
  const [maxPrice, setMaxPrice] = useState("");
  const [page, setPage] = useState(1);
  const [selected, setSelected] = useState<Set<string>>(new Set());
  const [removed, setRemoved] = useState<Set<string>>(new Set());
  const [banner, setBanner] = useState<{ type: "success" | "error"; text: string } | null>(null);

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    const min = minPrice === "" ? -Infinity : Number(minPrice);
    const max = maxPrice === "" ? Infinity : Number(maxPrice);
    return products.filter((p) => {
      if (removed.has(p.id)) return false;
      if (category !== "All categories" && p.category !== category) return false;
      if (q && !p.name.toLowerCase().includes(q) && !p.sku.toLowerCase().includes(q)) return false;
      if (statusFilter === "low" && !(p.stock > 0 && p.stock < 50)) return false;
      if (statusFilter === "out" && p.stock !== 0) return false;
      if (statusFilter === "active" || statusFilter === "draft" || statusFilter === "archived") {
        if (p.status !== statusFilter) return false;
      }
      if (p.price < min || p.price > max) return false;
      return true;
    });
  }, [query, category, statusFilter, minPrice, maxPrice, removed]);

  const pageCount = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const currentPage = Math.min(page, pageCount);
  const paged = filtered.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);

  const inStockCount = filtered.filter((p) => p.stock >= 50).length;
  const lowStockCount = filtered.filter((p) => p.stock > 0 && p.stock < 50).length;
  const totalValue = filtered.reduce((sum, p) => sum + p.price * p.stock, 0);

  const clearFilters = () => {
    setQuery("");
    setCategory("All categories");
    setStatusFilter("all");
    setMinPrice("");
    setMaxPrice("");
    setAdvancedOpen(false);
    setPage(1);
  };

  const onSelectAll = (checked: boolean) => {
    setSelected(checked ? new Set(paged.map((p) => p.id)) : new Set());
  };

  const onToggleOne = (id: string) => {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const onBatchDelete = () => {
    const ids = [...selected];
    if (ids.length === 0) return;
    setRemoved((prev) => new Set([...prev, ...ids]));
    setBanner({
      type: "success",
      text: `${ids.length} product${ids.length > 1 ? "s" : ""} deleted from the catalog.`,
    });
    setSelected(new Set());
  };

  const onDeleteOne = (id: string) => {
    setRemoved((prev) => new Set([...prev, id]));
    setBanner({ type: "success", text: "Product removed from the catalog." });
  };

  const stats = [
    {
      title: "Total products",
      value: filtered.length,
      icon: <Boxes className="h-4 w-4" />,
      variant: "primary" as const,
    },
    {
      title: "In stock",
      value: inStockCount,
      icon: <Package className="h-4 w-4" />,
      variant: "success" as const,
    },
    {
      title: "Low stock",
      value: lowStockCount,
      icon: <AlertTriangle className="h-4 w-4" />,
      variant: "warning" as const,
    },
    {
      title: "Inventory value",
      value: formatCurrency(totalValue),
      icon: <BadgeDollarSign className="h-4 w-4" />,
      variant: "info" as const,
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Products"
        description="Manage your product catalog."
        actions={
          <Link href="/app/ecommerce/products/new">
            <Button>
              <Plus className="h-4 w-4" />
              Add Product
            </Button>
          </Link>
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
            <div className="flex flex-1 flex-col gap-3 md:flex-row md:items-center">
              <div className="md:w-64">
                <Input
                  icon={<Search className="h-4 w-4" />}
                  value={query}
                  onChange={(e) => {
                    setQuery(e.target.value);
                    setPage(1);
                  }}
                  placeholder="Search products..."
                />
              </div>
              <div className="flex flex-wrap items-center gap-2">
                <Select
                  aria-label="Filter by category"
                  value={category}
                  onChange={(e) => {
                    setCategory(e.target.value);
                    setPage(1);
                  }}
                >
                  {categoryFilters.map((c) => (
                    <option key={c} value={c}>
                      {c}
                    </option>
                  ))}
                </Select>
                <Select
                  aria-label="Filter by status"
                  value={statusFilter}
                  onChange={(e) => {
                    setStatusFilter(e.target.value as StatusFilterId);
                    setPage(1);
                  }}
                >
                  {statusFilterOptions.map((o) => (
                    <option key={o.id} value={o.id}>
                      {o.label}
                    </option>
                  ))}
                </Select>
                <Button
                  size="sm"
                  variant={advancedOpen ? "default" : "outline"}
                  onClick={() => setAdvancedOpen((v) => !v)}
                >
                  <SlidersHorizontal className="h-4 w-4" />
                  Advanced
                </Button>
              </div>
            </div>
            {(query || category !== "All categories" || statusFilter !== "all" || advancedOpen) && (
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

          {advancedOpen && (
            <div className="mt-4 flex flex-wrap items-end gap-4 rounded-lg border border-border bg-muted/30 p-4 animate-fade-in">
              <div className="w-full sm:w-44">
                <Input
                  label="Min price"
                  type="number"
                  value={minPrice}
                  onChange={(e) => setMinPrice(e.target.value)}
                  placeholder="0"
                />
              </div>
              <div className="w-full sm:w-44">
                <Input
                  label="Max price"
                  type="number"
                  value={maxPrice}
                  onChange={(e) => setMaxPrice(e.target.value)}
                  placeholder="500"
                />
              </div>
              <p className="pb-2 text-xs text-muted-foreground">
                Filter products by their selling price range.
              </p>
            </div>
          )}

          {banner && (
            <div
              className={cn(
                "mt-4 flex items-center justify-between gap-3 rounded-lg border px-4 py-3 text-sm animate-fade-in",
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
        </CardContent>

        <CardContent className="p-0">
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
              icon={<Package className="h-8 w-8" />}
              title="No products found"
              description="Try adjusting your search, filters, or price range."
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
                        onChange={(e) => onSelectAll(e.target.checked)}
                        aria-label="Select all"
                      />
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Product
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Category
                    </th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Price</th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Stock</th>
                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                      Status
                    </th>
                    <th className="py-3 pr-4 text-right font-medium text-muted-foreground">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {paged.map((product) => {
                    const badge = stockBadge(product);
                    const cat = categoryVisualFor(product.category);
                    const isSelected = selected.has(product.id);
                    return (
                      <tr
                        key={product.id}
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
                            onChange={() => onToggleOne(product.id)}
                            aria-label={`Select ${product.name}`}
                          />
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-3">
                            <div
                              className={cn(
                                "flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br text-white",
                                cat.gradient,
                              )}
                            >
                              <cat.icon className="h-5 w-5" />
                            </div>
                            <div className="min-w-0">
                              <Link
                                href="/app/ecommerce/products/details"
                                className="block truncate font-medium transition-colors hover:text-primary"
                              >
                                {product.name}
                              </Link>
                              <p className="text-xs text-muted-foreground">{product.sku}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-4 py-3">
                          <Badge variant="secondary">{product.category}</Badge>
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex flex-col">
                            <span className="font-medium">{formatCurrency(product.price)}</span>
                            {product.comparePrice && product.comparePrice > product.price && (
                              <span className="text-xs text-muted-foreground">
                                <span className="line-through">
                                  {formatCurrency(product.comparePrice)}
                                </span>{" "}
                                <span className="font-medium text-success">
                                  -{Math.round((1 - product.price / product.comparePrice) * 100)}%
                                </span>
                              </span>
                            )}
                          </div>
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-2">
                            <span
                              className={cn(
                                "font-medium",
                                product.stock === 0
                                  ? "text-destructive"
                                  : product.stock < 50
                                    ? "text-warning"
                                    : "text-foreground",
                              )}
                            >
                              {product.stock}
                            </span>
                            <div className="h-1.5 w-16 overflow-hidden rounded-full bg-muted">
                              <div
                                className={cn(
                                  "h-full rounded-full",
                                  product.stock === 0
                                    ? "bg-destructive"
                                    : product.stock < 50
                                      ? "bg-warning"
                                      : "bg-success",
                                )}
                                style={{ width: `${Math.min(100, Math.max(5, product.stock))}%` }}
                              />
                            </div>
                          </div>
                        </td>
                        <td className="px-4 py-3">
                          <Badge variant={badge.variant}>{badge.label}</Badge>
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center justify-end gap-1">
                            <Link
                              href="/app/ecommerce/products/details"
                              aria-label="View"
                              className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                              <Eye className="h-4 w-4" />
                            </Link>
                            <Link
                              href="/app/ecommerce/products/new"
                              aria-label="Edit"
                              className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                              <Pencil className="h-4 w-4" />
                            </Link>
                            <button
                              onClick={() => onDeleteOne(product.id)}
                              aria-label="Delete"
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
