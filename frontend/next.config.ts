import type { NextConfig } from "next";

const DEFAULT_GRAPHQL_ENDPOINTS = [
  "http://127.0.0.1:8090/graphql",
  "http://localhost:8090/graphql",
  "http://127.0.0.1:8080/graphql",
  "http://localhost:8080/graphql",
];

type RemotePatterns = NonNullable<NonNullable<NextConfig["images"]>["remotePatterns"]>;

function getRemoteImagePatterns(): RemotePatterns {
  const graphQlEndpoints = [
    process.env.NEXT_PUBLIC_WORDPRESS_GRAPHQL_URL,
    ...DEFAULT_GRAPHQL_ENDPOINTS,
  ].filter((value): value is string => Boolean(value));

  const patterns: RemotePatterns = graphQlEndpoints.flatMap((endpoint) => {
    try {
      const url = new URL(endpoint);

      return [
        {
          protocol: url.protocol === "https:" ? "https" : "http",
          hostname: url.hostname,
          port: url.port || undefined,
          pathname: "/**",
        },
      ];
    } catch {
      return [];
    }
  });

  return [
    ...patterns,
    {
      protocol: "https",
      hostname: "secure.gravatar.com",
      pathname: "/avatar/**",
    },
    {
      protocol: "https",
      hostname: "gravatar.com",
      pathname: "/avatar/**",
    },
  ];
}

const nextConfig: NextConfig = {
  images: {
    remotePatterns: getRemoteImagePatterns(),
  },
};

export default nextConfig;
