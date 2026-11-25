"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { CreditCard } from "@/components/ui/credit-card";
import { GradientCard } from "@/components/ui/gradient-card";
import { categories, customers, orders, products } from "@/data/ecommerce";
import {
  CreditCard as CreditCardIcon,
  DollarSign,
  Package,
  Plus,
  ShoppingBag,
  Users,
} from "lucide-react";
import {
  Area,
  AreaChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { axisTick, chartColors, tooltipProps } from "@/lib/chart-theme";

const monthlySalesData = [
  { month: "Jan", sales: 12400 },
  { month: "Feb", sales: 14800 },
  { month: "Mar", sales: 13200 },
  { month: "Apr", sales: 17600 },
  { month: "May", sales: 16100 },
  { month: "Jun", sales: 19800 },
  { month: "Jul", sales: 21400 },
  { month: "Aug", sales: 18900 },
  { month: "Sep", sales: 22600 },
  { month: "Oct", sales: 24100 },
  { month: "Nov", sales: 28400 },
  { month: "Dec", sales: 32200 },
];

const orderStatusDistribution = [
  { status: "Delivered", count: 142, variant: "success" as const },
  { status: "Shipped", count: 38, variant: "info" as const },
  { status: "Processing", count: 24, variant: "warning" as const },
  { status: "Pending", count: 11, variant: "default" as const },
  { status: "Cancelled", count: 5, variant: "danger" as const },
];

const topProducts = products
  .filter((p) => p.status === "active")
  .sort((a, b) => b.price * b.stock - a.price * a.stock)
  .slice(0, 6);

const topCustomers = [...customers].sort((a, b) => b.spent - a.spent).slice(0, 5);

const recentOrders = [...orders].slice(0, 5);

const orderStatusVariant: Record<string, "success" | "info" | "warning" | "default" | "danger"> = {
  delivered: "success",
  shipped: "info",
  processing: "warning",
  pending: "default",
  cancelled: "danger",
};

const salesByCategory = categories.map((c) => ({
  name: c.name,
  revenue: Number(c.revenue.replace(/[$,]/g, "")),
}));
const maxCategoryRevenue = Math.max(...salesByCategory.map((c) => c.revenue));

export default function EcommerceDashboard() {
  const totalSales = monthlySalesData.reduce((sum, m) => sum + m.sales, 0);

  return (
    <div className="space-y-6">
      <PageHeader
        title="E-commerce Dashboard"
        description="Sales performance, products, and customer insights."
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <GradientCard
          title="Total Sales"
          value={`$${(totalSales / 1000).toFixed(0)}K`}
          change={22.4}
          variant="primary"
          icon={<DollarSign className="h-4 w-4" />}
        />
        <GradientCard
          title="Products"
          value={products.length}
          change={8}
          variant="success"
          icon={<Package className="h-4 w-4" />}
        />
        <GradientCard
          title="Customers"
          value={customers.length}
          change={12}
          variant="ocean"
          icon={<Users className="h-4 w-4" />}
        />
        <GradientCard
          title="Avg Order"
          value="$186"
          change={5.2}
          variant="sunset"
          icon={<ShoppingBag className="h-4 w-4" />}
        />
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <CreditCard
          variant="primary"
          balance="$24,680.50"
          cardNumber="•••• •••• •••• 4242"
          holderName="Alex Morgan"
          expiry="09/29"
          brand="visa"
        />
        <CreditCard
          variant="ocean"
          balance="$8,120.00"
          cardNumber="•••• •••• •••• 5555"
          holderName="Alex Morgan"
          expiry="03/28"
          brand="mastercard"
        />
        <Card>
          <CardHeader>
            <CardTitle>Payment Methods</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <div className="flex items-center gap-3 rounded-lg border border-border p-3">
              <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10">
                <CreditCardIcon className="h-4 w-4 text-primary" />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium">Visa •••• 4242</p>
                <p className="text-2xs text-muted-foreground">Expires 09/29</p>
              </div>
              <Badge variant="success">Default</Badge>
            </div>
            <div className="flex items-center gap-3 rounded-lg border border-border p-3">
              <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-success/10">
                <CreditCardIcon className="h-4 w-4 text-success" />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium">Mastercard •••• 5555</p>
                <p className="text-2xs text-muted-foreground">Expires 03/28</p>
              </div>
              <Badge variant="secondary">Active</Badge>
            </div>
            <div className="flex items-center gap-3 rounded-lg border border-border p-3">
              <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-info/10">
                <CreditCardIcon className="h-4 w-4 text-info" />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium">PayPal</p>
                <p className="text-2xs text-muted-foreground">alex@dropx.io</p>
              </div>
              <Badge variant="info">Connected</Badge>
            </div>
            <button className="flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-border p-3 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">
              <Plus className="h-4 w-4" />
              Add payment method
            </button>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle>Monthly Sales</CardTitle>
            <div className="flex items-center gap-2 text-xs text-muted-foreground">
              <span className="flex items-center gap-1">
                <span className="h-2 w-2 rounded-full bg-primary" /> Sales
              </span>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="h-[280px]">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={monthlySalesData}>
                <defs>
                  <linearGradient id="gSales" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor={chartColors.primary} stopOpacity={0.2} />
                    <stop offset="95%" stopColor={chartColors.primary} stopOpacity={0} />
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                <XAxis dataKey="month" tick={axisTick} axisLine={false} tickLine={false} />
                <YAxis
                  tick={axisTick}
                  tickFormatter={(v) => `$${v / 1000}k`}
                  axisLine={false}
                  tickLine={false}
                />
                <Tooltip {...tooltipProps} />
                <Area
                  type="monotone"
                  dataKey="sales"
                  stroke={chartColors.primary}
                  fill="url(#gSales)"
                  strokeWidth={2}
                />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </CardContent>
      </Card>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Top Selling Products</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              <div className="grid grid-cols-4 text-xs font-medium text-muted-foreground border-b border-border pb-2">
                <span className="col-span-2">Product</span>
                <span className="text-right">Price</span>
                <span className="text-right">Stock</span>
              </div>
              {topProducts.map((product) => (
                <div key={product.id} className="grid grid-cols-4 items-center text-sm py-1.5">
                  <span className="col-span-2 font-medium truncate">{product.name}</span>
                  <span className="text-right text-muted-foreground">${product.price}</span>
                  <span className="text-right font-medium">{product.stock}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Order Status Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {orderStatusDistribution.map((item) => (
                <div key={item.status} className="flex items-center justify-between py-1.5">
                  <div className="flex items-center gap-3">
                    <Badge variant={item.variant}>{item.status}</Badge>
                  </div>
                  <span className="text-sm font-semibold">{item.count} orders</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
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
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Order</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">
                    Customer
                  </th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Date</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Payment</th>
                  <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                  <th className="px-4 py-3 text-right font-medium text-muted-foreground">Total</th>
                </tr>
              </thead>
              <tbody>
                {recentOrders.map((order) => (
                  <tr
                    key={order.id}
                    className="border-b border-border last:border-0 hover:bg-muted/50"
                  >
                    <td className="px-4 py-3 font-medium">{order.orderNo}</td>
                    <td className="px-4 py-3">
                      <p className="font-medium">{order.customer}</p>
                      <p className="text-2xs text-muted-foreground">{order.email}</p>
                    </td>
                    <td className="px-4 py-3 text-muted-foreground">{order.date}</td>
                    <td className="px-4 py-3 text-muted-foreground">{order.method}</td>
                    <td className="px-4 py-3">
                      <Badge variant={orderStatusVariant[order.status]}>{order.status}</Badge>
                    </td>
                    <td className="px-4 py-3 text-right font-semibold">
                      ${order.total.toLocaleString()}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Sales by Category</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {salesByCategory.map((cat) => (
                <div key={cat.name} className="space-y-1">
                  <div className="flex items-center justify-between text-sm">
                    <span className="font-medium">{cat.name}</span>
                    <span className="text-muted-foreground">
                      ${(cat.revenue / 1000).toFixed(0)}K
                    </span>
                  </div>
                  <div className="h-2 rounded-full bg-muted">
                    <div
                      className="h-full rounded-full bg-gradient-to-r from-primary to-purple-500"
                      style={{ width: `${(cat.revenue / maxCategoryRevenue) * 100}%` }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Top Customers</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {topCustomers.map((customer, i) => (
                <div key={customer.id} className="flex items-center gap-3 py-1">
                  <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-muted text-2xs font-bold text-muted-foreground">
                    {i + 1}
                  </span>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium truncate">{customer.name}</p>
                    <p className="text-2xs text-muted-foreground">
                      {customer.orders} orders · {customer.email}
                    </p>
                  </div>
                  <span className="text-sm font-semibold">${customer.spent.toLocaleString()}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
