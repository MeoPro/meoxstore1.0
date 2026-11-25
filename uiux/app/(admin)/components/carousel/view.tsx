"use client";

import { PageHeader } from "@/components/layout/page-header";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useState } from "react";

const slides = [
  { title: "Slide 1", color: "from-primary/20 to-primary/5" },
  { title: "Slide 2", color: "from-success/20 to-success/5" },
  { title: "Slide 3", color: "from-warning/20 to-warning/5" },
  { title: "Slide 4", color: "from-info/20 to-info/5" },
];

export default function CarouselPage() {
  const [current, setCurrent] = useState(0);

  const prev = () => setCurrent((c) => (c === 0 ? slides.length - 1 : c - 1));
  const next = () => setCurrent((c) => (c === slides.length - 1 ? 0 : c + 1));

  return (
    <div className="space-y-6">
      <PageHeader title="Carousel" description="Slideshow component with navigation controls." />

      <Card>
        <CardHeader>
          <CardTitle>Basic Carousel</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative overflow-hidden rounded-xl">
            <div
              className={`flex h-48 items-center justify-center bg-gradient-to-br ${slides[current].color}`}
            >
              <span className="text-xl font-semibold text-foreground">{slides[current].title}</span>
            </div>
            <div className="absolute inset-y-0 left-0 flex items-center">
              <Button size="icon-sm" variant="ghost" onClick={prev} className="ml-2 bg-card/80">
                ‹
              </Button>
            </div>
            <div className="absolute inset-y-0 right-0 flex items-center">
              <Button size="icon-sm" variant="ghost" onClick={next} className="mr-2 bg-card/80">
                ›
              </Button>
            </div>
          </div>
          <div className="mt-3 flex justify-center gap-2">
            {slides.map((_, i) => (
              <button
                key={i}
                onClick={() => setCurrent(i)}
                className={`h-2 w-2 rounded-full transition-colors ${i === current ? "bg-primary" : "bg-muted-foreground/30"}`}
              />
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
