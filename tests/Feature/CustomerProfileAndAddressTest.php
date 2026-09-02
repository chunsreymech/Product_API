<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileAndAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_and_update_profile(): void
    {
        $customer = User::factory()->customer()->create(['name' => 'Original Dara']);

        $getRes = $this->actingAs($customer, 'sanctum')->getJson('/api/v1/customer/profile');
        $getRes->assertOk()->assertJsonPath('data.name', 'Original Dara');

        $updRes = $this->actingAs($customer, 'sanctum')->putJson('/api/v1/customer/profile', [
            'name' => 'Updated Dara',
        ]);
        $updRes->assertOk()->assertJsonPath('data.name', 'Updated Dara');
    }

    public function test_customer_can_manage_addresses(): void
    {
        $customer = User::factory()->customer()->create();

        // Create Address
        $res = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/customer/addresses', [
            'label' => 'Office',
            'address' => 'Vattanac Capital Tower, Level 18',
            'city' => 'Phnom Penh',
            'phone' => '+855 23 999 888',
            'is_default' => true,
        ]);

        $res->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.label', 'Office')
            ->assertJsonPath('data.is_default', true);

        $addressId = $res->json('data.id');

        // Update Address
        $upd = $this->actingAs($customer, 'sanctum')->putJson("/api/v1/customer/addresses/{$addressId}", [
            'label' => 'Headquarters',
        ]);
        $upd->assertOk()->assertJsonPath('data.label', 'Headquarters');

        // Delete Address
        $del = $this->actingAs($customer, 'sanctum')->deleteJson("/api/v1/customer/addresses/{$addressId}");
        $del->assertNoContent();
    }

    public function test_customer_cannot_access_other_customers_address(): void
    {
        $user1 = User::factory()->customer()->create();
        $user2 = User::factory()->customer()->create();
        $address = Address::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2, 'sanctum')
            ->getJson("/api/v1/customer/addresses/{$address->id}")
            ->assertForbidden();
    }
}
