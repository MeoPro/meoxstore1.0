import { navigation } from "@/config/navigation";
import { siteConfig } from "@/config/site";
import type { NavItem } from "@/types";
import type { MetadataRoute } from "next";

// Every page reachable from the sidebar; parent items only toggle submenus
function leafHrefs(items: NavItem[]): string[] {
  return items.flatMap((item) => (item.children ? leafHrefs(item.children) : [item.href]));
}

export default function sitemap(): MetadataRoute.Sitemap {
  const hrefs = [...new Set(navigation.flatMap((group) => leafHrefs(group.items)))];

  return hrefs.map((href) => ({
    url: `${siteConfig.url}${href}`,
    changeFrequency: "monthly",
    priority: href === "/dashboard" ? 1 : 0.6,
  }));
}
