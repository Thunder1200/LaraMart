<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Category::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');
        $name = (string) $this->input('name', '');

        $this->merge([
            'parent_id' => $this->input('parent_id') ?: null,
            'slug' => filled($slug) ? Str::slug((string) $slug) : Str::slug($name),
            'sort' => $this->input('sort') ?? 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:categories,slug'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'sort' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
