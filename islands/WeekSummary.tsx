import { useEffect, useState } from "react";

/**
 * Hydrated widget body. In the real extension this fetches `/time/summary`
 * (the manifest's dataEndpoint) via the base's API wrapper; the smoke uses a
 * static placeholder to prove the island hydrates when rendered through the
 * contract's widget slot.
 */
export default function WeekSummary() {
  const [hours, setHours] = useState<number | null>(null);
  useEffect(() => {
    // Placeholder: real impl calls the panel API base + dataEndpoint.
    setHours(12.5);
  }, []);
  return (
    <p className="widget__metric">
      {hours === null ? "…" : `${hours.toLocaleString("de-DE")} h`}
    </p>
  );
}
