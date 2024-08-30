<div class="max-w-4xl mx-auto py-10">
    <form wire:submit.prevent="{{ $editMode ? 'updateProvider' : 'addProvider' }}" class="mb-6 bg-white p-6 rounded-lg shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Provider Name</label>
                <input type="text" wire:model="name" placeholder="Provider Name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Portal URL</label>
                <input type="text" wire:model="portal_url" placeholder="Portal URL" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Username</label>
                <input type="text" wire:model="username" placeholder="Username" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="text" wire:model="password" placeholder="Password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
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
                <th class="px-4 py-2 text-center text-sm font-medium">Name</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Portal URL</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Username</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($providers as $provider)
            <tr class="border-t border-gray-200 hover:bg-gray-100">
                <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $provider->name }}</td>
                <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $provider->portal_url }}</td>
                <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $provider->username }}</td>
                <td class="px-4 py-2 text-center">
                    <button wire:click="editProvider({{ $provider->id }})" class="px-4 py-2 mr-2 text-sm text-black bg-indigo-50 rounded hover:bg-indigo-100">
                        Edit
                    </button>
                    <button wire:click="deleteProvider({{ $provider->id }})" class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                        Delete
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
