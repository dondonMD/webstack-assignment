"use client";

import Header from "@/components/Header";
import LinkButton from "@/components/LinkButton";

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <main>
      <Header />
      <section className="error-state shell">
        <p className="eyebrow">GraphQL Error</p>
        <h1>Unable to load content.</h1>
        <p>
          The frontend only reads from WPGraphQL. Check the GraphQL endpoint,
          WordPress availability, and plugin setup, then retry the request.
        </p>
        <p className="error-details">{error.message}</p>
        <div className="error-actions">
          <button className="button button-primary" onClick={reset} type="button">
            Try Again
          </button>
          <LinkButton href="/" variant="secondary">
            Back to Homepage
          </LinkButton>
        </div>
      </section>
    </main>
  );
}
