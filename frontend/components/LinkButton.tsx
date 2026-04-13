import Link from "next/link";

type LinkButtonProps = {
  href: string;
  variant: "primary" | "secondary" | "ghost";
  children: React.ReactNode;
};

export default function LinkButton({
  href,
  variant,
  children,
}: LinkButtonProps) {
  return (
    <Link className={`button button-${variant}`} href={href}>
      {children}
    </Link>
  );
}
