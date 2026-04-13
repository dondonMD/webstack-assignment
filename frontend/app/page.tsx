import Header from "@/components/Header";
import Hero from "@/components/Hero";
import PostsGrid from "@/components/PostsGrid";
import SectionTitle from "@/components/SectionTitle";
import { getLatestPosts } from "@/lib/wordpress";

export const revalidate = 60;

export default async function HomePage() {
  const posts = await getLatestPosts();

  return (
    <main>
      <Header />
      <Hero />
      <section className="posts-section">
        <div className="shell">
          <SectionTitle title="Latest Posts" />
          <PostsGrid posts={posts} />
        </div>
      </section>
    </main>
  );
}
