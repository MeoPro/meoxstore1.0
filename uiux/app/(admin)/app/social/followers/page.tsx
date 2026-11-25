import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Followers",
};

const followers = [
  { id: 1, name: "Sarah Chen", role: "Product Designer", followers: 1240, following: true },
  { id: 2, name: "Mike Johnson", role: "Frontend Developer", followers: 890, following: true },
  { id: 3, name: "Emma Davis", role: "Engineering Manager", followers: 2100, following: false },
  { id: 4, name: "James Wilson", role: "Backend Developer", followers: 560, following: true },
  { id: 5, name: "Lisa Park", role: "DevOps Engineer", followers: 780, following: false },
  { id: 6, name: "David Kim", role: "Data Scientist", followers: 1050, following: false },
  { id: 7, name: "Anna Brown", role: "UX Researcher", followers: 920, following: true },
  { id: 8, name: "Tom Miller", role: "QA Engineer", followers: 450, following: false },
];

export default function FollowersPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Followers" description="People following your activity." />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {followers.map((follower) => (
          <Card key={follower.id}>
            <CardContent className="p-5 text-center">
              <div className="flex flex-col items-center">
                <Avatar name={follower.name} size="lg" />
                <h3 className="mt-3 text-sm font-medium">{follower.name}</h3>
                <p className="text-xs text-muted-foreground">{follower.role}</p>
                <p className="mt-1 text-xs text-muted-foreground">
                  {follower.followers.toLocaleString()} followers
                </p>
                <Button
                  variant={follower.following ? "outline" : "default"}
                  size="sm"
                  className="mt-3 w-full"
                >
                  {follower.following ? "Following" : "Follow"}
                </Button>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
