<div class="max-w-4xl mx-auto py-10">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Category Filters</h2>

    <!-- Form for adding and editing category filters -->
    <form wire:submit.prevent="{{ $editMode ? 'updateCategoryFilter' : 'addCategoryFilter' }}" class="mb-6 bg-white p-6 rounded-lg shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Provider</label>
                <select wire:model="provider_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select Provider</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Category</label>
                <select wire:model="action" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select Category</option>
                    <option value="vod">VOD</option>
                    <option value="series">Series</option>
                    <option value="live">Live</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Inclusion Pattern</label>
                <input type="text" wire:model="inclusion_pattern" placeholder="Inclusion Pattern" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Exclusion Pattern</label>
                <input type="text" wire:model="exclusion_pattern" placeholder="Exclusion Pattern" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="mt-6 text-right">
            <button type="submit" class="px-6 py-2 text-black bg-indigo-50 hover:bg-blue-700 rounded-lg">
                {{ $editMode ? 'Update' : 'Add' }}
            </button>
        </div>
    </form>

    <!-- Display the list of category filters -->
    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
        <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="px-4 py-2 text-left text-sm font-medium">Provider</th>
                <th class="px-4 py-2 text-left text-sm font-medium">Category</th>
                <th class="px-4 py-2 text-left text-sm font-medium">Inclusion Pattern</th>
                <th class="px-4 py-2 text-left text-sm font-medium">Exclusion Pattern</th>
                <th class="px-4 py-2 text-right text-sm font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryFilters as $filter)
            <tr class="border-t border-gray-200 hover:bg-gray-100">
                <td class="px-4 py-2 text-sm text-gray-700">{{ $filter->provider->name }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ ucfirst($filter->action) }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ $filter->inclusion_pattern }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ $filter->exclusion_pattern }}</td>
                <td class="px-4 py-2 text-right">
                    <button wire:click="editCategoryFilter({{ $filter->id }})" class="px-4 py-2 mr-2 text-sm text-black bg-indigo-50 rounded hover:bg-indigo-100">
                        Edit
                    </button>
                    <button wire:click="deleteCategoryFilter({{ $filter->id }})" class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                        Delete
                    </button>
                    <button wire:click="updateCategoryActions({{ $filter->id }})" class="px-4 py-2 text-sm text-black bg-green-500 rounded hover:bg-green-600">
                        Update
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
