<?php

namespace App\Actions;

use App\Enums\SellerStatus;
use App\Mail\SellerApprovedMail;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SellerManagementAction
{
    public function apply(User $user, array $data): Seller
    {
        $slug = Str::slug($data['store_name']) . '-' . $user->id;

        return Seller::updateOrCreate(
            ['user_id' => $user->id],
            [
                'store_name' => $data['store_name'],
                'slug' => $slug,
                'tax_number' => $data['tax_number'],
                'iban' => $data['iban'],
                'status' => SellerStatus::Pending,
                'commission_rate' => '10.00',
            ]
        );
    }

    public function approve(Seller $seller): Seller
    {
        $seller->update(['status' => SellerStatus::Approved]);

        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        if (! $seller->user->hasRole('seller'))
        {
            $seller->user->assignRole($sellerRole);
        }

        Mail::to($seller->user->email)->queue(new SellerApprovedMail($seller));

        return $seller->fresh();
    }

    public function reject(Seller $seller): Seller
    {
        $seller->update(['status' => SellerStatus::Rejected]);

        return $seller->fresh();
    }
}

