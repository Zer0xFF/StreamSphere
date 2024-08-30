<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CategoryAction;
use App\Models\Provider;

class CategoryActionManager extends Component
{
    public $providers;
    public $categoryActions;
    public $provider_id = '';
    public $category_id;
    public $category_name;
    public $action;
    public $category_action_id;
    public $editMode = false;

    public function render()
    {
        $this->providers = Provider::all();
        $this->categoryActions = CategoryAction::with('provider')->get();
        return view('livewire.category-action-manager');
    }

    public function resetFields()
    {
        $this->provider_id = '';
        $this->category_id = '';
        $this->category_name = '';
        $this->action = '';
        $this->category_action_id = null;
        $this->editMode = false;
    }

    public function addCategoryAction()
    {
        $this->validate([
            'provider_id' => 'required|exists:providers,id',
            'category_id' => 'required|string|max:255',
            'category_name' => 'required|string|max:255',
            'action' => 'required|string|max:255',
        ]);

        CategoryAction::create([
            'provider_id' => $this->provider_id,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'action' => $this->action,
        ]);

        session()->flash('message', 'Category Action added successfully.');

        $this->resetFields();
    }

    public function editCategoryAction($id)
    {
        $categoryAction = CategoryAction::findOrFail($id);
        $this->category_action_id = $categoryAction->id;
        $this->provider_id = $categoryAction->provider_id;
        $this->category_id = $categoryAction->category_id;
        $this->category_name = $categoryAction->category_name;
        $this->action = $categoryAction->action;
        $this->editMode = true;
    }

    public function updateCategoryAction()
    {
        $this->validate([
            'provider_id' => 'required|exists:providers,id',
            'category_id' => 'required|string|max:255',
            'category_name' => 'required|string|max:255',
            'action' => 'required|string|max:255',
        ]);

        $categoryAction = CategoryAction::findOrFail($this->category_action_id);
        $categoryAction->update([
            'provider_id' => $this->provider_id,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'action' => $this->action,
        ]);

        session()->flash('message', 'Category Action updated successfully.');

        $this->resetFields();
    }

    public function deleteCategoryAction($id)
    {
        $categoryAction = CategoryAction::findOrFail($id);
        $categoryAction->delete();

        session()->flash('message', 'Category Action deleted successfully.');

        $this->resetFields();
    }
}
