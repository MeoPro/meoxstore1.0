import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Heart, ShoppingCart } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Product Details",
};

export default function ProductDetailsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Product Details" description="View product information." />

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardContent className="p-0">
            <div className="h-80 bg-muted flex items-center justify-center rounded-lg">
              <span className="text-muted-foreground">Product Image</span>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-5 space-y-4">
            <div>
              <Badge variant="success">In Stock</Badge>
              <h1 className="text-2xl font-bold mt-2">Wireless Headphones Pro</h1>
              <p className="text-sm text-muted-foreground mt-1">SKU: PRD-001 · Electronics</p>
            </div>

            <div className="flex items-center gap-1">
              <span>⭐⭐⭐⭐⭐</span>
              <span className="text-sm text-muted-foreground">(124 reviews)</span>
            </div>

            <div className="flex items-baseline gap-3">
              <span className="text-3xl font-bold">$49.99</span>
              <span className="text-lg text-muted-foreground line-through">$69.99</span>
              <Badge variant="destructive">-29%</Badge>
            </div>

            <p className="text-sm text-muted-foreground">
              Premium wireless headphones with active noise cancellation, 40-hour battery life, and
              hi-res audio support. Comfortable over-ear design for all-day wear.
            </p>

            <div className="space-y-2 pt-4 border-t border-border">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Brand</span>
                <span className="font-medium">AudioTech</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Color</span>
                <span className="font-medium">Matte Black</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Battery</span>
                <span className="font-medium">40 hours</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Weight</span>
                <span className="font-medium">250g</span>
              </div>
            </div>

            <div className="flex gap-3 pt-4">
              <Button className="flex-1">
                <ShoppingCart className="h-4 w-4" />
                Add to Cart
              </Button>
              <Button variant="outline">
                <Heart className="h-4 w-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
