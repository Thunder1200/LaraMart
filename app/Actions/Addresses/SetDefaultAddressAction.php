<?php

namespace App\Actions\Addresses;

use App\Models\Address;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressAction
{
    public function execute(Address $address): Address
    {
        return DB::transaction(function () use ($address): Address {
            Address::query()
                ->where('user_id', $address->user_id)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);

            return $address->refresh();
        });
    }
}
