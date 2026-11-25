export const siteConfig = {
  name: "DropX Admin",
  description:
    "Free Next.js 16 admin dashboard template with 8 dashboards, apps, auth pages, and 100+ UI components. Built with React 19, TypeScript, and Tailwind CSS v4.",
  version: "1.0.1",
  url: "https://dropx-admin-datadropx.vercel.app",
  author: {
    name: "DataDropX",
    url: "https://datadropx.net",
    email: "hello@datadropx.net",
  },
  developer: {
    name: "DataDropX",
    url: "https://datadropx.net",
  },
  distributor: {
    name: "ThemeWagon",
    url: "https://themewagon.com",
  },
} as const;

// Signed-in demo user shown in the header menu and profile pages
export const demoUser = {
  name: "John Doe",
  email: "john@example.com",
  role: "Admin",
} as const;
