"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { cn } from "@/lib/utils";
import {
  BarChart3,
  CheckCircle2,
  ChevronDown,
  CircleDollarSign,
  Code2,
  CreditCard,
  FileText,
  KeyRound,
  Layers3,
  LifeBuoy,
  Lock,
  PackageCheck,
  Rocket,
  ShieldCheck,
  Sparkles,
  UserCog,
} from "lucide-react";
import { useState } from "react";

type AccordionItemData = {
  title: string;
  content: string;
  icon?: React.ReactNode;
  badge?: string;
  meta?: string;
};

const faqItems: AccordionItemData[] = [
  {
    title: "What makes DropX Admin production ready?",
    content:
      "DropX Admin ships with typed data, reusable UI primitives, responsive layouts, dashboard variants, ecommerce flows, auth screens, charts, maps, dark mode, and documentation prepared for real product teams.",
    icon: <Rocket className="h-4 w-4" />,
    badge: "Popular",
  },
  {
    title: "Can I customize branding and colors?",
    content:
      "Yes. Brand details live in config files, while light and dark theme tokens live in the global stylesheet. You can update the logo, primary colors, sidebar, navigation, and mock data without rewriting every page.",
    icon: <Sparkles className="h-4 w-4" />,
  },
  {
    title: "Does it support commercial projects?",
    content:
      "The template is designed for commercial admin panels, SaaS dashboards, ecommerce back offices, internal tools, and client projects. Always follow the license terms from the marketplace where it was purchased.",
    icon: <ShieldCheck className="h-4 w-4" />,
  },
];

const featureItems: AccordionItemData[] = [
  {
    title: "Dashboard analytics",
    content:
      "Use KPI cards, trend charts, activity streams, and compact tables to build executive-level dashboard experiences with polished responsive behavior.",
    icon: <BarChart3 className="h-5 w-5" />,
    meta: "Charts, stats, insights",
  },
  {
    title: "Ecommerce operations",
    content:
      "Product, customer, order, checkout, invoice, cart, and billing pages give buyers a complete commerce admin starter kit.",
    icon: <PackageCheck className="h-5 w-5" />,
    meta: "Orders, products, billing",
  },
  {
    title: "Developer handoff",
    content:
      "TypeScript, centralized configuration, reusable components, and clear documentation make handoff easier for teams and marketplace buyers.",
    icon: <Code2 className="h-5 w-5" />,
    meta: "Typed, documented, scalable",
  },
];

const settingsItems: AccordionItemData[] = [
  {
    title: "Account permissions",
    content:
      "Create role-based access panels with readable grouping, status indicators, and clear permission descriptions for every module.",
    icon: <UserCog className="h-4 w-4" />,
    badge: "Team",
  },
  {
    title: "Security controls",
    content:
      "Show two-factor authentication, trusted devices, recovery codes, password rules, and login monitoring inside a compact settings layout.",
    icon: <Lock className="h-4 w-4" />,
    badge: "Secure",
  },
  {
    title: "Billing preferences",
    content:
      "Organize payment methods, invoice emails, tax details, and renewal preferences without overwhelming buyers on smaller screens.",
    icon: <CreditCard className="h-4 w-4" />,
    badge: "Billing",
  },
  {
    title: "API access",
    content:
      "Present API keys, webhook URLs, scopes, and audit details in a clean disclosure pattern for technical admin pages.",
    icon: <KeyRound className="h-4 w-4" />,
    badge: "API",
  },
];

const processItems: AccordionItemData[] = [
  {
    title: "Prepare the source package",
    content:
      "Clean local files, remove generated folders, verify documentation, and make sure the release zip contains only buyer-safe source assets.",
    icon: <Layers3 className="h-4 w-4" />,
    meta: "Step 01",
  },
  {
    title: "Run release validation",
    content:
      "Run linting, type checking, and production build. The marketplace package should not ship with known TypeScript, ESLint, or route errors.",
    icon: <CheckCircle2 className="h-4 w-4" />,
    meta: "Step 02",
  },
  {
    title: "Publish and support",
    content:
      "Upload the demo, screenshots, documentation, package zip, changelog, and support policy. After approval, respond quickly to buyer issues.",
    icon: <LifeBuoy className="h-4 w-4" />,
    meta: "Step 03",
  },
];

function AnimatedPanel({ open, children }: { open: boolean; children: React.ReactNode }) {
  return (
    <div
      className={cn(
        "grid transition-all duration-300 ease-out",
        open ? "grid-rows-[1fr] opacity-100" : "grid-rows-[0fr] opacity-0",
      )}
    >
      <div className="overflow-hidden">{children}</div>
    </div>
  );
}

function PremiumAccordion({
  items,
  variant,
  defaultOpen = 0,
}: {
  items: AccordionItemData[];
  variant: "classic" | "featured" | "compact" | "timeline";
  defaultOpen?: number;
}) {
  const [openIndex, setOpenIndex] = useState<number | null>(defaultOpen);

  if (variant === "featured") {
    return (
      <div className="grid gap-3">
        {items.map((item, index) => {
          const open = openIndex === index;
          return (
            <div
              key={item.title}
              className={cn(
                "overflow-hidden rounded-lg border transition-all duration-300",
                open
                  ? "border-primary/30 bg-gradient-to-br from-primary/10 via-card to-info/10 shadow-card"
                  : "border-border bg-card hover:border-primary/20",
              )}
            >
              <button
                onClick={() => setOpenIndex(open ? null : index)}
                className="flex w-full items-start gap-3 p-4 text-left sm:p-5"
                aria-expanded={open}
              >
                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                  {item.icon}
                </span>
                <span className="min-w-0 flex-1">
                  <span className="block text-sm font-semibold text-foreground">{item.title}</span>
                  {item.meta && (
                    <span className="mt-1 block text-xs text-muted-foreground">{item.meta}</span>
                  )}
                </span>
                <ChevronDown
                  className={cn(
                    "mt-1 h-4 w-4 shrink-0 transition-transform duration-300",
                    open && "rotate-180",
                  )}
                />
              </button>
              <AnimatedPanel open={open}>
                <p className="px-4 pb-4 pl-[68px] text-sm leading-6 text-muted-foreground sm:px-5 sm:pb-5 sm:pl-[76px]">
                  {item.content}
                </p>
              </AnimatedPanel>
            </div>
          );
        })}
      </div>
    );
  }

  if (variant === "compact") {
    return (
      <div className="divide-y divide-border rounded-xl border border-border bg-card">
        {items.map((item, index) => {
          const open = openIndex === index;
          return (
            <div key={item.title}>
              <button
                onClick={() => setOpenIndex(open ? null : index)}
                className="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-muted/50"
                aria-expanded={open}
              >
                <span
                  className={cn("text-muted-foreground transition-colors", open && "text-primary")}
                >
                  {item.icon}
                </span>
                <span className="min-w-0 flex-1 text-sm font-medium">{item.title}</span>
                {item.badge && <Badge variant={open ? "default" : "secondary"}>{item.badge}</Badge>}
                <ChevronDown
                  className={cn(
                    "h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-300",
                    open && "rotate-180",
                  )}
                />
              </button>
              <AnimatedPanel open={open}>
                <p className="px-4 pb-4 pl-11 text-sm leading-6 text-muted-foreground">
                  {item.content}
                </p>
              </AnimatedPanel>
            </div>
          );
        })}
      </div>
    );
  }

  if (variant === "timeline") {
    return (
      <div className="space-y-3">
        {items.map((item, index) => {
          const open = openIndex === index;
          return (
            <div key={item.title} className="relative pl-9">
              <div className="absolute left-4 top-11 h-[calc(100%-1rem)] w-px bg-border last:hidden" />
              <span
                className={cn(
                  "absolute left-0 top-3 flex h-8 w-8 items-center justify-center rounded-full border bg-card transition-colors",
                  open
                    ? "border-primary bg-primary text-primary-foreground"
                    : "border-border text-muted-foreground",
                )}
              >
                {item.icon}
              </span>
              <div
                className={cn(
                  "rounded-xl border bg-card transition-colors",
                  open ? "border-primary/30" : "border-border",
                )}
              >
                <button
                  onClick={() => setOpenIndex(open ? null : index)}
                  className="flex w-full items-center gap-3 px-4 py-3 text-left"
                  aria-expanded={open}
                >
                  <span className="min-w-0 flex-1">
                    {item.meta && (
                      <span className="text-2xs font-bold uppercase tracking-widest text-primary">
                        {item.meta}
                      </span>
                    )}
                    <span className="mt-1 block text-sm font-semibold">{item.title}</span>
                  </span>
                  <ChevronDown
                    className={cn(
                      "h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-300",
                      open && "rotate-180",
                    )}
                  />
                </button>
                <AnimatedPanel open={open}>
                  <p className="px-4 pb-4 text-sm leading-6 text-muted-foreground">
                    {item.content}
                  </p>
                </AnimatedPanel>
              </div>
            </div>
          );
        })}
      </div>
    );
  }

  return (
    <div className="space-y-2">
      {items.map((item, index) => {
        const open = openIndex === index;
        return (
          <div
            key={item.title}
            className={cn(
              "rounded-xl border bg-card transition-colors duration-300",
              open ? "border-primary/30 shadow-card" : "border-border hover:bg-muted/30",
            )}
          >
            <button
              onClick={() => setOpenIndex(open ? null : index)}
              className="flex w-full items-center gap-3 px-4 py-3.5 text-left"
              aria-expanded={open}
            >
              <span
                className={cn(
                  "flex h-8 w-8 shrink-0 items-center justify-center rounded-lg",
                  open ? "bg-primary/10 text-primary" : "bg-muted text-muted-foreground",
                )}
              >
                {item.icon}
              </span>
              <span className="min-w-0 flex-1 text-sm font-semibold text-foreground">
                {item.title}
              </span>
              {item.badge && <Badge variant="info">{item.badge}</Badge>}
              <ChevronDown
                className={cn(
                  "h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-300",
                  open && "rotate-180",
                )}
              />
            </button>
            <AnimatedPanel open={open}>
              <p className="px-4 pb-4 pl-15 text-sm leading-6 text-muted-foreground">
                {item.content}
              </p>
            </AnimatedPanel>
          </div>
        );
      })}
    </div>
  );
}

export default function AccordionPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Accordion"
        description="Premium disclosure patterns with smooth responsive motion."
      />

      <div className="grid gap-6 xl:grid-cols-2">
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between gap-3">
              <CardTitle>Classic FAQ</CardTitle>
              <Badge variant="outline">Design 01</Badge>
            </div>
          </CardHeader>
          <CardContent>
            <PremiumAccordion items={faqItems} variant="classic" />
          </CardContent>
        </Card>

        <Card className="overflow-hidden">
          <CardHeader>
            <div className="flex items-center justify-between gap-3">
              <CardTitle>Feature Spotlight</CardTitle>
              <Badge variant="outline">Design 02</Badge>
            </div>
          </CardHeader>
          <CardContent>
            <PremiumAccordion items={featureItems} variant="featured" />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <div className="flex items-center justify-between gap-3">
              <CardTitle>Settings Disclosure</CardTitle>
              <Badge variant="outline">Design 03</Badge>
            </div>
          </CardHeader>
          <CardContent>
            <PremiumAccordion items={settingsItems} variant="compact" />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <div className="flex items-center justify-between gap-3">
              <CardTitle>Process Timeline</CardTitle>
              <Badge variant="outline">Design 04</Badge>
            </div>
          </CardHeader>
          <CardContent>
            <PremiumAccordion items={processItems} variant="timeline" />
          </CardContent>
        </Card>
      </div>

      <Card className="border-primary/20 bg-primary/5">
        <CardContent className="flex flex-col gap-3 p-5 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-2">
            <CircleDollarSign className="h-4 w-4 text-primary" />
            <span>
              All four accordion styles are responsive, keyboard-friendly, and animated with CSS
              transitions.
            </span>
          </div>
          <div className="flex items-center gap-2 text-xs font-medium text-foreground">
            <FileText className="h-4 w-4 text-primary" />
            Premium component showcase
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
