import Header from "@/components/Header";
import Hero from "@/components/Hero";
import LoadingGrid from "@/components/LoadingGrid";
import SectionTitle from "@/components/SectionTitle";

export default function Loading() {
  return (
    <main>
      <Header />
      <Hero priorityImage={false} />
      <section className="posts-section">
        <div className="shell">
          <SectionTitle eyebrow="Latest Posts" title="Latest Posts" />
          <LoadingGrid />
        </div>
      </section>
    </main>
  );
}
