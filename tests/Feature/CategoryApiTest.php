<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories_with_pagination_and_search(): void
    {
        Category::factory()->create(['name' => 'Smartphones', 'status' => 'active']);
        Category::factory()->create(['name' => 'Laptops', 'status' => 'active']);
        Category::factory()->create(['name' => 'Home Appliances', 'status' => 'active']);

        $response = $this->getJson('/api/v1/categories?search=Smart');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Smartphones');
    }

    public function test_can_view_single_category_and_its_products(): void
    {
        $category = Category::factory()->create(['name' => 'Electronics']);
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $category->id);

        $prodResponse = $this->getJson("/api/v1/categories/{$category->id}/products");
        $prodResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/categories', [
            'name' => 'Tablets & E-Readers',
            'description' => 'Compact touch devices',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Tablets & E-Readers');

        $this->assertDatabaseHas('categories', ['name' => 'Tablets & E-Readers']);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Old Category']);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Updated Category',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Category');
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_non_admin_cannot_create_or_modify_category(): void
    {
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create();

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/admin/categories', ['name' => 'Hacked Category'])
            ->assertForbidden();

        $this->actingAs($customer, 'sanctum')
            ->putJson("/api/v1/admin/categories/{$category->id}", ['name' => 'Hacked Category'])
            ->assertForbidden();

        $this->actingAs($customer, 'sanctum')
            ->deleteJson("/api/v1/admin/categories/{$category->id}")
            ->assertForbidden();
    }
}
