import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "MyBlog | WordPress + Next.js Assignment",
  description:
    "A polished homepage built twice from the same WordPress content source: Elementor and Next.js with WPGraphQL.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}
