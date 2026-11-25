"use client";

import { store } from "@/store";
import { ThemeProvider } from "next-themes";
import { Provider as ReduxProvider } from "react-redux";
import { Toaster } from "sonner";

export function Providers({ children }: { children: React.ReactNode }) {
  return (
    <ReduxProvider store={store}>
      <ThemeProvider attribute="class" defaultTheme="system" enableSystem disableTransitionOnChange>
        {children}
        <Toaster position="top-right" richColors closeButton />
      </ThemeProvider>
    </ReduxProvider>
  );
}
