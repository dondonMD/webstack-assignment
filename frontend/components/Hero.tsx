import Image from "next/image";
import LinkButton from "./LinkButton";

export default function Hero({ priorityImage = true }: { priorityImage?: boolean }) {
  return (
    <section className="hero-section" id="about">
      <div className="shell hero-shell">
        <div className="hero-copy">
          <p className="eyebrow">Welcome to MyBlog</p>
          <h1>Insights &amp; Stories Powered by WordPress &amp; Next.js</h1>
          <p>
            A lightweight editorial homepage built twice from one WordPress
            source of truth: once with Elementor and once with WPGraphQL and
            Next.js.
          </p>
          <LinkButton href="#posts" variant="primary">
            Read More
          </LinkButton>
        </div>
        <div className="hero-art">
          <Image
            alt="Laptop illustration showing WordPress, GraphQL, and Next.js logos."
            fill
            priority={priorityImage}
            sizes="(max-width: 1024px) 100vw, 520px"
            src="/hero-laptop.svg"
          />
        </div>
      </div>
    </section>
  );
}
