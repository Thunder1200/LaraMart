<?php

use App\Models\Category;
use App\Models\Seller;
use App\Models\User;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'seller']);
    Role::firstOrCreate(['name' => 'customer']);
});

test('customer receives 403 when attempting to access admin panel', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $this->actingAs($customer)
        ->get('/admin')
        ->assertForbidden();

    $this->actingAs($customer)
        ->get('/admin/kategoriler')
        ->assertForbidden();
});

test('admin can access admin panel and category management', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();

    $this->actingAs($admin)
        ->get('/admin/kategoriler')
        ->assertOk();
});

test('only approved sellers can access seller panel', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $this->actingAs($customer)
        ->get('/satici')
        ->assertForbidden();

    $pendingSellerUser = User::factory()->create();
    $pendingSellerUser->assignRole('seller');
    Seller::factory()->create([
        'user_id' => $pendingSellerUser->id,
        'status' => 'pending',
    ]);

    $this->actingAs($pendingSellerUser)
        ->get('/satici')
        ->assertForbidden();

    $approvedSellerUser = User::factory()->create();
    $approvedSellerUser->assignRole('seller');
    Seller::factory()->create([
        'user_id' => $approvedSellerUser->id,
        'status' => 'approved',
    ]);

    $this->actingAs($approvedSellerUser)
        ->get('/satici')
        ->assertOk();
});

test('newly registered user automatically receives customer role', function () {
    Volt::test('auth.register')
        ->set('name', 'Yeni Müşteri')
        ->set('email', 'yeni.musteri@laramart.test')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors();

    $user = User::where('email', 'yeni.musteri@laramart.test')->firstOrFail();

    expect($user->hasRole('customer'))->toBeTrue();
});

test('admin can create, update and delete categories with subcategory support', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Oyun ve Konsol',
            'slug' => '',
            'commission_rate' => 9.50,
            'sort' => 1,
        ])
        ->assertRedirect(route('admin.categories.index'));

    $parent = Category::where('slug', 'oyun-ve-konsol')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'parent_id' => $parent->id,
            'name' => 'Oyun Kolları',
            'slug' => 'oyun-kollari',
            'commission_rate' => 11.00,
            'sort' => 2,
        ])
        ->assertRedirect(route('admin.categories.index'));

    $child = Category::where('slug', 'oyun-kollari')->firstOrFail();
    expect($child->parent_id)->toBe($parent->id);

    $this->actingAs($admin)
        ->put(route('admin.categories.update', $child), [
            'parent_id' => $parent->id,
            'name' => 'Gamepad ve Aksesuar',
            'slug' => 'gamepad-ve-aksesuar',
            'commission_rate' => 12.00,
            'sort' => 3,
        ])
        ->assertRedirect(route('admin.categories.index'));

    expect($child->refresh()->name)->toBe('Gamepad ve Aksesuar');

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $child))
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::find($child->id))->toBeNull();
});

test('mega menu renders parent and child categories from database', function () {
    $parent = Category::factory()->create([
        'parent_id' => null,
        'name' => 'Kitap ve Hobi',
        'slug' => 'kitap-ve-hobi',
        'sort' => 1,
    ]);

    Category::factory()->create([
        'parent_id' => $parent->id,
        'name' => 'Edebiyat Romanları',
        'slug' => 'edebiyat-romanlari',
        'sort' => 2,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Kitap ve Hobi')
        ->assertSee('Edebiyat Romanları');
});

test('form validation errors are displayed in Turkish', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)
        ->from(route('admin.categories.create'))
        ->post(route('admin.categories.store'), [
            'name' => '',
            'commission_rate' => '',
            'sort' => '',
        ]);

    $response->assertSessionHasErrors([
        'name' => 'Ad alanı zorunludur.',
        'commission_rate' => 'Komisyon oranı alanı zorunludur.',
    ]);
});
