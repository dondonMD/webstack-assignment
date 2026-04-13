import type { WordPressPostSummary } from "@/types/wordpress";
import PostCard from "./PostCard";

type PostsGridProps = {
  posts: WordPressPostSummary[];
};

export default function PostsGrid({ posts }: PostsGridProps) {
  if (posts.length === 0) {
    return (
      <div className="empty-state">
        <p className="eyebrow">No Posts Yet</p>
        <h3>No posts are available from WordPress.</h3>
        <p>
          Check the seed step or publish content in WordPress, then refresh the
          page.
        </p>
      </div>
    );
  }

  return (
    <div className="posts-grid">
      {posts.map((post) => (
        <PostCard key={post.slug} post={post} />
      ))}
    </div>
  );
}
