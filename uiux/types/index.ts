// ============================================
// Global Types for DropX Admin
// ============================================

import type { LucideIcon } from "lucide-react";

// Navigation
export interface NavItem {
  title: string;
  href: string;
  icon?: LucideIcon;
  badge?: string | number;
  disabled?: boolean;
  children?: NavItem[];
}

export interface NavGroup {
  label: string;
  items: NavItem[];
}

// Breadcrumb
export interface BreadcrumbItem {
  label: string;
  href?: string;
}

// Table / Data
export interface PaginationMeta {
  page: number;
  pageSize: number;
  total: number;
  totalPages: number;
}

export interface SortConfig {
  key: string;
  direction: "asc" | "desc";
}

export interface FilterConfig {
  key: string;
  value: string | number | boolean;
  operator?: "eq" | "neq" | "gt" | "lt" | "gte" | "lte" | "contains";
}

// API Response
export interface ApiResponse<T> {
  data: T;
  meta?: PaginationMeta;
  message?: string;
  success: boolean;
}

export interface ApiError {
  message: string;
  code?: string;
  status?: number;
  errors?: Record<string, string[]>;
}

// User & Auth
export interface User {
  id: string;
  name: string;
  email: string;
  avatar?: string;
  role: UserRole;
  status: "active" | "inactive" | "suspended";
  createdAt: string;
  updatedAt: string;
}

export type UserRole = "admin" | "manager" | "editor" | "viewer";

// Notification
export interface Notification {
  id: string;
  title: string;
  description: string;
  type: "info" | "success" | "warning" | "error";
  read: boolean;
  createdAt: string;
  actionUrl?: string;
}

// Chart / Analytics
export interface ChartDataPoint {
  label: string;
  value: number;
  [key: string]: string | number;
}
