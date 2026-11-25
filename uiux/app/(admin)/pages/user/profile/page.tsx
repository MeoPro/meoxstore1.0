import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "User Profile",
};

export default function UserProfilePage() {
  return (
    <div className="space-y-6">
      <PageHeader title="User Profile" description="Manage your personal profile information." />

      <div className="grid gap-6 lg:grid-cols-3">
        <Card className="lg:col-span-1">
          <CardContent className="p-5 text-center">
            <div className="mx-auto h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center text-2xl font-bold text-primary">
              JD
            </div>
            <h2 className="mt-3 text-lg font-semibold">John Doe</h2>
            <p className="text-sm text-muted-foreground">john@example.com</p>
            <p className="mt-2 text-sm text-muted-foreground">Full-Stack Developer</p>
            <Button variant="outline" className="mt-4 w-full">
              Change Avatar
            </Button>
          </CardContent>
        </Card>

        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Bio & Info</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
              <Input label="First Name" defaultValue="John" />
              <Input label="Last Name" defaultValue="Doe" />
            </div>
            <Input label="Email" type="email" defaultValue="john@example.com" />
            <Input label="Bio" defaultValue="Passionate developer building great products." />
            <Input label="Location" defaultValue="San Francisco, CA" />
            <Button>Save Changes</Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
