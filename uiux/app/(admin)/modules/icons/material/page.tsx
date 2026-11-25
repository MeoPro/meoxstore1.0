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
  CheckCircle,
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
  XCircle,
} from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Material Icons",
};

const icons: IconGridItem[] = [
  { name: "home", icon: Home },
  { name: "person", icon: User },
  { name: "favorite", icon: Heart },
  { name: "star", icon: Star },
  { name: "search", icon: Search },
  { name: "email", icon: Mail },
  { name: "notifications", icon: Bell },
  { name: "photo_camera", icon: Camera },
  { name: "settings", icon: Settings },
  { name: "delete", icon: Trash2 },
  { name: "edit", icon: Pencil },
  { name: "check_circle", icon: CheckCircle },
  { name: "cancel", icon: XCircle },
  { name: "add", icon: Plus },
  { name: "remove", icon: Minus },
  { name: "arrow_forward", icon: ArrowRight },
  { name: "arrow_back", icon: ArrowLeft },
  { name: "file_download", icon: Download },
  { name: "file_upload", icon: Upload },
  { name: "share", icon: Share2 },
  { name: "lock", icon: Lock },
  { name: "lock_open", icon: LockOpen },
  { name: "language", icon: Globe },
  { name: "place", icon: MapPin },
  { name: "phone", icon: Phone },
  { name: "event", icon: Calendar },
  { name: "schedule", icon: Clock },
  { name: "bookmark", icon: Bookmark },
  { name: "label", icon: Tag },
  { name: "folder", icon: Folder },
];

export default function MaterialIconsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Material Icons"
        description="Material Design style icons rendered with Lucide React."
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
            Use Material Icons via Google Fonts CDN or install{" "}
            <code className="rounded-md bg-muted px-1 py-0.5 text-xs">@mui/icons-material</code> for
            the React component library.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
