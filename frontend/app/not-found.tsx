import Header from "@/components/Header";
import LinkButton from "@/components/LinkButton";

export default function NotFound() {
  return (
    <main>
      <Header />
      <section className="error-state shell">
        <p className="eyebrow">Not Found</p>
        <h1>That post does not exist.</h1>
        <p>
          The slug you requested was not returned by WPGraphQL. Head back to the
          homepage to browse the latest posts.
        </p>
        <LinkButton href="/" variant="primary">
          Back to Homepage
        </LinkButton>
      </section>
    </main>
  );
}
