// ============================================
// Module-specific Types
// Mirrors the shapes defined in data/
// ============================================

// --- ECOMMERCE ---

export type ProductStatus = "active" | "draft" | "archived";

export interface Product {
  id: string;
  name: string;
  sku: string;
  price: number;
  comparePrice?: number;
  stock: number;
  category: string;
  status: ProductStatus;
  image: string;
}

export type OrderStatus = "pending" | "processing" | "shipped" | "delivered" | "cancelled";

export type PaymentStatus = "paid" | "pending" | "refunded";

export interface Order {
  id: string;
  orderNo: string;
  customer: string;
  email: string;
  items: number;
  subtotal: number;
  shipping: number;
  tax: number;
  total: number;
  status: OrderStatus;
  payment: PaymentStatus;
  method: string;
  date: string;
  address: string;
}

export interface Customer {
  id: string;
  name: string;
  email: string;
  phone: string;
  orders: number;
  spent: number;
  status: "active" | "inactive";
  joined: string;
  lastOrder: string;
}

export interface Category {
  id: string;
  name: string;
  slug: string;
  products: number;
  revenue: string;
}

export interface CartItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
  image: string;
}

export interface Review {
  id: string;
  customer: string;
  product: string;
  rating: number;
  comment: string;
  date: string;
}

// --- CRM ---

export type ContactStatus = "lead" | "prospect" | "customer" | "churned";

export interface Contact {
  id: string;
  name: string;
  email: string;
  phone: string;
  company: string;
  position: string;
  status: ContactStatus;
  source: string;
  lastContacted: string;
}

export type DealStage =
  "qualification" | "needs-analysis" | "proposal" | "negotiation" | "closed-won" | "closed-lost";

export interface Deal {
  id: string;
  title: string;
  company: string;
  contact: string;
  value: number;
  stage: DealStage;
  probability: number;
  closeDate: string;
  assignedTo: string;
}

export interface PipelineStage {
  name: string;
  deals: string[];
}

export type ActivityType = "call" | "email" | "meeting" | "task" | "note";

export interface Activity {
  id: string;
  type: ActivityType;
  title: string;
  contact: string;
  date: string;
  done: boolean;
}

export interface StatChange {
  value: number;
  change: number;
}

export interface CrmStats {
  totalLeads: StatChange;
  activeDeals: StatChange;
  pipelineValue: StatChange;
  winRate: StatChange;
  avgDealSize: StatChange;
  conversionRate: StatChange;
}

// --- DASHBOARD ---

export interface RevenuePoint {
  month: string;
  revenue: number;
  profit: number;
}

export interface WeeklyOrder {
  day: string;
  orders: number;
}

export interface TrafficSource {
  name: string;
  value: number;
  color: string;
}

export interface RecentOrder {
  id: string;
  orderNo: string;
  customer: string;
  email: string;
  amount: number;
  status: OrderStatus;
  date: string;
}

export interface TopProduct {
  name: string;
  category: string;
  revenue: string;
  sold: number;
}

export type DashboardActivityType =
  "order" | "customer" | "payment" | "shipping" | "view" | "report";

export interface DashboardActivity {
  type: DashboardActivityType;
  text: string;
  time: string;
}

export type GradientVariant =
  "primary" | "success" | "info" | "warning" | "ocean" | "sunset" | "gradient";

export interface Target {
  label: string;
  value: number;
  variant: GradientVariant;
}

export interface Stat {
  title: string;
  value: string;
  change: number;
  variant: GradientVariant;
}

export interface DashboardStats {
  totalRevenue: Stat;
  totalOrders: Stat;
  newCustomers: Stat;
  conversion: Stat;
}

// --- FINANCE ---

export type TransactionType = "income" | "expense";

export interface Transaction {
  id: string;
  type: TransactionType;
  description: string;
  category: string;
  amount: number;
  date: string;
  status: "completed" | "pending" | "failed";
  reference: string;
}

export type InvoiceStatus = "draft" | "sent" | "paid" | "overdue";

export interface Invoice {
  id: string;
  customer: string;
  email: string;
  amount: number;
  issued: string;
  due: string;
  status: InvoiceStatus;
  paidDate: string | null;
}

export type AccountType = "checking" | "savings" | "credit" | "investment";

export interface Account {
  id: string;
  name: string;
  type: AccountType;
  institution: string;
  balance: number;
  currency: string;
}

export interface Budget {
  id: string;
  name: string;
  category: string;
  allocated: number;
  spent: number;
}

export interface FinanceStats {
  totalBalance: number;
  monthlyIncome: number;
  monthlyExpenses: number;
  netProfit: number;
  incomeChange: number;
  expenseChange: number;
}

export interface MonthlyRevenue {
  month: string;
  income: number;
  expenses: number;
}

// --- LMS ---

export type CourseStatus = "draft" | "published" | "archived";
export type CourseLevel = "beginner" | "intermediate" | "advanced";

export interface Course {
  id: string;
  title: string;
  instructor: string;
  students: number;
  lessons: number;
  hours: number;
  rating: number;
  price: number;
  status: CourseStatus;
  level: CourseLevel;
  category: string;
}

export type EnrollmentStatus = "active" | "completed" | "dropped";

export interface Enrollment {
  id: string;
  student: string;
  email: string;
  course: string;
  progress: number;
  status: EnrollmentStatus;
  enrolled: string;
  lastAccess: string;
}

export interface Instructor {
  id: string;
  name: string;
  email: string;
  courses: number;
  students: number;
  rating: number;
  revenue: string;
  expertise: string[];
}

export interface Quiz {
  id: string;
  title: string;
  course: string;
  questions: number;
  passScore: number;
  attempts: number;
  avgScore: number;
  passRate: number;
}

export interface LmsStats {
  totalStudents: number;
  totalCourses: number;
  completionRate: number;
  totalRevenue: number;
  avgRating: number;
  activeEnrollments: number;
}
