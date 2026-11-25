import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent } from "@/components/ui/card";
import {
  Book,
  FileText,
  HelpCircle,
  MessageCircle,
  Settings,
  Shield,
  Users,
  Zap,
} from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Quick Links",
};

const links = [
  {
    icon: Book,
    title: "Knowledge Base",
    description: "Browse support articles and guides",
    color: "text-blue-500",
  },
  {
    icon: FileText,
    title: "Documentation",
    description: "API docs and technical references",
    color: "text-green-500",
  },
  {
    icon: MessageCircle,
    title: "Community Forum",
    description: "Ask questions and share knowledge",
    color: "text-purple-500",
  },
  {
    icon: HelpCircle,
    title: "FAQ",
    description: "Frequently asked questions",
    color: "text-orange-500",
  },
  {
    icon: Zap,
    title: "Status Page",
    description: "System uptime and incidents",
    color: "text-yellow-500",
  },
  {
    icon: Users,
    title: "Team Directory",
    description: "Find support agents and managers",
    color: "text-pink-500",
  },
  {
    icon: Shield,
    title: "Security Center",
    description: "Security policies and best practices",
    color: "text-red-500",
  },
  {
    icon: Settings,
    title: "Admin Settings",
    description: "Configure support desk settings",
    color: "text-gray-500",
  },
];

export default function QuickLinksPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Quick Links" description="Shortcuts to commonly used resources." />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {links.map((link) => {
          const Icon = link.icon;
          return (
            <Card
              key={link.title}
              className="hover:shadow-dropdown transition-shadow cursor-pointer"
            >
              <CardContent className="p-5 text-center">
                <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-muted">
                  <Icon className={`h-6 w-6 ${link.color}`} />
                </div>
                <h3 className="text-sm font-medium">{link.title}</h3>
                <p className="mt-1 text-xs text-muted-foreground">{link.description}</p>
              </CardContent>
            </Card>
          );
        })}
      </div>
    </div>
  );
}
