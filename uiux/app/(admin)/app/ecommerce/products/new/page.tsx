import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Add Product",
};

export default function NewProductPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Add Product" description="Create a new product listing." />

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>General Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <Input label="Product Name" placeholder="Enter product name" />
              <Textarea
                label="Description"
                className="min-h-[120px]"
                placeholder="Enter product description..."
              />
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Pricing</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-4 sm:grid-cols-2">
                <Input label="Price" placeholder="$0.00" />
                <Input
                  label="Compare at Price"
                  placeholder="$0.00"
                  hint="Optional original price"
                />
              </div>
              <div className="grid gap-4 sm:grid-cols-2">
                <Input label="Cost per Item" placeholder="$0.00" />
                <Input label="SKU" placeholder="Enter SKU" />
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Organization</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <Input label="Category" placeholder="Select category" />
              <Input label="Tags" placeholder="Add tags" hint="Separate with commas" />
              <Input label="Stock Quantity" placeholder="0" />
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Media</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="border-2 border-dashed border-border rounded-lg p-8 text-center">
                <p className="text-sm text-muted-foreground">Drop images here or click to upload</p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <div className="flex justify-end gap-2">
        <Button variant="outline">Save as Draft</Button>
        <Button>Publish Product</Button>
      </div>
    </div>
  );
}
