"use client";

import "leaflet/dist/leaflet.css";

import { useEffect, useRef } from "react";

const locations = [
  { label: "Dhaka", coords: [23.8103, 90.4125] as [number, number] },
  { label: "Chattogram", coords: [22.3569, 91.7832] as [number, number] },
  { label: "Sylhet", coords: [24.8949, 91.8687] as [number, number] },
  { label: "Khulna", coords: [22.8456, 89.5403] as [number, number] },
];

export function LeafletMap() {
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    let map: { remove: () => void } | null = null;
    let cancelled = false;

    (async () => {
      const L = (await import("leaflet")).default;
      if (cancelled || !containerRef.current) return;

      const m = L.map(containerRef.current).setView([23.685, 90.3563], 7);
      map = m;

      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution:
          '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      }).addTo(m);

      locations.forEach(({ label, coords }) => {
        L.circleMarker(coords, {
          radius: 8,
          color: "var(--ring)",
          weight: 2,
          fillColor: "var(--primary)",
          fillOpacity: 0.85,
        })
          .addTo(m)
          .bindPopup(`<strong>${label}</strong>`);
      });
    })();

    return () => {
      cancelled = true;
      map?.remove();
    };
  }, []);

  return <div ref={containerRef} className="z-0 h-[400px] w-full rounded-lg" />;
}
