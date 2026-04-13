import Link from "next/link";
import LinkButton from "./LinkButton";

const navItems = [
  { href: "/", label: "Home" },
  { href: "/#about", label: "About" },
  { href: "/#contact", label: "Contact" },
];

export default function Header() {
  return (
    <header className="site-header">
      <div className="shell header-shell">
        <Link className="site-logo" href="/">
          Logo
        </Link>
        <nav aria-label="Primary" className="site-nav">
          {navItems.map((item) => (
            <Link key={item.href} href={item.href}>
              {item.label}
            </Link>
          ))}
        </nav>
        <LinkButton href="/#contact" variant="primary">
          Get Started
        </LinkButton>
      </div>
    </header>
  );
}
