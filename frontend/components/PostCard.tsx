import Image from "next/image";
import Link from "next/link";
import type { WordPressPostSummary } from "@/types/wordpress";
import { formatDate } from "@/lib/utils";

type PostCardProps = {
  post: WordPressPostSummary;
};

export default function PostCard({ post }: PostCardProps) {
  return (
    <article className="post-card">
      <Link aria-label={`Read ${post.title}`} className="post-card-image" href={`/posts/${post.slug}`}>
        <Image
          alt={post.featuredImage.alt}
          fill
          sizes="(max-width: 768px) 100vw, (max-width: 1080px) 50vw, 33vw"
          src={post.featuredImage.url}
        />
      </Link>
      <div className="post-card-body">
        <h3>
          <Link href={`/posts/${post.slug}`}>{post.title}</Link>
        </h3>
        <p>{post.excerptText}</p>
      </div>
      <footer className="post-card-footer">
        <div className="author-inline">
          <Image
            alt={post.author.avatar.alt}
            className="avatar"
            height={28}
            src={post.author.avatar.url}
            width={28}
          />
          <span>{post.author.name}</span>
        </div>
        <div className="post-card-meta">
          <span>{formatDate(post.date)}</span>
          <Link href={`/posts/${post.slug}`}>Read More</Link>
        </div>
      </footer>
    </article>
  );
}
