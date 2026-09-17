<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    protected ?string $search;
    protected ?string $status;
    protected ?string $stockFilter;

    public function __construct(
        ?string $search = null,
        ?string $status = null,
        ?string $stockFilter = null
    ) {
        $this->search = $search;
        $this->status = $status;
        $this->stockFilter = $stockFilter;
    }

    /**
     * Build the product query using the selected filters.
     */
    public function query(): Builder
    {
        $query = Product::query();

        // Live search filter
        if (!empty($this->search)) {
            $search = $this->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        // Status filter
        if (!empty($this->status) && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        // Stock filter
        if (!empty($this->stockFilter) && $this->stockFilter !== 'all') {

            if ($this->stockFilter === 'in_stock') {
                $query->where('stock', '>', 0);
            }

            if ($this->stockFilter === 'low_stock') {
                $query->whereBetween('stock', [1, 10]);
            }

            if ($this->stockFilter === 'out_of_stock') {
                $query->where('stock', 0);
            }
        }

        return $query->latest();
    }

    /**
     * Map each product row for Excel.
     */
    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->price,
            $product->stock,
            $product->status,
            $product->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Excel headings.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Price',
            'Stock',
            'Status',
            'Created At',
        ];
    }
}