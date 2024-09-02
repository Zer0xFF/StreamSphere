<div class="max-w-4xl mx-auto py-10">
    <form wire:submit.prevent="{{ $editMode ? 'updateCategoryAction' : 'addCategoryAction' }}" class="mb-6 bg-white p-6 rounded-lg shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Provider</label>
                <select wire:model="provider_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="" disabled selected>Select Provider</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Category ID</label>
                <input type="text" wire:model="category_id" placeholder="Category ID" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Category Name</label>
                <input type="text" wire:model="category_name" placeholder="Category Name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Action</label>
                <input type="text" wire:model="action" placeholder="Action" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Is Hidden</label>
                <input type="checkbox" wire:model="is_hidden" class="form-checkbox h-5 w-5 text-blue-600">
            </div>
        </div>
        <div class="mt-6 text-right">
            <button type="submit" class="px-6 py-2 text-black bg-indigo-50 hover:bg-blue-700 rounded-lg">
                {{ $editMode ? 'Update' : 'Add' }}
            </button>
        </div>
    </form>

    <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
        <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="px-4 py-2 text-center text-sm font-medium">Provider</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Category ID</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Category Name</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Action</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Action</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Is Hidden</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryActions as $categoryAction)
            <tr class="border-t border-gray-200 hover:bg-gray-100">
                <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $categoryAction->provider->name }}</td>
                <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $categoryAction->category_id }}</td>
                <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $categoryAction->category_name }}</td>
                <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $categoryAction->action }}</td>
                <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $categoryAction->is_hidden ? 'Hidden' : 'Visible' }}</td>
                <td class="px-4 py-2 text-center">
                    <button wire:click="editCategoryAction({{ $categoryAction->id }})" class="px-4 py-2 mr-2 text-sm text-black bg-indigo-50 rounded hover:bg-indigo-100">
                        Edit
                    </button>
                    <button wire:click="deleteCategoryAction({{ $categoryAction->id }})" class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                        Delete
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
