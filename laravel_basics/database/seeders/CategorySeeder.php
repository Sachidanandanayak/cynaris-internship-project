<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Development Tools',
                'slug' => 'development-tools',
                'description' => 'IDEs, compilers, debuggers, and developer productivity suites.',
            ],
            [
                'name' => 'Cloud Infrastructure',
                'slug' => 'cloud-infrastructure',
                'description' => 'Virtual machines, serverless runtimes, and managed storage.',
            ],
            [
                'name' => 'Cybersecurity',
                'slug' => 'cybersecurity',
                'description' => 'Endpoint protection, vulnerability scanners, and SSL certificates.',
            ],
            [
                'name' => 'Hardware & Peripherals',
                'slug' => 'hardware-peripherals',
                'description' => 'Mechanical keyboards, ergonomic monitors, and developer workstations.',
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
