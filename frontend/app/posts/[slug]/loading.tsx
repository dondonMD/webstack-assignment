import Header from "@/components/Header";

export default function LoadingPostPage() {
  return (
    <main>
      <Header />
      <article className="post-page">
        <div className="shell post-page-shell">
          <div className="skeleton skeleton-inline" />
          <div className="skeleton skeleton-title" />
          <div className="skeleton skeleton-meta" />
          <div className="skeleton skeleton-image" />
          <div className="skeleton skeleton-copy" />
          <div className="skeleton skeleton-copy short" />
          <div className="skeleton skeleton-copy" />
        </div>
      </article>
    </main>
  );
}
