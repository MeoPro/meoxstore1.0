import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Order Details",
};

const orderItems = [
  { name: "Wireless Headphones Pro", qty: 1, price: "$49.99" },
  { name: "USB-C Hub 7-in-1", qty: 2, price: "$79.98" },
  { name: "Laptop Stand Ergonomic", qty: 1, price: "$39.99" },
];

export default function OrderDetailPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Order #ORD-5521" description="Order details and tracking." />

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Items</CardTitle>
                <Badge variant="success">Delivered</Badge>
              </div>
            </CardHeader>
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <table className="data-table w-full text-sm">
                  <thead>
                    <tr className="border-b border-border bg-muted/50">
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Product
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">Qty</th>
                      <th className="px-4 py-3 text-right font-medium text-muted-foreground">
                        Price
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {orderItems.map((item) => (
                      <tr
                        key={item.name}
                        className="border-b border-border last:border-0 hover:bg-muted/50"
                      >
                        <td className="px-4 py-3 font-medium">{item.name}</td>
                        <td className="px-4 py-3">{item.qty}</td>
                        <td className="px-4 py-3 text-right">{item.price}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              <div className="border-t border-border px-4 py-3 flex justify-between font-medium">
                <span>Total</span>
                <span>$169.96</span>
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Customer</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2 text-sm">
              <p className="font-medium">John Smith</p>
              <p className="text-muted-foreground">john@example.com</p>
              <p className="text-muted-foreground">+1 555-0101</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Shipping Address</CardTitle>
            </CardHeader>
            <CardContent className="text-sm text-muted-foreground">
              <p>123 Main Street</p>
              <p>Apt 4B</p>
              <p>New York, NY 10001</p>
              <p>United States</p>
            </CardContent>
          </Card>

          <div className="flex gap-2">
            <Button variant="outline" className="flex-1">
              Refund
            </Button>
            <Button className="flex-1">Print Invoice</Button>
          </div>
        </div>
      </div>
    </div>
  );
}
