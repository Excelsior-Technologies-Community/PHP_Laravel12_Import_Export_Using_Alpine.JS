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
    protected $minPrice;
    protected $maxPrice;
    protected ?string $startDate;
    protected ?string $endDate;

    public function __construct(
        ?string $search = null,
        ?string $status = null,
        ?string $stockFilter = null,
        $minPrice = null,
        $maxPrice = null,
        ?string $startDate = null,
        ?string $endDate = null
    ) {
        $this->search = $search;
        $this->status = $status;
        $this->stockFilter = $stockFilter;
        $this->minPrice = $minPrice;
        $this->maxPrice = $maxPrice;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Build filtered export query.
     */
    public function query(): Builder
    {
        $query = Product::query();

        // Search
        if (!empty($this->search)) {
            $search = $this->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        // Status
        if (
            !empty($this->status)
            && $this->status !== 'all'
        ) {
            $query->where('status', $this->status);
        }

        // Stock
        if (
            !empty($this->stockFilter)
            && $this->stockFilter !== 'all'
        ) {
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

        // Minimum price
        if (
            $this->minPrice !== null
            && $this->minPrice !== ''
        ) {
            $query->where(
                'price',
                '>=',
                $this->minPrice
            );
        }

        // Maximum price
        if (
            $this->maxPrice !== null
            && $this->maxPrice !== ''
        ) {
            $query->where(
                'price',
                '<=',
                $this->maxPrice
            );
        }

        // Start date
        if (!empty($this->startDate)) {
            $query->whereDate(
                'created_at',
                '>=',
                $this->startDate
            );
        }

        // End date
        if (!empty($this->endDate)) {
            $query->whereDate(
                'created_at',
                '<=',
                $this->endDate
            );
        }

        return $query->latest();
    }

    /**
     * Map product row.
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