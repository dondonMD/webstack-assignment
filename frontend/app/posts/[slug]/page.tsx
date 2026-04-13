import type { Metadata } from "next";
import Image from "next/image";
import Header from "@/components/Header";
import LinkButton from "@/components/LinkButton";
import { getPostBySlug } from "@/lib/wordpress";
import { formatDate } from "@/lib/utils";

export const revalidate = 60;

type PostPageProps = {
  params: Promise<{
    slug: string;
  }>;
};

export async function generateMetadata({
  params,
}: PostPageProps): Promise<Metadata> {
  const { slug } = await params;
  const post = await getPostBySlug(slug);

  return {
    title: `${post.title} | MyBlog`,
    description: post.excerptText,
  };
}

export default async function PostPage({ params }: PostPageProps) {
  const { slug } = await params;
  const post = await getPostBySlug(slug);

  return (
    <main>
      <Header />
      <article className="post-page">
        <div className="shell post-page-shell">
          <LinkButton href="/" variant="ghost">
            Back to Latest Posts
          </LinkButton>
          <div className="post-header">
            <p className="eyebrow">Article</p>
            <h1>{post.title}</h1>
            <div className="post-meta">
              <div className="author-inline">
                <Image
                  alt={post.author.avatar.alt}
                  className="avatar"
                  height={48}
                  src={post.author.avatar.url}
                  width={48}
                />
                <span>{post.author.name}</span>
              </div>
              <span>{formatDate(post.date)}</span>
            </div>
          </div>
          <div className="post-feature">
            <Image
              alt={post.featuredImage.alt}
              fill
              priority
              sizes="(max-width: 1024px) 100vw, 960px"
              src={post.featuredImage.url}
            />
          </div>
          <div
            className="post-content"
            dangerouslySetInnerHTML={{ __html: post.content }}
          />
        </div>
      </article>
    </main>
  );
}
