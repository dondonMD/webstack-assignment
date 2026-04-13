import { notFound } from "next/navigation";
import { fetchGraphQL } from "./graphql";
import {
  LATEST_POSTS_QUERY,
  POST_BY_SLUG_QUERY,
  POST_SLUGS_QUERY,
} from "./queries";
import { stripHtml } from "./utils";
import type {
  LatestPostsQuery,
  PostBySlugQuery,
  PostSlugsQuery,
  WordPressPost,
  WordPressPostSummary,
} from "@/types/wordpress";

const fallbackFeaturedImage = {
  url: "/post-fallback.svg",
  alt: "Abstract placeholder illustration.",
};

const fallbackAvatar = {
  url: "/avatar-fallback.svg",
  alt: "Default author avatar.",
};

const localFeaturedImagesBySlug: Record<string, string> = {
  "getting-started-with-next-js": "/nextjs.svg",
  "wordpress-graphql-integration": "/wpgraphql.svg",
  "tips-for-effective-content-creation": "/content.svg",
  "understanding-graphql-queries": "/queries.svg",
  "seo-best-practices": "/seo.svg",
  "the-benefits-of-headless-cms": "/headless.svg",
};

function isPublicAssetUrl(value?: string | null) {
  if (!value) {
    return false;
  }

  try {
    const url = new URL(value);
    const hostname = url.hostname.toLowerCase();

    if (hostname === "localhost" || hostname === "127.0.0.1" || hostname === "::1") {
      return false;
    }

    return true;
  } catch {
    return false;
  }
}

function getFeaturedImageUrl(post: WordPressPost) {
  if (isPublicAssetUrl(post.featuredImage?.node.sourceUrl)) {
    return post.featuredImage?.node.sourceUrl ?? fallbackFeaturedImage.url;
  }

  return localFeaturedImagesBySlug[post.slug] ?? fallbackFeaturedImage.url;
}

function getAuthorName(name?: string | null) {
  if (!name || name === "admin") {
    return "John Doe";
  }

  return name;
}

function mapPost(post: WordPressPost): WordPressPostSummary {
  const authorName = getAuthorName(post.author?.node.name);

  return {
    title: post.title,
    slug: post.slug,
    date: post.date,
    excerptText: stripHtml(post.excerpt),
    featuredImage: {
      url: getFeaturedImageUrl(post),
      alt: post.featuredImage?.node.altText || fallbackFeaturedImage.alt,
    },
    author: {
      name: authorName,
      avatar: {
        url:
          post.author?.node.name && post.author.node.name !== "admin"
            ? (post.author.node.avatar?.url ?? fallbackAvatar.url)
            : fallbackAvatar.url,
        alt: `${authorName} avatar`,
      },
    },
  };
}

export async function getLatestPosts() {
  try {
    const data = await fetchGraphQL<LatestPostsQuery, Record<string, never>>(
      LATEST_POSTS_QUERY,
    );

    return data.posts.nodes.map(mapPost);
  } catch (error) {
    console.error("Failed to fetch latest WordPress posts.", error);
    return [];
  }
}

export async function getPostBySlug(slug: string) {
  const data = await fetchGraphQL<PostBySlugQuery, { slug: string }>(
    POST_BY_SLUG_QUERY,
    { slug },
  );

  if (!data.post) {
    notFound();
  }

  const summary = mapPost(data.post);

  return {
    ...summary,
    content: data.post.content,
  };
}

export async function getPostSlugs() {
  try {
    const data = await fetchGraphQL<PostSlugsQuery, Record<string, never>>(
      POST_SLUGS_QUERY,
    );

    return data.posts.nodes.map((post) => post.slug);
  } catch (error) {
    console.error("Failed to fetch WordPress post slugs.", error);
    return [];
  }
}
