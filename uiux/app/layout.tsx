import { Providers } from "@/components/providers";
import { siteConfig } from "@/config/site";
import type { Metadata, Viewport } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  metadataBase: new URL(siteConfig.url),
  title: {
    default: `${siteConfig.name} by DataDropX`,
    template: `%s | ${siteConfig.name}`,
  },
  description: siteConfig.description,
  applicationName: siteConfig.name,
  authors: [{ name: siteConfig.author.name, url: siteConfig.author.url }],
  creator: siteConfig.author.name,
  publisher: siteConfig.author.name,
  keywords: ["admin dashboard", "nextjs template", "react admin", "tailwind css", "free template"],
  openGraph: {
    type: "website",
    siteName: siteConfig.name,
    title: `${siteConfig.name} by DataDropX`,
    description: siteConfig.description,
    url: siteConfig.url,
  },
  twitter: {
    card: "summary_large_image",
    title: `${siteConfig.name} by DataDropX`,
    description: siteConfig.description,
  },
};

export const viewport: Viewport = {
  themeColor: [
    { media: "(prefers-color-scheme: light)", color: "#f9fafb" },
    { media: "(prefers-color-scheme: dark)", color: "#0f172a" },
  ],
};

// Structured data so search engines and AI answer engines credit DataDropX as the author
const organization = {
  "@type": "Organization",
  "@id": `${siteConfig.author.url}/#organization`,
  name: siteConfig.author.name,
  url: siteConfig.author.url,
  email: siteConfig.author.email,
  logo: `${siteConfig.url}/brand/datadropx-logo-for-light-bg.png`,
};

const jsonLd = {
  "@context": "https://schema.org",
  "@graph": [
    organization,
    {
      "@type": "SoftwareApplication",
      name: siteConfig.name,
      alternateName: `${siteConfig.name} by DataDropX`,
      description: siteConfig.description,
      url: siteConfig.url,
      applicationCategory: "DeveloperApplication",
      operatingSystem: "Web",
      softwareVersion: siteConfig.version,
      isAccessibleForFree: true,
      offers: { "@type": "Offer", price: "0", priceCurrency: "USD" },
      author: { "@id": organization["@id"] },
      creator: { "@id": organization["@id"] },
      publisher: { "@id": organization["@id"] },
      copyrightHolder: { "@id": organization["@id"] },
      copyrightYear: 2026,
    },
  ],
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html
      lang="en"
      suppressHydrationWarning
      className={`${geistSans.variable} ${geistMono.variable}`}
    >
      <body className="min-h-screen antialiased" suppressHydrationWarning>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify(jsonLd).replace(/</g, "\\u003c"),
          }}
        />
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
