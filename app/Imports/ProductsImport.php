<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    /**
     * Import mode.
     *
     * add_new        = Always create new products.
     * update_existing = Update product if name exists, otherwise create.
     */
    protected string $mode;

    /**
     * Number of newly created products.
     */
    public int $createdCount = 0;

    /**
     * Number of updated products.
     */
    public int $updatedCount = 0;

    /**
     * Number of skipped rows.
     */
    public int $skippedCount = 0;

    /**
     * Import errors.
     */
    public array $errors = [];

    /**
     * Import activity details.
     */
    public array $activities = [];

    /**
     * Constructor.
     */
    public function __construct(string $mode = 'add_new')
    {
        $this->mode = in_array($mode, [
            'add_new',
            'update_existing',
        ])
            ? $mode
            : 'add_new';
    }

    /**
     * Process all Excel rows.
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            /*
            |--------------------------------------------------------------------------
            | Excel row number
            |--------------------------------------------------------------------------
            |
            | Header is row 1, therefore data starts from row 2.
            |
            */

            $excelRowNumber = $index + 2;

            $data = [
                'name' => isset($row['name'])
                    ? trim((string) $row['name'])
                    : '',

                'price' => $row['price'] ?? null,

                'stock' => $row['stock'] ?? null,

                'status' => isset($row['status'])
                    && trim((string) $row['status']) !== ''
                    ? strtolower(trim((string) $row['status']))
                    : 'active',
            ];


            /*
            |--------------------------------------------------------------------------
            | Validate Excel Row
            |--------------------------------------------------------------------------
            */

            $validator = Validator::make(
                $data,
                [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'price' => [
                        'required',
                        'numeric',
                        'min:0',
                    ],

                    'stock' => [
                        'required',
                        'integer',
                        'min:0',
                    ],

                    'status' => [
                        'required',
                        'in:active,inactive',
                    ],
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Skip Invalid Row
            |--------------------------------------------------------------------------
            */

            if ($validator->fails()) {

                $this->skippedCount++;

                $this->errors[] = [
                    'row' => $excelRowNumber,

                    'name' => $data['name'] ?: 'Unknown',

                    'errors' => $validator
                        ->errors()
                        ->all(),
                ];

                $this->activities[] = [
                    'row' => $excelRowNumber,

                    'name' => $data['name'] ?: 'Unknown',

                    'action' => 'Skipped',

                    'message' => 'Validation failed.',
                ];

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ADD NEW MODE
            |--------------------------------------------------------------------------
            */

            if ($this->mode === 'add_new') {

                $product = Product::create([
                    'name' => $data['name'],

                    'price' => $data['price'],

                    'stock' => $data['stock'],

                    'status' => $data['status'],
                ]);

                $this->createdCount++;

                $this->activities[] = [
                    'row' => $excelRowNumber,

                    'name' => $product->name,

                    'action' => 'Created',

                    'message' => 'New product created.',
                ];

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE EXISTING MODE
            |--------------------------------------------------------------------------
            |
            | Match products using their name.
            |
            */

            $product = Product::where(
                'name',
                $data['name']
            )->first();


            /*
            |--------------------------------------------------------------------------
            | Existing Product Found
            |--------------------------------------------------------------------------
            */

            if ($product) {

                $product->update([
                    'price' => $data['price'],

                    'stock' => $data['stock'],

                    'status' => $data['status'],
                ]);

                $this->updatedCount++;

                $this->activities[] = [
                    'row' => $excelRowNumber,

                    'name' => $product->name,

                    'action' => 'Updated',

                    'message' => 'Existing product updated.',
                ];

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Product Does Not Exist
            |--------------------------------------------------------------------------
            |
            | In update mode, a product that doesn't exist is created.
            |
            */

            $product = Product::create([
                'name' => $data['name'],

                'price' => $data['price'],

                'stock' => $data['stock'],

                'status' => $data['status'],
            ]);

            $this->createdCount++;

            $this->activities[] = [
                'row' => $excelRowNumber,

                'name' => $product->name,

                'action' => 'Created',

                'message' => 'Product did not exist, so a new product was created.',
            ];
        }
    }

    /**
     * Get selected import mode.
     */
    public function getMode(): string
    {
        return $this->mode;
    }
}