<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomImportExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Test direct mapped data batch import (add_new mode).
     */
    public function test_direct_import_creates_new_products(): void
    {
        $payload = [
            'import_mode' => 'add_new',
            'rows' => [
                [
                    'name' => 'Logitech Wireless Mouse',
                    'price' => 29.99,
                    'stock' => 15,
                    'status' => 'active',
                ],
                [
                    'name' => 'Mechanical Keyboard RGB',
                    'price' => 89.50,
                    'stock' => 8,
                    'status' => 'active',
                ],
            ],
        ];

        $response = $this->postJson('/api/products/import-direct', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'created' => 2,
                'updated' => 0,
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Logitech Wireless Mouse',
            'price' => 29.99,
            'stock' => 15,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Mechanical Keyboard RGB',
            'price' => 89.50,
            'stock' => 8,
        ]);
    }

    /**
     * 2. Test direct mapped data import in update_existing mode.
     */
    public function test_direct_import_updates_existing_products(): void
    {
        Product::create([
            'name' => 'Gaming Monitor 4K',
            'price' => 399.00,
            'stock' => 5,
            'status' => 'active',
        ]);

        $payload = [
            'import_mode' => 'update_existing',
            'rows' => [
                [
                    'name' => 'Gaming Monitor 4K',
                    'price' => 349.00, // Updated price
                    'stock' => 12,     // Updated stock
                    'status' => 'active',
                ],
            ],
        ];

        $response = $this->postJson('/api/products/import-direct', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'created' => 0,
                'updated' => 1,
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Gaming Monitor 4K',
            'price' => 349.00,
            'stock' => 12,
        ]);
    }

    /**
     * 3. Test Custom Export in JSON format.
     */
    public function test_custom_export_json_format(): void
    {
        Product::create([
            'name' => 'USB-C Cable 2M',
            'price' => 9.99,
            'stock' => 50,
            'status' => 'active',
        ]);

        $response = $this->get('/api/products/export-custom?format=json&columns[]=id&columns[]=name&columns[]=price');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');

        $content = $response->streamedContent();
        $this->assertStringContainsString('USB-C Cable 2M', $content);
        $this->assertStringContainsString('9.99', $content);
    }

    /**
     * 4. Test Custom Export in CSV format.
     */
    public function test_custom_export_csv_format(): void
    {
        Product::create([
            'name' => 'Ergonomic Desk Mat',
            'price' => 19.99,
            'stock' => 20,
            'status' => 'active',
        ]);

        $response = $this->get('/api/products/export-custom?format=csv&columns[]=id&columns[]=name&columns[]=price');

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-disposition') ?? '', '.csv')
        );
    }

    /**
     * 5. Test Custom Export in PDF HTML Report format.
     */
    public function test_custom_export_pdf_html_report(): void
    {
        Product::create([
            'name' => 'Studio Headphones Pro',
            'price' => 149.00,
            'stock' => 10,
            'status' => 'active',
        ]);

        $response = $this->get('/api/products/export-custom?format=pdf_html&columns[]=name&columns[]=price&columns[]=stock');

        $response->assertStatus(200);
        $response->assertSeeText('Studio Headphones Pro');
        $response->assertSeeText('Product Inventory');
        $response->assertSeeText('Export Report');
    }
}
