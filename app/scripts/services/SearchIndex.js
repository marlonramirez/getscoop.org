export default class SearchIndex {
  async load(url, signal) {
    const response = await fetch(url, { signal });
    if (!response.ok) throw new Error(`Search index returned HTTP ${response.status}`);
    const data = await response.json();
    if (!Array.isArray(data)) throw new Error('Invalid documentation search index');
    if (typeof window.Fuse !== 'function') throw new Error('Fuse is unavailable');
    return new window.Fuse(data, {
      keys: [{ name: 'title', weight: .7 }, { name: 'content', weight: .2 }],
      threshold: .2,
      ignoreLocation: true,
      distance: 1000,
      includeScore: true
    });
  }
}
