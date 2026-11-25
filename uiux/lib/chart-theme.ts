import type { CSSProperties } from "react";

// Theme-aware series colors; values come from --chart-* tokens in globals.css
export const chartColors = {
  primary: "var(--chart-1)",
  violet: "var(--chart-2)",
  success: "var(--chart-3)",
  warning: "var(--chart-4)",
  pink: "var(--chart-5)",
  danger: "var(--chart-6)",
  info: "var(--chart-7)",
  muted: "var(--chart-muted)",
} as const;

export const axisTick = { fill: "var(--muted-foreground)", fontSize: 11 };

const tooltipContent: CSSProperties = {
  backgroundColor: "var(--card)",
  border: "1px solid var(--border)",
  borderRadius: "8px",
  boxShadow: "var(--shadow-dropdown)",
  fontSize: "12px",
  padding: "8px 10px",
};

// Spread onto <Tooltip /> so every chart shares one readable tooltip in both themes
export const tooltipProps = {
  contentStyle: tooltipContent,
  labelStyle: { color: "var(--foreground)", fontWeight: 600, marginBottom: 2 },
  cursor: { fill: "var(--muted)", fillOpacity: 0.5, stroke: "var(--border)" },
};
