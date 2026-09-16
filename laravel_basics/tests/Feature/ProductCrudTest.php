<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Cloud Services',
            'slug' => 'cloud-services',
            'description' => 'Scalable virtual infrastructure and object storage.',
        ]);
    }

    /**
     * Test GET /products displays the catalog index with products.
     */
    public function test_products_index_displays_product_catalog(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Enterprise Cloud Storage',
            'description' => 'Ultra resilient distributed storage tier.',
            'price' => 49.99,
        ]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Product Catalog');
        $response->assertSee('Enterprise Cloud Storage');
        $response->assertSee('Cloud Services');
        $response->assertSee('49.99');
    }

    /**
     * Test GET /products/create displays create form with category options.
     */
    public function test_product_create_page_renders_successfully(): void
    {
        $response = $this->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertSee('Add New Product');
        $response->assertSee('Cloud Services');
        $response->assertSee('create-product-form');
    }

    /**
     * Test POST /products creates a product and redirects with flash message.
     */
    public function test_product_can_be_stored_with_valid_data(): void
    {
        $payload = [
            'name' => 'Automated CI/CD Pipeline Agent',
            'category_id' => $this->category->id,
            'price' => 79.50,
            'description' => 'Fast test execution runner with caching support.',
        ];

        $response = $this->post(route('products.store'), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Automated CI/CD Pipeline Agent',
            'category_id' => $this->category->id,
            'price' => 79.50,
        ]);
    }

    /**
     * Test POST /products fails validation when required fields are missing or invalid.
     */
    public function test_product_store_validation_fails_with_invalid_data(): void
    {
        $response = $this->post(route('products.store'), [
            'name' => '',
            'category_id' => 9999, // non-existent category
            'price' => 'not-a-number',
            'description' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'category_id', 'price']);
        $this->assertDatabaseCount('products', 0);
    }

    /**
     * Test GET /products/{product} displays details using Route Model Binding.
     */
    public function test_product_show_displays_product_using_route_model_binding(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Dedicated VPN Gateway',
            'description' => 'Zero-trust network access gateway for distributed teams.',
            'price' => 120.00,
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Dedicated VPN Gateway');
        $response->assertSee('Cloud Services');
        $response->assertSee('120.00');
        $response->assertSee('Route Model Binding in Action');
    }

    /**
     * Test GET /products/{product} returns 404 if record does not exist (Route Model Binding).
     */
    public function test_product_show_returns_404_for_missing_record(): void
    {
        $response = $this->get('/products/99999');

        $response->assertStatus(404);
    }

    /**
     * Test GET /products/{product}/edit renders form with prefilled values.
     */
    public function test_product_edit_page_renders_with_existing_values(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Kubernetes Cluster Node',
            'description' => 'Managed worker node with auto-scaling.',
            'price' => 60.00,
        ]);

        $response = $this->get(route('products.edit', $product));

        $response->assertStatus(200);
        $response->assertSee('Edit Product #' . $product->id);
        $response->assertSee('Kubernetes Cluster Node');
        $response->assertSee('60.00');
    }

    /**
     * Test PUT /products/{product} updates the record using Route Model Binding.
     */
    public function test_product_can_be_updated_using_route_model_binding(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Initial Hardware Server',
            'description' => 'Entry-level server blade.',
            'price' => 300.00,
        ]);

        $response = $this->put(route('products.update', $product), [
            'name' => 'Upgraded Enterprise Server',
            'category_id' => $this->category->id,
            'price' => 450.00,
            'description' => 'Enterprise dual Xeon server blade.',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Upgraded Enterprise Server',
            'price' => 450.00,
        ]);
    }

    /**
     * Test PUT /products/{product} validation failure with negative price.
     */
    public function test_product_update_fails_validation_with_invalid_price(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Machine',
            'description' => 'Valid initial machine.',
            'price' => 100.00,
        ]);

        $response = $this->put(route('products.update', $product), [
            'name' => 'Updated Name',
            'category_id' => $this->category->id,
            'price' => -50.00, // Negative price invalid
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['price']);
    }

    /**
     * Test DELETE /products/{product} removes record using Route Model Binding.
     */
    public function test_product_can_be_deleted_using_route_model_binding(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Temporary Scratch Machine',
            'description' => 'To be deleted.',
            'price' => 15.00,
        ]);

        $response = $this->delete(route('products.destroy', $product));

        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
