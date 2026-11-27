<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        return $category !== null && ($this->user()?->can('update', $category) ?? false);
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
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                Rule::notIn([$category->id]),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($category->id),
            ],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'sort' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
