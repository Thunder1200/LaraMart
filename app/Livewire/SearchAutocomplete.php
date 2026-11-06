<?php

namespace App\Livewire;

use App\Services\CatalogService;
use Livewire\Component;

class SearchAutocomplete extends Component
{
    public string $query = '';

    public function render(CatalogService $catalogService)
    {
        $suggestions = mb_strlen(trim($this->query)) >= 2
            ? $catalogService->autocomplete($this->query)
            : collect();

        return view('livewire.search-autocomplete', [
            'suggestions' => $suggestions,
        ]);
    }
}
