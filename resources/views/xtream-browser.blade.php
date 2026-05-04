<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Xtream Browser') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="xtreamBrowser()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                        <select x-model="providerId" @change="reloadCategories()" class="w-full border-gray-300 rounded-lg">
                            <option value="">Select provider</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select x-model="selectedCategory" class="w-full border-gray-300 rounded-lg">
                            <option value="">All categories</option>
                            <template x-for="category in categories" :key="category.category_id">
                                <option :value="category.category_id" x-text="category.category_name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="flex gap-2">
                            <input x-model="searchQuery" @keydown.enter.prevent="search()" type="text" placeholder="Search channels, shows, movies" class="w-full border-gray-300 rounded-lg" />
                            <button @click="search()" class="px-4 py-2 rounded-lg bg-indigo-600 text-white">Go</button>
                            <button @click="refreshCache()" :disabled="loading || !providerId" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 border border-gray-300 disabled:opacity-50">Refresh cache</button>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-6">
                        <button @click="changeTab('live')" :class="tabClass('live')" class="py-2 px-1 border-b-2 text-sm font-medium">Channels</button>
                        <button @click="changeTab('series')" :class="tabClass('series')" class="py-2 px-1 border-b-2 text-sm font-medium">TV Shows</button>
                        <button @click="changeTab('movie')" :class="tabClass('movie')" class="py-2 px-1 border-b-2 text-sm font-medium">Movies</button>
                    </nav>
                </div>

                <div class="space-y-2 max-h-[480px] overflow-auto">
                    <template x-if="loading">
                        <p class="text-sm text-gray-500">Loading...</p>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <p class="text-sm text-gray-500">No results found.</p>
                    </template>
                    <template x-for="item in items" :key="`${item.stream_id}-${item.name}`">
                        <div class="border rounded-md p-3 text-sm text-gray-800 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium" x-text="item.name"></p>
                                <p class="text-xs text-gray-500" x-text="item.category_name ?? 'Unknown category'"></p>
                            </div>
                            <button @click="openLightbox(item)" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs">Start stream</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div x-show="lightboxOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4" @keydown.escape.window="closeLightbox()" style="display:none;">
            <div class="bg-white w-full max-w-4xl rounded-lg shadow-xl overflow-hidden">
                <div class="flex items-center justify-between border-b px-4 py-3">
                    <h3 class="font-semibold text-gray-900" x-text="currentItem?.name ?? 'Stream preview'"></h3>
                    <button @click="closeLightbox()" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-4">
                    <template x-if="currentStreamUrl">
                        <video :src="currentStreamUrl" controls autoplay class="w-full rounded bg-black h-[60vh]"></video>
                    </template>
                    <template x-if="!currentStreamUrl">
                        <p class="text-sm text-gray-500">Streaming preview is only available for channels and movies.</p>
                    </template>
                </div>
            </div>
    </div>

    <script>
        function xtreamBrowser() {
            return {
                providerId: '',
                activeTab: 'live',
                categories: [],
                selectedCategory: '',
                searchQuery: '',
                items: [],
                loading: false,
                refreshing: false,
                lightboxOpen: false,
                currentItem: null,
                currentStreamUrl: '',
                providerConfigs: @json($providers->keyBy('id')->map(fn($provider) => [
                    'portal_url' => rtrim($provider->portal_url, '/'),
                    'username' => $provider->username,
                    'password' => $provider->password,
                ])),

                init() {},
                tabClass(tab) {
                    return this.activeTab === tab
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                },
                async changeTab(tab) {
                    this.activeTab = tab;
                    await this.reloadCategories();
                    if (this.searchQuery.trim() !== '') {
                        await this.search();
                    }
                },

                async refreshCache() {
                    if (!this.providerId) return;

                    this.refreshing = true;
                    await fetch(`/xtream-browser/refresh-cache?provider_id=${this.providerId}&type=${this.activeTab}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '',
                        },
                    });

                    await this.reloadCategories();
                    if (this.searchQuery.trim() !== '') {
                        await this.search();
                    }
                    this.refreshing = false;
                },
                async reloadCategories() {
                    this.categories = [];
                    this.selectedCategory = '';
                    this.items = [];
                    if (!this.providerId) return;

                    const res = await fetch(`/xtream-browser/categories?provider_id=${this.providerId}&type=${this.activeTab}`);
                    const json = await res.json();
                    this.categories = json.data ?? [];
                },
                streamUrl(item) {
                    if (!this.providerId || !item?.stream_id) {
                        return '';
                    }

                    const config = this.providerConfigs[this.providerId];

                    if (!config) {
                        return '';
                    }

                    if (this.activeTab === 'live') {
                        return `${config.portal_url}/live/${config.username}/${config.password}/${item.stream_id}.m3u8`;
                    }

                    if (this.activeTab === 'movie') {
                        return `${config.portal_url}/movie/${config.username}/${config.password}/${item.stream_id}.mp4`;
                    }

                    return '';
                },
                openLightbox(item) {
                    this.currentItem = item;
                    this.currentStreamUrl = this.streamUrl(item);
                    this.lightboxOpen = true;
                },
                closeLightbox() {
                    this.lightboxOpen = false;
                    this.currentItem = null;
                    this.currentStreamUrl = '';
                },
                async search() {
                    if (!this.providerId || this.searchQuery.trim() === '') {
                        this.items = [];
                        return;
                    }

                    this.loading = true;
                    const params = new URLSearchParams({
                        provider_id: this.providerId,
                        type: this.activeTab,
                        q: this.searchQuery,
                    });

                    if (this.selectedCategory) {
                        params.append('category_id', this.selectedCategory);
                    }

                    const res = await fetch(`/xtream-browser/search?${params.toString()}`);
                    const json = await res.json();
                    this.items = json.data ?? [];
                    this.loading = false;
                },
            }
        }
    </script>
</x-app-layout>
