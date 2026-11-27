<?php

namespace App\Actions\Sellers;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Str;

class ApplyAsSellerAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): Seller
    {
        $baseSlug = Str::slug((string) $data['store_name']);
        $slug = $baseSlug;
        $counter = 1;

        while (Seller::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return Seller::create([
            'user_id' => $user->id,
            'store_name' => $data['store_name'],
            'slug' => $slug,
            'tax_number' => $data['tax_number'],
            'iban' => $data['iban'],
            'status' => 'pending',
            'commission_rate' => null,
        ]);
    }
}
