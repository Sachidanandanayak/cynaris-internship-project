<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devTools = Category::where('slug', 'development-tools')->first();
        $cloud = Category::where('slug', 'cloud-infrastructure')->first();
        $security = Category::where('slug', 'cybersecurity')->first();
        $hardware = Category::where('slug', 'hardware-peripherals')->first();

        $products = [
            [
                'category_id' => $devTools?->id,
                'name' => 'PhpStorm IDE Professional License',
                'description' => 'Advanced PHP and Web IDE featuring intelligent code completion, refactoring, and on-the-fly error prevention.',
                'price' => 249.00,
            ],
            [
                'category_id' => $devTools?->id,
                'name' => 'Laravel Nova Admin Panel License',
                'description' => 'Beautifully designed administration panel for Laravel web applications.',
                'price' => 99.00,
            ],
            [
                'category_id' => $cloud?->id,
                'name' => 'Cloud Compute Node (4 vCPU, 16GB RAM)',
                'description' => 'High-performance cloud compute instance optimized for containerized workloads and database hosting.',
                'price' => 45.00,
            ],
            [
                'category_id' => $cloud?->id,
                'name' => 'Managed S3 Object Storage (1TB)',
                'description' => 'Highly resilient distributed object storage with geo-redundancy and high transfer speeds.',
                'price' => 19.99,
            ],
            [
                'category_id' => $security?->id,
                'name' => 'Wildcard SSL/TLS Certificate (2-Year)',
                'description' => 'Enterprise 256-bit encryption certificate covering unlimited subdomains with 99.9% browser trust.',
                'price' => 129.50,
            ],
            [
                'category_id' => $security?->id,
                'name' => 'Automated Vulnerability Scanner Pro',
                'description' => 'Continuous security scanning for OWASP Top 10 vulnerabilities and dependency CVE alerts.',
                'price' => 89.00,
            ],
            [
                'category_id' => $hardware?->id,
                'name' => 'Ergonomic Mechanical Split Keyboard',
                'description' => 'Hot-swappable mechanical switches with columnar layout and per-key RGB backlighting for developer ergonomics.',
                'price' => 189.95,
            ],
            [
                'category_id' => $hardware?->id,
                'name' => '4K Ultra-Wide Developer Monitor 34"',
                'description' => 'IPS curved panel with 99% sRGB coverage, USB-C 90W power delivery, and built-in KVM switch.',
                'price' => 499.99,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
