<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_view_products(): void
    {
        $this->postJson('/api/v1/auth/register', ['name' => 'Dara', 'email' => 'dara@example.com', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertCreated()->assertJsonPath('success', true);
        Category::create(['name' => 'Phones', 'slug' => 'phones']);
        $this->getJson('/api/v1/products')->assertOk()->assertJsonPath('success', true);
    }

    public function test_customer_cannot_create_category(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/categories', ['name' => 'Blocked'])
            ->assertForbidden();
    }
}