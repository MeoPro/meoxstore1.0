"use client";

import { cn } from "@/lib/utils";
import { useEffect, useRef, useState } from "react";

type GradientVariant =
  "primary" | "success" | "warning" | "danger" | "info" | "purple" | "pink" | "ocean" | "sunset";

interface GradientCardProps {
  title: string;
  value: string | number;
  change?: number;
  changeLabel?: string;
  icon?: React.ReactNode;
  variant?: GradientVariant;
  className?: string;
}

export function GradientCard({
  title,
  value,
  change,
  changeLabel = "vs last month",
  icon,
  variant = "primary",
  className,
}: GradientCardProps) {
  const [visible, setVisible] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setVisible(true);
          observer.disconnect();
        }
      },
      { threshold: 0.1 },
    );
    if (ref.current) observer.observe(ref.current);
    return () => observer.disconnect();
  }, []);

  return (
    <div
      ref={ref}
      className={cn(
        `gradient-${variant} relative overflow-hidden rounded-xl p-5 text-white`,
        visible ? "animate-fade-in-up" : "opacity-0",
        className,
      )}
    >
      <div className="absolute -right-3 -top-3 h-20 w-20 rounded-full bg-white/10" />
      <div className="absolute -bottom-4 -right-4 h-24 w-24 rounded-full bg-white/5" />

      <div className="relative">
        <div className="flex items-center justify-between">
          <p className="text-xs font-medium text-white/80">{title}</p>
          {icon && (
            <div className="flex h-8 w-8 items-center justify-center rounded-md bg-white/15">
              {icon}
            </div>
          )}
        </div>
        <p className="mt-2 text-2xl font-bold">{value}</p>
        {change !== undefined && (
          <p className="mt-1.5 text-xs text-white/70">
            <span className="font-semibold text-white">
              {change >= 0 ? "↑" : "↓"} {Math.abs(change)}%
            </span>{" "}
            {changeLabel}
          </p>
        )}
      </div>
    </div>
  );
}
