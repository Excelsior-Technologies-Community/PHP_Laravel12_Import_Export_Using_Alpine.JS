<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected ?string $search;
    protected ?string $status;
    protected ?string $stockFilter;
    protected $minPrice;
    protected $maxPrice;
    protected ?string $startDate;
    protected ?string $endDate;
    protected array $columns;

    public function __construct(
        ?string $search = null,
        ?string $status = null,
        ?string $stockFilter = null,
        $minPrice = null,
        $maxPrice = null,
        ?string $startDate = null,
        ?string $endDate = null,
        array $columns = ['id', 'name', 'price', 'stock', 'status', 'created_at']
    ) {
        $this->search = $search;
        $this->status = $status;
        $this->stockFilter = $stockFilter;
        $this->minPrice = $minPrice;
        $this->maxPrice = $maxPrice;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->columns = !empty($columns) ? $columns : ['id', 'name', 'price', 'stock', 'status', 'created_at'];
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
        if (!empty($this->status) && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        // Stock filter
        if (!empty($this->stockFilter) && $this->stockFilter !== 'all') {
            if ($this->stockFilter === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($this->stockFilter === 'low_stock') {
                $query->whereBetween('stock', [1, 10]);
            } elseif ($this->stockFilter === 'out_of_stock') {
                $query->where('stock', 0);
            }
        }

        // Price range
        if ($this->minPrice !== null && $this->minPrice !== '') {
            $query->where('price', '>=', $this->minPrice);
        }

        if ($this->maxPrice !== null && $this->maxPrice !== '') {
            $query->where('price', '<=', $this->maxPrice);
        }

        // Date range
        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->latest();
    }

    /**
     * Map product row dynamically based on selected columns.
     */
    public function map($product): array
    {
        $row = [];

        foreach ($this->columns as $col) {
            switch ($col) {
                case 'id':
                    $row[] = $product->id;
                    break;
                case 'name':
                    $row[] = $product->name;
                    break;
                case 'price':
                    $row[] = (float) $product->price;
                    break;
                case 'stock':
                    $row[] = (int) $product->stock;
                    break;
                case 'status':
                    $row[] = ucfirst($product->status);
                    break;
                case 'inventory_value':
                    $row[] = round((float) $product->price * (int) $product->stock, 2);
                    break;
                case 'created_at':
                    $row[] = $product->created_at?->format('Y-m-d H:i:s');
                    break;
                case 'updated_at':
                    $row[] = $product->updated_at?->format('Y-m-d H:i:s');
                    break;
            }
        }

        return $row;
    }

    /**
     * Dynamic Excel headings.
     */
    public function headings(): array
    {
        $headingMap = [
            'id' => 'Product ID',
            'name' => 'Product Name',
            'price' => 'Unit Price',
            'stock' => 'Stock Qty',
            'status' => 'Status',
            'inventory_value' => 'Total Inventory Value',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];

        $headings = [];
        foreach ($this->columns as $col) {
            $headings[] = $headingMap[$col] ?? ucfirst(str_replace('_', ' ', $col));
        }

        return $headings;
    }

    /**
     * Custom Excel Header Styling
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3730A3'], // Indigo Header
                ],
            ],
        ];
    }
}