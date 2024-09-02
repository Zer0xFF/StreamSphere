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
    public $is_hidden = true;
    public $editMode = false;

    public $filter_provider_id = '';
    public $filter_category_name = '';
    public $filter_action = '';
    public $filter_is_hidden = '';

    public function render()
    {
        // Fetch all providers
        $this->providers = Provider::all();

        // Build the query for CategoryAction with filters
        $query = CategoryAction::withoutGlobalScope('hidden')->with('provider');

        if ($this->filter_provider_id)
            $query->where('provider_id', $this->filter_provider_id);

        if ($this->filter_category_name)
            $query->where('category_name', 'like', '%' . $this->filter_category_name . '%');

        if ($this->filter_action)
            $query->where('action', 'like', '%' . $this->filter_action . '%');

        if ($this->filter_is_hidden !== '')
            $query->where('is_hidden', $this->filter_is_hidden);

        $this->categoryActions = $query->get();

        return view('livewire.category-action-manager');
    }

    public function resetFields()
    {
        $this->provider_id = '';
        $this->category_id = '';
        $this->category_name = '';
        $this->action = '';
        $this->category_action_id = null;
        $this->is_hidden = true;
        $this->editMode = false;
    }

    public function addCategoryAction()
    {
        $this->validate([
            'provider_id' => 'required|exists:providers,id',
            'category_id' => 'required|string|max:255',
            'category_name' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'is_hidden' => 'boolean',
        ]);

        CategoryAction::create([
            'provider_id' => $this->provider_id,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'action' => $this->action,
            'is_hidden' => $this->is_hidden,
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
        $this->is_hidden = $categoryAction->is_hidden;
        $this->editMode = true;
    }

    public function updateCategoryAction()
    {
        $this->validate([
            'provider_id' => 'required|exists:providers,id',
            'category_id' => 'required|string|max:255',
            'category_name' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'is_hidden' => 'boolean',
        ]);

        $categoryAction = CategoryAction::findOrFail($this->category_action_id);
        $categoryAction->update([
            'provider_id' => $this->provider_id,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'action' => $this->action,
            'is_hidden' => $this->is_hidden,
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
