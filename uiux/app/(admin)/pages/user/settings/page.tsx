import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "User Settings",
};

export default function UserSettingsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="User Settings"
        description="Manage your account settings and preferences."
      />

      {/* General */}
      <Card>
        <CardHeader>
          <CardTitle>General</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <Input label="Display Name" defaultValue="John Doe" />
          <Input label="Email" type="email" defaultValue="john@example.com" />
          <Input label="Language" defaultValue="English" />
          <Button>Update Profile</Button>
        </CardContent>
      </Card>

      {/* Notifications */}
      <Card>
        <CardHeader>
          <CardTitle>Notifications</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3 text-sm">
            <div className="flex items-center justify-between">
              <span>Email notifications</span>
              <span className="text-muted-foreground">Enabled</span>
            </div>
            <div className="flex items-center justify-between">
              <span>Push notifications</span>
              <span className="text-muted-foreground">Enabled</span>
            </div>
            <div className="flex items-center justify-between">
              <span>Weekly digest</span>
              <span className="text-muted-foreground">Disabled</span>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Security */}
      <Card>
        <CardHeader>
          <CardTitle>Security</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <Input label="Current Password" type="password" placeholder="••••••••" />
          <Input label="New Password" type="password" placeholder="••••••••" />
          <Input label="Confirm Password" type="password" placeholder="••••••••" />
          <Button>Change Password</Button>
        </CardContent>
      </Card>
    </div>
  );
}
