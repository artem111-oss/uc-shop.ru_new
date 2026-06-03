<?php

namespace App\Console\Commands;

use App\Services\RagnerShopService;
use Illuminate\Console\Command;

class SyncRagnerProducts extends Command
{
    protected $signature = 'ragner:sync-products';
    protected $description = 'Sync PUBG UC products from Ragner Shop API';

    public function handle(RagnerShopService $ragnerShop): int
    {
        $this->info('Fetching products from Ragner Shop...');

        try {
            $products = $ragnerShop->getProducts();

            $this->info('Found ' . count($products) . ' products:');
            $this->newLine();

            $this->table(
                ['ID', 'Title', 'Amount (UC)', 'Price'],
                collect($products)->map(function ($product) {
                    return [
                        $product['id'] ?? 'N/A',
                        $product['title'] ?? 'N/A',
                        $product['amount'] ?? 'N/A',
                        $product['price'] ?? 'N/A',
                    ];
                })->toArray()
            );

            $this->newLine();
            $this->info('✅ Products synced successfully!');
            $this->newLine();
            $this->warn('Update the mapping in RagnerShopService::getItemIdByUcAmount() with these IDs');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to sync products: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
