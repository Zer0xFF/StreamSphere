<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CategoryAction;
use App\Models\CategoryFilter;
use App\Models\Provider;

class CategoryFilterManager extends Component
{
    public $providers;
    public $categoryFilters;

    // Form fields
    public $provider_id = '';
    public $action = '';
    public $inclusion_pattern = '';
    public $exclusion_pattern = '';
    public $filter_id;
    public $editMode = false;

    public function render()
    {
        $this->providers = Provider::all();
        $this->categoryFilters = CategoryFilter::with('provider')->get();

        return view('livewire.category-filter-manager');
    }

    public function resetFields()
    {
        $this->provider_id = '';
        $this->action = '';
        $this->inclusion_pattern = '';
        $this->exclusion_pattern = '';
        $this->filter_id = null;
        $this->editMode = false;
    }

    public function addCategoryFilter()
    {
        $this->validate([
            'provider_id' => 'required|exists:providers,id',
            'action' => 'required|in:vod,series,live',
            'inclusion_pattern' => 'nullable|string|max:255',
            'exclusion_pattern' => 'nullable|string|max:255',
        ]);

        CategoryFilter::create([
            'provider_id' => $this->provider_id,
            'action' => $this->action,
            'inclusion_pattern' => $this->inclusion_pattern,
            'exclusion_pattern' => $this->exclusion_pattern,
        ]);

        session()->flash('message', 'Category Filter added successfully.');

        $this->resetFields();
    }

    public function editCategoryFilter($id)
    {
        $filter = CategoryFilter::findOrFail($id);
        $this->filter_id = $filter->id;
        $this->provider_id = $filter->provider_id;
        $this->action = $filter->action;
        $this->inclusion_pattern = $filter->inclusion_pattern;
        $this->exclusion_pattern = $filter->exclusion_pattern;
        $this->editMode = true;
    }

    public function updateCategoryFilter()
    {
        $this->validate([
            'provider_id' => 'required|exists:providers,id',
            'action' => 'required|in:vod,series,live',
            'inclusion_pattern' => 'nullable|string|max:255',
            'exclusion_pattern' => 'nullable|string|max:255',
        ]);

        $filter = CategoryFilter::findOrFail($this->filter_id);
        $filter->update([
            'provider_id' => $this->provider_id,
            'action' => $this->action,
            'inclusion_pattern' => $this->inclusion_pattern,
            'exclusion_pattern' => $this->exclusion_pattern,
        ]);

        session()->flash('message', 'Category Filter updated successfully.');

        $this->resetFields();
    }

    public function deleteCategoryFilter($id)
    {
        $filter = CategoryFilter::findOrFail($id);
        $filter->delete();

        session()->flash('message', 'Category Filter deleted successfully.');

        $this->resetFields();
    }

    public function updateCategoryActions($filterId)
    {
        $filter = CategoryFilter::findOrFail($filterId);

        $categories = CategoryAction::where('action', $filter->category_name)
            ->where('provider_id', $filter->provider_id)
            ->get();

        foreach($categories as $category)
        {
            $categoryName = $category->category_name;
            $isHidden = preg_match($filter->exclusion_pattern, $categoryName) || !preg_match($filter->inclusion_pattern, $categoryName);

            $category->is_hidden = $isHidden;
            $category->save();
        }

        session()->flash('message', 'Category Actions updated successfully based on filters.');
    }
}
