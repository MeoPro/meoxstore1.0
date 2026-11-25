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
  title: "Font Awesome Icons",
};

const icons: IconGridItem[] = [
  { name: "fa-home", icon: Home },
  { name: "fa-user", icon: User },
  { name: "fa-heart", icon: Heart },
  { name: "fa-star", icon: Star },
  { name: "fa-search", icon: Search },
  { name: "fa-envelope", icon: Mail },
  { name: "fa-bell", icon: Bell },
  { name: "fa-camera", icon: Camera },
  { name: "fa-cog", icon: Settings },
  { name: "fa-trash", icon: Trash2 },
  { name: "fa-edit", icon: Pencil },
  { name: "fa-check", icon: Check },
  { name: "fa-times", icon: X },
  { name: "fa-plus", icon: Plus },
  { name: "fa-minus", icon: Minus },
  { name: "fa-arrow-right", icon: ArrowRight },
  { name: "fa-arrow-left", icon: ArrowLeft },
  { name: "fa-download", icon: Download },
  { name: "fa-upload", icon: Upload },
  { name: "fa-share", icon: Share2 },
  { name: "fa-lock", icon: Lock },
  { name: "fa-unlock", icon: LockOpen },
  { name: "fa-globe", icon: Globe },
  { name: "fa-map-marker", icon: MapPin },
  { name: "fa-phone", icon: Phone },
  { name: "fa-calendar", icon: Calendar },
  { name: "fa-clock", icon: Clock },
  { name: "fa-bookmark", icon: Bookmark },
  { name: "fa-tag", icon: Tag },
  { name: "fa-folder", icon: Folder },
];

export default function FontAwesomeIconsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Font Awesome Icons"
        description="Font Awesome style icons rendered with Lucide React."
      />

      <Card>
        <CardHeader>
          <CardTitle>Solid Icons</CardTitle>
        </CardHeader>
        <CardContent>
          <IconGrid items={icons} />
        </CardContent>
      </Card>

      <Card>
        <CardContent className="py-4">
          <p className="text-sm text-muted-foreground">
            Install Font Awesome with{" "}
            <code className="rounded-md bg-muted px-1 py-0.5 text-xs">
              npm install @fortawesome/fontawesome-free
            </code>{" "}
            or use the React components from{" "}
            <code className="rounded-md bg-muted px-1 py-0.5 text-xs">
              @fortawesome/react-fontawesome
            </code>
            .
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
