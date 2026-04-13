export default function LoadingGrid() {
  return (
    <div className="posts-grid" aria-busy="true" aria-live="polite">
      {Array.from({ length: 6 }, (_, index) => (
        <div className="post-card loading-card" key={index}>
          <div className="skeleton skeleton-card-image" />
          <div className="post-card-body">
            <div className="skeleton skeleton-card-title" />
            <div className="skeleton skeleton-card-copy" />
            <div className="skeleton skeleton-card-copy short" />
          </div>
          <div className="post-card-footer">
            <div className="skeleton skeleton-inline" />
            <div className="skeleton skeleton-inline short" />
          </div>
        </div>
      ))}
    </div>
  );
}
