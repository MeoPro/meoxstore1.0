import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GradientCard } from "@/components/ui/gradient-card";
import { ShoppingBag, TrendingUp, Wallet } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Customer Details",
};

const recentOrders = [
  { id: "ORD-5521", date: "Aug 15, 2026", total: "$245.00", status: "delivered" },
  { id: "ORD-5412", date: "Jul 28, 2026", total: "$89.99", status: "delivered" },
  { id: "ORD-5380", date: "Jul 15, 2026", total: "$156.00", status: "delivered" },
];

export default function CustomerDetailsPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Customer Details" description="View customer information and history." />

      <div className="grid gap-6 lg:grid-cols-3">
        <Card>
          <CardContent className="p-5 text-center">
            <Avatar name="John Smith" size="lg" />
            <h2 className="mt-3 text-lg font-semibold">John Smith</h2>
            <p className="text-sm text-muted-foreground">john@example.com</p>
            <p className="text-sm text-muted-foreground">+1 555-0101</p>
            <Badge variant="info" className="mt-2">
              VIP Customer
            </Badge>
            <div className="mt-4 pt-4 border-t border-border text-left space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-muted-foreground">Member since</span>
                <span>Jan 2024</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Location</span>
                <span>New York, NY</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="lg:col-span-2 space-y-6">
          <div className="grid gap-4 sm:grid-cols-3">
            <GradientCard
              title="Total Orders"
              value="23"
              change={8}
              variant="primary"
              icon={<ShoppingBag className="h-4 w-4" />}
            />
            <GradientCard
              title="Total Spent"
              value="$5,890"
              change={12.5}
              variant="success"
              icon={<Wallet className="h-4 w-4" />}
            />
            <GradientCard
              title="Avg. Order"
              value="$256"
              change={4.2}
              variant="sunset"
              icon={<TrendingUp className="h-4 w-4" />}
            />
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Recent Orders</CardTitle>
            </CardHeader>
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <table className="data-table w-full text-sm">
                  <thead>
                    <tr className="border-b border-border bg-muted/50">
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Order
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Date
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Total
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                        Status
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {recentOrders.map((order) => (
                      <tr
                        key={order.id}
                        className="border-b border-border last:border-0 hover:bg-muted/50"
                      >
                        <td className="px-4 py-3 font-medium">{order.id}</td>
                        <td className="px-4 py-3 text-muted-foreground">{order.date}</td>
                        <td className="px-4 py-3 font-medium">{order.total}</td>
                        <td className="px-4 py-3">
                          <Badge variant="success">{order.status}</Badge>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
