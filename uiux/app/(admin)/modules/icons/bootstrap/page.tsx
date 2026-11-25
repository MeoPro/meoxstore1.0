import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { IconGrid, type IconGridItem } from "@/components/ui/icon-grid";
import {
  ArrowLeft,
  ArrowRight,
  Bell,
  Bookmark,
  Calendar,
  Camera,
  Check,
  Clock,
  Download,
  Folder,
  Globe,
  Heart,
  Home,
  Lock,
  LockOpen,
  Mail,
  MapPin,
  Minus,
  Pencil,
  Phone,
  Plus,
  Search,
  Settings,
  Share2,
  Star,
  Tag,
  Trash2,
  Upload,
  User,
  X,
} from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Bootstrap Icons",
};

const icons: IconGridItem[] = [
  { name: "bi-house", icon: Home },
  { name: "bi-person", icon: User },
  { name: "bi-heart", icon: Heart },
  { name: "bi-star", icon: Star },
  { name: "bi-search", icon: Search },
  { name: "bi-envelope", icon: Mail },
  { name: "bi-bell", icon: Bell },
  { name: "bi-camera", icon: Camera },
  { name: "bi-gear", icon: Settings },
  { name: "bi-trash", icon: Trash2 },
  { name: "bi-pencil", icon: Pencil },
  { name: "bi-check-lg", icon: Check },
  { name: "bi-x-lg", icon: X },
  { name: "bi-plus-lg", icon: Plus },
  { name: "bi-dash-lg", icon: Minus },
  { name: "bi-arrow-right", icon: ArrowRight },
  { name: "bi-arrow-left", icon: ArrowLeft },
  { name: "bi-download", icon: Download },
  { name: "bi-upload", icon: Upload },
  { name: "bi-share", icon: Share2 },
  { name: "bi-lock", icon: Lock },
  { name: "bi-unlock", icon: LockOpen },
  { name: "bi-globe", icon: Globe },
  { name: "bi-geo-alt", icon: MapPin },
  { name: "bi-telephone", icon: Phone },
  { name: "bi-calendar", icon: Calendar },
  { name: "bi-clock", icon: Clock },
  { name: "bi-bookmark", icon: Bookmark },
  { name: "bi-tag", icon: Tag },
  { name: "bi-folder", icon: Folder },
];

export default function BootstrapIconsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Bootstrap Icons"
        description="Bootstrap style icons rendered with Lucide React."
      />

      <Card>
        <CardHeader>
          <CardTitle>Icons Grid</CardTitle>
        </CardHeader>
        <CardContent>
          <IconGrid items={icons} />
        </CardContent>
      </Card>

      <Card>
        <CardContent className="py-4">
          <p className="text-sm text-muted-foreground">
            Install Bootstrap Icons with{" "}
            <code className="rounded-md bg-muted px-1 py-0.5 text-xs">
              npm install bootstrap-icons
            </code>{" "}
            and import the CSS or use individual SVGs.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
