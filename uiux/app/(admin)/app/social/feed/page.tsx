import { PageHeader } from "@/components/layout/page-header";
import { Avatar } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Heart, MessageCircle, Share2 } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Social Feed",
};

const posts = [
  {
    id: 1,
    author: "Sarah Chen",
    role: "Product Designer",
    content:
      "Just shipped the new dashboard redesign! 🎉 Super proud of the team for pulling this off in 2 weeks.",
    likes: 42,
    comments: 8,
    time: "2 hours ago",
  },
  {
    id: 2,
    author: "Mike Johnson",
    role: "Frontend Developer",
    content:
      "Anyone else excited about the new React compiler? The automatic memoization is going to save us so much boilerplate.",
    likes: 28,
    comments: 12,
    time: "4 hours ago",
  },
  {
    id: 3,
    author: "Emma Davis",
    role: "Engineering Manager",
    content:
      "We just hit 99.9% uptime for 6 months straight. Big shoutout to the infrastructure team! 🏆",
    likes: 89,
    comments: 15,
    time: "6 hours ago",
  },
  {
    id: 4,
    author: "James Wilson",
    role: "Backend Developer",
    content:
      "Published a new blog post on microservices patterns we've adopted. Link in bio if anyone is interested!",
    likes: 35,
    comments: 6,
    time: "1 day ago",
  },
];

export default function SocialFeedPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Feed" description="Latest updates from your team." />

      <div className="max-w-2xl mx-auto space-y-4">
        {posts.map((post) => (
          <Card key={post.id}>
            <CardContent className="p-5">
              <div className="flex items-center gap-3 mb-3">
                <Avatar name={post.author} />
                <div>
                  <p className="text-sm font-medium">{post.author}</p>
                  <p className="text-xs text-muted-foreground">
                    {post.role} · {post.time}
                  </p>
                </div>
              </div>
              <p className="text-sm mb-4">{post.content}</p>
              <div className="flex items-center gap-4 pt-3 border-t border-border">
                <Button variant="ghost" size="sm" className="text-muted-foreground">
                  <Heart className="h-4 w-4 mr-1" />
                  {post.likes}
                </Button>
                <Button variant="ghost" size="sm" className="text-muted-foreground">
                  <MessageCircle className="h-4 w-4 mr-1" />
                  {post.comments}
                </Button>
                <Button variant="ghost" size="sm" className="text-muted-foreground">
                  <Share2 className="h-4 w-4 mr-1" />
                  Share
                </Button>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
