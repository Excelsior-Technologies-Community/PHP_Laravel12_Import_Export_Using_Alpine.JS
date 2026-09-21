<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $products = [
            // High-End Hardware & Laptops
            ['name' => 'Apple MacBook Pro 16 M3 Max (36GB / 1TB)', 'price' => 3499.00, 'stock' => 12, 'status' => 'active'],
            ['name' => 'Dell XPS 15 OLED Touchscreen Laptop', 'price' => 2199.50, 'stock' => 15, 'status' => 'active'],
            ['name' => 'ASUS ROG Zephyrus G14 Gaming Laptop', 'price' => 1599.00, 'stock' => 8, 'status' => 'active'],
            ['name' => 'Lenovo ThinkPad X1 Carbon Gen 11', 'price' => 1420.00, 'stock' => 20, 'status' => 'active'],

            // Monitors & Displays
            ['name' => 'Dell UltraSharp 32 4K USB-C Hub Monitor', 'price' => 849.99, 'stock' => 18, 'status' => 'active'],
            ['name' => 'Samsung Odyssey OLED G9 49 Curved Gaming Monitor', 'price' => 1299.99, 'stock' => 4, 'status' => 'active'],
            ['name' => 'LG UltraFine 27 5K IPS Display', 'price' => 1196.00, 'stock' => 6, 'status' => 'active'],
            ['name' => 'BenQ ScreenBar Halo LED Monitor Light Bar', 'price' => 179.00, 'stock' => 25, 'status' => 'active'],

            // Keyboards & Mice
            ['name' => 'Logitech MX Master 3S Wireless Performance Mouse', 'price' => 99.99, 'stock' => 45, 'status' => 'active'],
            ['name' => 'Keychron Q1 Pro QMK Custom Mechanical Keyboard', 'price' => 199.00, 'stock' => 24, 'status' => 'active'],
            ['name' => 'Logitech MX Keys S Advanced Wireless Keyboard', 'price' => 109.99, 'stock' => 38, 'status' => 'active'],
            ['name' => 'Razer DeathAdder V3 Pro Ultra-lightweight Mouse', 'price' => 149.99, 'stock' => 17, 'status' => 'active'],
            ['name' => 'SteelSeries Apex Pro TKL Mechanical Keyboard', 'price' => 189.95, 'stock' => 0, 'status' => 'inactive'], // Out of Stock & Inactive

            // Audio & Microphones
            ['name' => 'Sony WH-1000XM5 Wireless Noise Canceling Headphones', 'price' => 398.00, 'stock' => 30, 'status' => 'active'],
            ['name' => 'Bose QuietComfort Ultra Wireless Noise Cancelling Earbuds', 'price' => 299.00, 'stock' => 22, 'status' => 'active'],
            ['name' => 'Shure SM7B Cardioid Dynamic Vocal Microphone', 'price' => 399.00, 'stock' => 0, 'status' => 'inactive'], // Out of stock
            ['name' => 'Focusrite Scarlett 2i2 4th Gen USB Audio Interface', 'price' => 199.99, 'stock' => 19, 'status' => 'active'],
            ['name' => 'Audio-Technica ATH-M50x Professional Studio Monitor', 'price' => 149.00, 'stock' => 7, 'status' => 'active'], // Low stock

            // Ergonomic Furniture & Accessories
            ['name' => 'Herman Miller Aeron Ergonomic Office Chair', 'price' => 1695.00, 'stock' => 5, 'status' => 'active'], // Low stock
            ['name' => 'Jarvis Bamboo Standing Desk (60x30)', 'price' => 699.00, 'stock' => 9, 'status' => 'active'], // Low stock
            ['name' => 'Ergotron LX Desk Mount Heavy-Duty Monitor Arm', 'price' => 189.00, 'stock' => 3, 'status' => 'active'], // Low stock
            ['name' => 'Grovemade Walnut MagSafe Charging Stand', 'price' => 120.00, 'stock' => 16, 'status' => 'active'],

            // Storage & Power
            ['name' => 'CalDigit TS4 Thunderbolt 4 18-Port Dock', 'price' => 399.95, 'stock' => 2, 'status' => 'active'], // Low stock
            ['name' => 'Anker 737 Power Bank 24000mAh 140W', 'price' => 149.99, 'stock' => 0, 'status' => 'active'], // Out of stock
            ['name' => 'Samsung 990 Pro 2TB PCIe 4.0 NVMe SSD', 'price' => 179.99, 'stock' => 40, 'status' => 'active'],
            ['name' => 'SanDisk Extreme Pro 2TB Portable SSD', 'price' => 219.99, 'stock' => 28, 'status' => 'active'],
            ['name' => 'UGREEN Nexode 100W 4-Port GaN Fast Wall Charger', 'price' => 59.99, 'stock' => 65, 'status' => 'active'],

            // Streaming & Cameras
            ['name' => 'Elgato Stream Deck MK.2 Studio Controller', 'price' => 149.99, 'stock' => 1, 'status' => 'active'], // Low stock
            ['name' => 'Logitech Brio 4K Ultra HD Pro Webcam', 'price' => 169.99, 'stock' => 0, 'status' => 'active'], // Out of stock
            ['name' => 'Sony Alpha A6700 Mirrorless Camera Body', 'price' => 1398.00, 'stock' => 4, 'status' => 'inactive'], // Low stock & Inactive
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['name' => $product['name']], $product);
        }
    }
}
