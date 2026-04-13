type SectionTitleProps = {
  eyebrow?: string;
  title: string;
};

export default function SectionTitle({ eyebrow, title }: SectionTitleProps) {
  return (
    <div className="section-title" id="posts">
      {eyebrow ? <p className="eyebrow">{eyebrow}</p> : null}
      <h2>{title}</h2>
    </div>
  );
}
