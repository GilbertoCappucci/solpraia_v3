<?php

namespace App\Livewire\Menu;

use App\Services\MenuService;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class MenuCategoryFilter extends Component
{
    #[Reactive]
    public $activeMenuId;

    #[Reactive]
    public $parentCategories;

    #[Reactive]
    public $childCategories;

    public $selectedParentCategoryId = null;
    public $selectedChildCategoryId = null;
    public $showFavoritesOnly = false;

    protected $menuService;

    public function boot(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function selectParentCategory($categoryId)
    {
        $this->selectedParentCategoryId = $categoryId === $this->selectedParentCategoryId ? null : $categoryId;
        $this->selectedChildCategoryId = null;
        $this->showFavoritesOnly = false;
        
        $this->dispatch('category-filter-changed', [
            'parentCategoryId' => $this->selectedParentCategoryId,
            'childCategoryId' => null,
            'showFavoritesOnly' => false,
        ]);
    }

    public function selectFavorites()
    {
        $this->showFavoritesOnly = !$this->showFavoritesOnly;
        $this->selectedParentCategoryId = null;
        $this->selectedChildCategoryId = null;
        
        $this->dispatch('category-filter-changed', [
            'parentCategoryId' => null,
            'childCategoryId' => null,
            'showFavoritesOnly' => $this->showFavoritesOnly,
        ]);
    }

    public function selectChildCategory($categoryId)
    {
        $this->selectedChildCategoryId = $categoryId === $this->selectedChildCategoryId ? null : $categoryId;
        
        $this->dispatch('category-filter-changed', [
            'parentCategoryId' => $this->selectedParentCategoryId,
            'childCategoryId' => $this->selectedChildCategoryId,
            'showFavoritesOnly' => false,
        ]);
    }

    public function render()
    {
        return view('livewire.menu.menu-category-filter');
    }
}
