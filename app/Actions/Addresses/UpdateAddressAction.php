<?php

namespace App\Actions\Addresses;

use App\Models\Address;
use Illuminate\Support\Facades\DB;

class UpdateAddressAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data): Address {
            $isDefault = (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                Address::query()
                    ->where('user_id', $address->user_id)
                    ->whereKeyNot($address->id)
                    ->update(['is_default' => false]);
            }

            $address->update([
                'title' => $data['title'],
                'city' => $data['city'],
                'district' => $data['district'],
                'full_address' => $data['full_address'],
                'is_default' => $isDefault,
            ]);

            return $address->refresh();
        });
    }
}
