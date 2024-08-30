<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Provider;

class ProviderManager extends Component
{
    public $providers;
    public $name, $portal_url, $username, $password;
    public $editMode = false;
    public $providerId;

    public function render()
    {
        $this->providers = Provider::all();
        return view('livewire.provider-manager');
    }

    public function addProvider()
    {
        Provider::create([
            'name' => $this->name,
            'portal_url' => $this->portal_url,
            'username' => $this->username,
            'password' => $this->password,
        ]);

        $this->resetInputFields();
    }

    public function editProvider($id)
    {
        $provider = Provider::findOrFail($id);
        $this->providerId = $id;
        $this->name = $provider->name;
        $this->portal_url = $provider->portal_url;
        $this->username = $provider->username;
        $this->password = $provider->password;
        $this->editMode = true;
    }

    public function updateProvider()
    {
        $provider = Provider::findOrFail($this->providerId);
        $provider->update([
            'name' => $this->name,
            'portal_url' => $this->portal_url,
            'username' => $this->username,
            'password' => $this->password,
        ]);

        $this->resetInputFields();
        $this->editMode = false;
    }

    public function deleteProvider($id)
    {
        Provider::findOrFail($id)->delete();
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->portal_url = '';
        $this->username = '';
        $this->password = '';
    }
}
