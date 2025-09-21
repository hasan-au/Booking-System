{{-- resources/views/components/iconify-picker-boot.blade.php --}}
@once
<script>
(function () {
  function register() {
    window.iconifyPicker = function ({ entangled, limit, prefixes }) {
      return {
        entangled,            // string like "mdi:home" (can be '' or null initially)
        query: '',
        results: [],
        loading: false,
        open: false,
        start: 0,
        limit: Number(limit ?? 64),
        hasMore: false,
        prefixes: prefixes ?? null,
        color: 'currentColor', // used by <img> fallback

        supportsWebComponent() {
          return !!(window.customElements && window.customElements.get('iconify-icon'));
        },
        iconUrl(name) {
          if (!name) return '';
          // color works for monotone icons
          const params = new URLSearchParams({ color: this.color });
          return `https://api.iconify.design/${name}.svg?${params.toString()}`;
        },

        clearSelection() { this.entangled = ''; },
        select(name) { this.entangled = name; this.open = false; },

        async search(reset = true) {
          const q = this.query.trim();
          if (q.length < 2) {
            if (reset) { this.results = []; this.hasMore = false; this.start = 0; }
            return;
          }

          this.loading = true;
          if (reset) { this.start = 0; this.results = []; }

          const params = new URLSearchParams({
            query: q,
            limit: String(this.limit),
            start: String(this.start),
          });
          if (this.prefixes) params.set('prefixes', this.prefixes);

          try {
            const res = await fetch('https://api.iconify.design/search?' + params.toString());
            if (res.ok) {
              const json = await res.json(); // { icons, total, start, ... }
              const batch = (json.icons || []).filter(Boolean); // drop falsy (undefined)
              this.results = reset ? batch : this.results.concat(batch);

              const consumed = (json.start ?? 0) + batch.length;
              this.hasMore = consumed < (json.total ?? consumed);
              this.start = consumed;
            } else {
              this.results = []; this.hasMore = false;
            }
          } catch {
            this.results = []; this.hasMore = false;
          } finally { this.loading = false; }
        },

        loadMore() { this.search(false); },
        init() { this.$watch('query', () => this.search(true)); },
      };
    };
  }

  if (window.Alpine) register();
  else document.addEventListener('alpine:init', register);
})();
</script>
@endonce
