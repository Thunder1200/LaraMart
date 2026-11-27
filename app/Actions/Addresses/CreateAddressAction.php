<?php

namespace App\Actions\Addresses;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateAddressAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data): Address {
            $shouldBeDefault = (bool) ($data['is_default'] ?? false) || ! $user->addresses()->exists();

            if ($shouldBeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                'title' => $data['title'],
                'city' => $data['city'],
                'district' => $data['district'],
                'full_address' => $data['full_address'],
                'is_default' => $shouldBeDefault,
            ]);
        });
    }
}
