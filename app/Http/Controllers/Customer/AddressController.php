<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Addresses\CreateAddressAction;
use App\Actions\Addresses\DeleteAddressAction;
use App\Actions\Addresses\SetDefaultAddressAction;
use App\Actions\Addresses\UpdateAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreAddressRequest;
use App\Http\Requests\Customer\UpdateAddressRequest;
use App\Models\Address;
use App\Support\TurkeyCities;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        return view('customer.addresses.index', [
            'addresses' => $addresses,
            'cities' => TurkeyCities::all(),
        ]);
    }

    public function store(StoreAddressRequest $request, CreateAddressAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

        return redirect()
            ->route('customer.addresses.index')
            ->with('status', 'Adres başarıyla eklendi.');
    }

    public function edit(Address $address): View
    {
        Gate::authorize('update', $address);

        return view('customer.addresses.edit', [
            'address' => $address,
            'cities' => TurkeyCities::all(),
        ]);
    }

    public function update(
        UpdateAddressRequest $request,
        Address $address,
        UpdateAddressAction $action
    ): RedirectResponse {
        $action->execute($address, $request->validated());

        return redirect()
            ->route('customer.addresses.index')
            ->with('status', 'Adres başarıyla güncellendi.');
    }

    public function setDefault(Address $address, SetDefaultAddressAction $action): RedirectResponse
    {
        Gate::authorize('update', $address);

        $action->execute($address);

        return redirect()
            ->route('customer.addresses.index')
            ->with('status', 'Varsayılan teslimat adresi güncellendi.');
    }

    public function destroy(Address $address, DeleteAddressAction $action): RedirectResponse
    {
        Gate::authorize('delete', $address);

        $action->execute($address);

        return redirect()
            ->route('customer.addresses.index')
            ->with('status', 'Adres silindi.');
    }
}
