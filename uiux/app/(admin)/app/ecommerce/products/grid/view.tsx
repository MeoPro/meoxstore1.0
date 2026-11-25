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
import {
  AlertTriangle,
  BadgeDollarSign,
  Boxes,
  Check,
  ChevronLeft,
  ChevronRight,
  Headphones,
  Heart,
  Keyboard,
  Laptop,
  LayoutGrid,
  Lightbulb,
  List,
  Package,
  Search,
  ShoppingCart,
  Star,
  Watch,
  X,
  Zap,
} from "lucide-react";
import Link from "next/link";
import { useMemo, useState } from "react";

const PAGE_SIZE = 8;

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

function stockMeta(product: (typeof products)[number]): {
  label: string;
  variant: "success" | "warning" | "destructive";
} {
  if (product.stock === 0) return { label: "Out of Stock", variant: "destructive" };
  if (product.stock < 50) return { label: "Low Stock", variant: "warning" };
  return { label: "In Stock", variant: "success" };
}

const rating = 4.6;

export default function ProductGridPage() {
  const [query, setQuery] = useState("");
  const [category, setCategory] = useState("All categories");
  const [view, setView] = useState<"grid" | "list">("grid");
  const [wishlist, setWishlist] = useState<Set<string>>(new Set(["2", "5"]));
  const [banner, setBanner] = useState<string | null>(null);
  const [page, setPage] = useState(1);
  const [cartCount, setCartCount] = useState(0);

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    return products.filter((p) => {
      if (category !== "All categories" && p.category !== category) return false;
      if (q && !p.name.toLowerCase().includes(q) && !p.sku.toLowerCase().includes(q)) return false;
      return true;
    });
  }, [query, category]);

  const pageCount = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const currentPage = Math.min(page, pageCount);
  const paged = filtered.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);

  const toggleWishlist = (id: string) => {
    setWishlist((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const addToCart = (name: string) => {
    setCartCount((c) => c + 1);
    setBanner(`Added ${name} to cart.`);
  };

  const stats = [
    {
      title: "Total products",
      value: products.length,
      icon: <Boxes className="h-4 w-4" />,
      variant: "primary" as const,
    },
    {
      title: "In stock",
      value: products.filter((p) => p.stock >= 50).length,
      icon: <Package className="h-4 w-4" />,
      variant: "success" as const,
    },
    {
      title: "Low stock",
      value: products.filter((p) => p.stock > 0 && p.stock < 50).length,
      icon: <AlertTriangle className="h-4 w-4" />,
      variant: "warning" as const,
    },
    {
      title: "Inventory value",
      value: formatCurrency(products.reduce((s, p) => s + p.price * p.stock, 0)),
      icon: <BadgeDollarSign className="h-4 w-4" />,
      variant: "info" as const,
    },
  ];

  const clearFilters = () => {
    setQuery("");
    setCategory("All categories");
    setPage(1);
  };

  const renderPrice = (p: (typeof products)[number]) => {
    const discount = p.comparePrice && p.comparePrice > p.price;
    return (
      <div className="flex items-baseline gap-2">
        <span className="text-base font-bold text-foreground">{formatCurrency(p.price)}</span>
        {discount && (
          <>
            <span className="text-xs text-muted-foreground line-through">
              {formatCurrency(p.comparePrice as number)}
            </span>
            <span className="text-xs font-semibold text-success">
              -{Math.round((1 - p.price / (p.comparePrice as number)) * 100)}%
            </span>
          </>
        )}
      </div>
    );
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Product Grid"
        description="Browse your storefront in a visual catalog view."
        actions={
          <Button variant="outline" onClick={() => setCartCount((c) => c * 0)}>
            <ShoppingCart className="h-4 w-4" />
            Cart ({cartCount})
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
          <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div className="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
              <div className="sm:w-64">
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
            </div>
            <div className="flex items-center gap-2">
              <div className="flex rounded-md border border-border p-0.5">
                <button
                  onClick={() => setView("grid")}
                  aria-label="Grid view"
                  className={cn(
                    "flex h-8 w-8 items-center justify-center rounded-md",
                    view === "grid"
                      ? "bg-muted text-foreground"
                      : "text-muted-foreground hover:text-foreground",
                  )}
                >
                  <LayoutGrid className="h-4 w-4" />
                </button>
                <button
                  onClick={() => setView("list")}
                  aria-label="List view"
                  className={cn(
                    "flex h-8 w-8 items-center justify-center rounded-md",
                    view === "list"
                      ? "bg-muted text-foreground"
                      : "text-muted-foreground hover:text-foreground",
                  )}
                >
                  <List className="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>

          {banner && (
            <div className="mt-4 flex items-center justify-between gap-3 rounded-lg border border-success/30 bg-success/5 px-4 py-3 text-sm text-success animate-fade-in">
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
        </CardContent>
      </Card>

      {paged.length === 0 ? (
        <EmptyState
          icon={<Package className="h-8 w-8" />}
          title="No products found"
          description="Try adjusting your search or category filter."
          action={{ label: "Clear filters", onClick: clearFilters }}
        />
      ) : view === "grid" ? (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {paged.map((product) => {
            const cat = categoryVisualFor(product.category);
            const stock = stockMeta(product);
            const inWishlist = wishlist.has(product.id);
            return (
              <Card
                key={product.id}
                className="overflow-hidden transition-shadow hover:shadow-dropdown"
              >
                <div
                  className={cn(
                    "relative flex h-32 items-center justify-center bg-gradient-to-br text-white",
                    cat.gradient,
                  )}
                >
                  <cat.icon className="h-10 w-10" />
                  <div className="absolute right-2 top-2 flex items-center gap-2">
                    <Badge variant={stock.variant}>{stock.label}</Badge>
                  </div>
                  <button
                    onClick={() => toggleWishlist(product.id)}
                    aria-label={inWishlist ? "Remove from wishlist" : "Add to wishlist"}
                    className="absolute bottom-2 right-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-muted-foreground shadow-card transition-colors hover:text-foreground"
                  >
                    <Heart className={cn("h-4 w-4", inWishlist && "fill-rose-500 text-rose-500")} />
                  </button>
                </div>
                <CardContent className="p-5">
                  <p className="text-xs text-muted-foreground">{product.category}</p>
                  <Link
                    href="/app/ecommerce/products/details"
                    className="mt-0.5 line-clamp-1 text-sm font-semibold transition-colors hover:text-primary"
                  >
                    {product.name}
                  </Link>
                  <div className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                    <Star className="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
                    <span className="font-medium text-foreground">{rating}</span>
                    <span>({Math.max(20, product.stock * 3 + 40)} reviews)</span>
                  </div>
                  <div className="mt-3 flex items-center justify-between">
                    {renderPrice(product)}
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => addToCart(product.name)}
                      aria-label="Add to cart"
                    >
                      <ShoppingCart className="h-3.5 w-3.5" />
                    </Button>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>
      ) : (
        <Card>
          <div className="divide-y divide-border">
            {paged.map((product) => {
              const cat = categoryVisualFor(product.category);
              const stock = stockMeta(product);
              const inWishlist = wishlist.has(product.id);
              return (
                <div
                  key={product.id}
                  className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center"
                >
                  <div
                    className={cn(
                      "flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br text-white",
                      cat.gradient,
                    )}
                  >
                    <cat.icon className="h-6 w-6" />
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2">
                      <Link
                        href="/app/ecommerce/products/details"
                        className="truncate text-sm font-semibold transition-colors hover:text-primary"
                      >
                        {product.name}
                      </Link>
                      <Badge variant="secondary">{product.category}</Badge>
                    </div>
                    <div className="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                      <span>{product.sku}</span>
                      <span className="flex items-center gap-1">
                        <Star className="h-3 w-3 fill-amber-400 text-amber-400" />
                        {rating}
                      </span>
                      <Badge variant={stock.variant}>{stock.label}</Badge>
                    </div>
                  </div>
                  {renderPrice(product)}
                  <div className="flex items-center gap-2">
                    <button
                      onClick={() => toggleWishlist(product.id)}
                      aria-label="Toggle wishlist"
                      className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    >
                      <Heart
                        className={cn("h-4 w-4", inWishlist && "fill-rose-500 text-rose-500")}
                      />
                    </button>
                    <Button size="sm" variant="outline" onClick={() => addToCart(product.name)}>
                      <ShoppingCart className="h-3.5 w-3.5" />
                      Add to Cart
                    </Button>
                  </div>
                </div>
              );
            })}
          </div>
        </Card>
      )}

      {pageCount > 1 && paged.length > 0 && (
        <Card>
          <div className="flex flex-col items-center justify-between gap-3 px-5 py-3 sm:flex-row">
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
        </Card>
      )}
    </div>
  );
}
