export type WordPressImageNode = {
  sourceUrl: string;
  altText: string | null;
};

export type WordPressAuthorNode = {
  name: string;
  avatar?: {
    url: string;
  } | null;
};

export type WordPressPost = {
  title: string;
  slug: string;
  excerpt: string;
  date: string;
  content: string;
  featuredImage?: {
    node: WordPressImageNode;
  } | null;
  author?: {
    node: WordPressAuthorNode;
  } | null;
};

export type WordPressPostSummary = {
  title: string;
  slug: string;
  excerptText: string;
  date: string;
  featuredImage: {
    url: string;
    alt: string;
  };
  author: {
    name: string;
    avatar: {
      url: string;
      alt: string;
    };
  };
};

export type LatestPostsQuery = {
  posts: {
    nodes: WordPressPost[];
  };
};

export type PostBySlugQuery = {
  post: WordPressPost | null;
};

export type PostSlugsQuery = {
  posts: {
    nodes: Array<{
      slug: string;
    }>;
  };
};
