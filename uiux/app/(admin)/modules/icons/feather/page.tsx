import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { IconGrid, type IconGridItem } from "@/components/ui/icon-grid";
import {
  ArrowLeft,
  ArrowRight,
  Bell,
  Camera,
  Check,
  Download,
  Edit,
  Heart,
  Home,
  Mail,
  Minus,
  Plus,
  Search,
  Settings,
  Share2,
  Star,
  Trash2,
  Upload,
  User,
  X,
} from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Feather Icons",
};

const icons: IconGridItem[] = [
  { name: "Home", icon: Home },
  { name: "User", icon: User },
  { name: "Heart", icon: Heart },
  { name: "Star", icon: Star },
  { name: "Search", icon: Search },
  { name: "Mail", icon: Mail },
  { name: "Bell", icon: Bell },
  { name: "Camera", icon: Camera },
  { name: "Settings", icon: Settings },
  { name: "Trash2", icon: Trash2 },
  { name: "Edit", icon: Edit },
  { name: "Check", icon: Check },
  { name: "X", icon: X },
  { name: "Plus", icon: Plus },
  { name: "Minus", icon: Minus },
  { name: "ArrowRight", icon: ArrowRight },
  { name: "ArrowLeft", icon: ArrowLeft },
  { name: "Download", icon: Download },
  { name: "Upload", icon: Upload },
  { name: "Share2", icon: Share2 },
];

export default function FeatherIconsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Feather Icons"
        description="Beautiful, open-source icons via Lucide React."
      />

      <Card>
        <CardHeader>
          <CardTitle>Icons Grid</CardTitle>
        </CardHeader>
        <CardContent>
          <IconGrid items={icons} />
        </CardContent>
      </Card>
    </div>
  );
}
