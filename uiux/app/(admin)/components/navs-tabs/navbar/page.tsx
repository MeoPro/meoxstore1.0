import { PageHeader } from "@/components/layout/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Navbar",
};

export default function NavbarPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Navbar" description="Top navigation bar variations." />

      <Card>
        <CardHeader>
          <CardTitle>Default Navbar</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="rounded-xl border border-border bg-card px-4 py-3">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-6">
                <span className="text-lg font-bold text-primary">Brand</span>
                <nav className="hidden items-center gap-4 text-sm sm:flex">
                  <span className="font-medium text-foreground cursor-pointer">Home</span>
                  <span className="text-muted-foreground hover:text-foreground cursor-pointer">
                    Features
                  </span>
                  <span className="text-muted-foreground hover:text-foreground cursor-pointer">
                    Pricing
                  </span>
                  <span className="text-muted-foreground hover:text-foreground cursor-pointer">
                    About
                  </span>
                </nav>
              </div>
              <div className="flex items-center gap-2">
                <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-xs font-medium text-primary">
                  U
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Dark Navbar</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="rounded-xl bg-slate-900 px-4 py-3">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-6">
                <span className="text-lg font-bold text-white">Brand</span>
                <nav className="hidden items-center gap-4 text-sm sm:flex">
                  <span className="font-medium text-white cursor-pointer">Home</span>
                  <span className="text-slate-400 hover:text-white cursor-pointer">Products</span>
                  <span className="text-slate-400 hover:text-white cursor-pointer">Team</span>
                  <span className="text-slate-400 hover:text-white cursor-pointer">Contact</span>
                </nav>
              </div>
              <button className="rounded-lg bg-white/10 px-3 py-1.5 text-sm text-white">
                Sign In
              </button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
