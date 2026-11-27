<?php

namespace App\Livewire\Storefront;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SearchBar extends Component
{
    public string $query = '';

    public bool $showSuggestions = false;

    public function mount(): void
    {
        $this->query = (string) request()->query('q', '');
    }

    public function updatedQuery(): void
    {
        $this->showSuggestions = mb_strlen(trim($this->query)) >= 2;
    }

    public function search(): void
    {
        $term = trim($this->query);

        $this->redirect(route('search', $term !== '' ? ['q' => $term] : []));
    }

    /**
     * @return array{products: Collection<int, Product>, brands: Collection<int, Brand>}
     */
    #[Computed]
    public function suggestions(): array
    {
        $term = trim($this->query);

        if (mb_strlen($term) < 2) {
            return [
                'products' => collect(),
                'brands' => collect(),
            ];
        }

        $products = Product::query()
            ->active()
            ->with(['brand', 'variants', 'media'])
            ->where(function (Builder $builder) use ($term): void {
                $builder
                    ->where('name', 'like', "%{$term}%")
                    ->orWhereHas('brand', fn (Builder $brandQuery) => $brandQuery->where('name', 'like', "%{$term}%"));
            })
            ->limit(5)
            ->get();

        $brands = Brand::query()
            ->where('name', 'like', "%{$term}%")
            ->limit(4)
            ->get();

        return [
            'products' => $products,
            'brands' => $brands,
        ];
    }

    public function render(): View
    {
        return view('livewire.storefront.search-bar');
    }
}
