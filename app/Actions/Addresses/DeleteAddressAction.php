<?php

namespace App\Actions\Addresses;

use App\Models\Address;
use Illuminate\Support\Facades\DB;

class DeleteAddressAction
{
    public function execute(Address $address): void
    {
        DB::transaction(function () use ($address): void {
            $wasDefault = $address->is_default;
            $userId = $address->user_id;

            $address->delete();

            if ($wasDefault) {
                Address::query()
                    ->where('user_id', $userId)
                    ->latest('id')
                    ->first()
                    ?->update(['is_default' => true]);
            }
        });
    }
}
