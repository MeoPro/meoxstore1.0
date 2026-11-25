import type { LucideIcon } from "lucide-react";

export interface IconGridItem {
  name: string;
  icon: LucideIcon;
}

export function IconGrid({ items }: { items: IconGridItem[] }) {
  return (
    <div className="grid grid-cols-3 gap-3 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-8">
      {items.map(({ name, icon: Icon }) => (
        <div
          key={name}
          className="flex flex-col items-center gap-2 rounded-lg border border-border p-3 text-center transition-colors hover:bg-muted/50"
        >
          <Icon className="h-5 w-5" />
          <span className="text-2xs text-muted-foreground">{name}</span>
        </div>
      ))}
    </div>
  );
}
