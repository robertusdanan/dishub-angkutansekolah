
export const PROXY = '/api/supabase-proxy';

export async function proxyGet(table, extra = '') {
  const url = `${PROXY}?table=${encodeURIComponent(table)}${extra}`;
  const res = await fetch(url);
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.error || 'HTTP ' + res.status);
  }
  return res.json();
}
