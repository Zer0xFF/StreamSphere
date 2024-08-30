<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DeviceUser;
use App\Models\Provider;

class DeviceManager extends Component
{
    public $devices;
    public $providers;
    public $username, $provider_id = '';
    public $editMode = false;
    public $deviceId;

    public function render()
    {
        $this->devices = DeviceUser::all();
        $this->providers = Provider::all();
        return view('livewire.device-manager');
    }

    public function addDevice()
    {
        DeviceUser::create([
            'username' => $this->username,
            'provider_id' => $this->provider_id,
        ]);

        $this->resetInputFields();
    }

    public function editDevice($id)
    {
        $device = DeviceUser::findOrFail($id);
        $this->deviceId = $id;
        $this->username = $device->username;
        $this->provider_id = $device->provider_id;
        $this->editMode = true;
    }

    public function updateDevice()
    {
        $device = DeviceUser::findOrFail($this->deviceId);
        $device->update([
            'username' => $this->username,
            'provider_id' => $this->provider_id,
        ]);

        $this->resetInputFields();
        $this->editMode = false;
    }

    public function deleteDevice($id)
    {
        DeviceUser::findOrFail($id)->delete();
    }

    private function resetInputFields()
    {
        $this->username = '';
        $this->provider_id = '';
    }
}
