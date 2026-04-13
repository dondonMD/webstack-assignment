type GraphQLResponse<T> = {
  data?: T;
  errors?: Array<{
    message: string;
  }>;
};

const DEFAULT_ENDPOINTS = [
  "http://127.0.0.1:8090/graphql",
  "http://localhost:8090/graphql",
  "http://127.0.0.1:8080/graphql",
  "http://localhost:8080/graphql",
];

function getGraphqlEndpoints() {
  const configuredEndpoint = process.env.NEXT_PUBLIC_WORDPRESS_GRAPHQL_URL;

  if (configuredEndpoint) {
    return [configuredEndpoint];
  }

  return DEFAULT_ENDPOINTS;
}

export async function fetchGraphQL<TData, TVariables extends Record<string, unknown>>(
  query: string,
  variables?: TVariables,
): Promise<TData> {
  let lastError: Error | null = null;

  for (const endpoint of getGraphqlEndpoints()) {
    try {
      const response = await fetch(endpoint, {
        signal: AbortSignal.timeout(10000),
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          query,
          variables,
        }),
        next: {
          revalidate: 60,
        },
      });

      if (!response.ok) {
        throw new Error(`GraphQL request failed with status ${response.status}.`);
      }

      const json = (await response.json()) as GraphQLResponse<TData>;

      if (json.errors?.length) {
        throw new Error(json.errors.map((error) => error.message).join(" "));
      }

      if (!json.data) {
        throw new Error("GraphQL response did not include data.");
      }

      return json.data;
    } catch (error) {
      lastError = error instanceof Error ? error : new Error("GraphQL request failed.");
    }
  }

  throw lastError ?? new Error("GraphQL request failed.");
}
