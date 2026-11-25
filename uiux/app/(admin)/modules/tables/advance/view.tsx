"use client";

import { PageHeader } from "@/components/layout/page-header";
import { AdvancedTable } from "@/components/ui/advanced-table";
import { Badge } from "@/components/ui/badge";

const statusVariants = {
  completed: "success",
  processing: "warning",
  cancelled: "destructive",
} as const;

type Order = {
  id: string;
  customer: string;
  amount: string;
  date: string;
  status: keyof typeof statusVariants;
};

const orders: Order[] = [
  {
    id: "ORD-001",
    customer: "Alice Martin",
    amount: "$120.00",
    date: "2024-01-15",
    status: "completed",
  },
  {
    id: "ORD-002",
    customer: "Bob Smith",
    amount: "$85.50",
    date: "2024-01-14",
    status: "processing",
  },
  {
    id: "ORD-003",
    customer: "Carol White",
    amount: "$240.00",
    date: "2024-01-13",
    status: "completed",
  },
  {
    id: "ORD-004",
    customer: "David Lee",
    amount: "$55.00",
    date: "2024-01-12",
    status: "cancelled",
  },
  {
    id: "ORD-005",
    customer: "Eva Green",
    amount: "$180.75",
    date: "2024-01-11",
    status: "completed",
  },
  {
    id: "ORD-006",
    customer: "Frank Miller",
    amount: "$310.20",
    date: "2024-01-10",
    status: "processing",
  },
  {
    id: "ORD-007",
    customer: "Grace King",
    amount: "$92.40",
    date: "2024-01-09",
    status: "completed",
  },
  {
    id: "ORD-008",
    customer: "Henry Ford",
    amount: "$460.00",
    date: "2024-01-08",
    status: "cancelled",
  },
  {
    id: "ORD-009",
    customer: "Ivy Jones",
    amount: "$135.90",
    date: "2024-01-07",
    status: "processing",
  },
  {
    id: "ORD-010",
    customer: "Jack Brown",
    amount: "$78.25",
    date: "2024-01-06",
    status: "completed",
  },
  {
    id: "ORD-011",
    customer: "Kate Wilson",
    amount: "$515.60",
    date: "2024-01-05",
    status: "completed",
  },
  {
    id: "ORD-012",
    customer: "Leo Garcia",
    amount: "$46.10",
    date: "2024-01-04",
    status: "cancelled",
  },
  {
    id: "ORD-013",
    customer: "Mia Davis",
    amount: "$389.30",
    date: "2024-01-03",
    status: "processing",
  },
  {
    id: "ORD-014",
    customer: "Noah Lewis",
    amount: "$154.85",
    date: "2024-01-02",
    status: "completed",
  },
  {
    id: "ORD-015",
    customer: "Olivia Hall",
    amount: "$99.00",
    date: "2024-01-01",
    status: "cancelled",
  },
];

export default function AdvancedTablesPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Advanced Tables"
        description="Tables with sorting, filtering, selection and pagination."
      />

      <AdvancedTable<Order>
        title="Orders"
        description="Search, sort and select rows to explore table features."
        columns={[
          { key: "id", header: "Order ID", sortable: true },
          { key: "customer", header: "Customer", sortable: true },
          { key: "amount", header: "Amount", sortable: true },
          { key: "date", header: "Date", sortable: true },
          {
            key: "status",
            header: "Status",
            cell: (row) => <Badge variant={statusVariants[row.status]}>{row.status}</Badge>,
          },
        ]}
        data={orders}
        pageSize={5}
        searchable
        selectable
        showPagination
        searchPlaceholder="Search orders..."
      />
    </div>
  );
}
