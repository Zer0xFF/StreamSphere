<div class="max-w-4xl mx-auto py-10">
    <form wire:submit.prevent="{{ $editMode ? 'updateDevice' : 'addDevice' }}" class="mb-6 bg-white p-6 rounded-lg shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Username</label>
                <input type="text" wire:model="username" placeholder="Username" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="providers" class="block text-gray-700 font-medium mb-2">Select Providers:</label>
                <select wire:model="provider_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="" disabled selected>Select Provider</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-6 text-right">
            <button type="submit" class="px-6 py-2 text-black bg-indigo-10 hover:bg-indigo-10 rounded-lg">
                {{ $editMode ? 'Update' : 'Add' }}
            </button>
        </div>
    </form>

    <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
        <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="px-4 py-2 text-center text-sm font-medium">Username</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Providers</th>
                <th class="px-4 py-2 text-center text-sm font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devices as $device)
                <tr class="border-t border-gray-200 hover:bg-gray-100">
                    <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $device->username }}</td>
                    <td class="px-4 py-2 text-center text-sm text-gray-700">
                    {{ $providers->find($device->provider_id)->name ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-2 text-center">
                        <button wire:click="editDevice({{ $device->id }})" class="px-4 py-2 mr-2 text-sm text-black bg-indigo-100 rounded hover:bg-indigo-100">
                            Edit
                        </button>
                        <button wire:click="deleteDevice({{ $device->id }})" class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                            Delete
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
