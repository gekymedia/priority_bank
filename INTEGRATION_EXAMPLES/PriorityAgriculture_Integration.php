<?php

/**
 * Priority Agriculture System Integration Example
 * 
 * This file shows how to integrate Priority Agriculture system with Priority Bank.
 * 
 * Place this in: priority_agribusiness/app/Services/PriorityBankIntegrationService.php
 */

namespace App\Services;

use App\Services\PriorityBankApiClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PriorityBankIntegrationService
{
    protected PriorityBankApiClient $client;

    public function __construct()
    {
        $this->client = new PriorityBankApiClient(
            config('services.priority_bank.api_url'),
            config('services.priority_bank.api_token')
        );
    }

    /**
     * Push egg sale income to Priority Bank
     */
    public function pushEggSale($eggSale): bool
    {
        try {
            $totalAmount = $eggSale->quantity_sold * $eggSale->price_per_unit;

            $result = $this->client->pushIncome(
                systemId: 'priority_agriculture',
                externalTransactionId: 'agri_egg_sale_' . $eggSale->id,
                amount: (float) $totalAmount,
                date: $eggSale->date->format('Y-m-d'),
                channel: 'cash', // Adjust based on payment method
                options: [
                    'notes' => "Egg sale: {$eggSale->quantity_sold} {$eggSale->unit_type} @ {$eggSale->price_per_unit}",
                    'income_category_name' => 'Egg Sales',
                    'metadata' => [
                        'operation' => 'poultry_farm',
                        'quantity' => $eggSale->quantity_sold,
                        'unit_type' => $eggSale->unit_type,
                        'price_per_unit' => $eggSale->price_per_unit,
                    ],
                ]
            );

            return $result && $result['success'];
        } catch (\Exception $e) {
            Log::error('Exception pushing egg sale to Priority Bank', [
                'egg_sale_id' => $eggSale->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Push bird sale income to Priority Bank
     */
    public function pushBirdSale($birdSale): bool
    {
        try {
            $totalAmount = $birdSale->quantity_sold * $birdSale->price_per_unit;

            $result = $this->client->pushIncome(
                systemId: 'priority_agriculture',
                externalTransactionId: 'agri_bird_sale_' . $birdSale->id,
                amount: (float) $totalAmount,
                date: $birdSale->date->format('Y-m-d'),
                channel: 'cash',
                options: [
                    'notes' => "Bird sale: {$birdSale->quantity_sold} {$birdSale->unit_type} @ {$birdSale->price_per_unit}",
                    'income_category_name' => 'Bird Sales',
                    'metadata' => [
                        'operation' => 'poultry_farm',
                        'quantity' => $birdSale->quantity_sold,
                        'unit_type' => $birdSale->unit_type,
                    ],
                ]
            );

            return $result && $result['success'];
        } catch (\Exception $e) {
            Log::error('Exception pushing bird sale to Priority Bank', [
                'bird_sale_id' => $birdSale->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Push crop sale income to Priority Bank
     */
    public function pushCropSale($cropSale): bool
    {
        try {
            $result = $this->client->pushIncome(
                systemId: 'priority_agriculture',
                externalTransactionId: 'agri_crop_sale_' . $cropSale->id,
                amount: (float) $cropSale->amount,
                date: $cropSale->date->format('Y-m-d'),
                channel: 'cash',
                options: [
                    'notes' => $cropSale->description ?? 'Crop sale',
                    'income_category_name' => 'Crop Sales',
                    'metadata' => [
                        'operation' => 'crop_farm',
                    ],
                ]
            );

            return $result && $result['success'];
        } catch (\Exception $e) {
            Log::error('Exception pushing crop sale to Priority Bank', [
                'crop_sale_id' => $cropSale->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Push poultry expense to Priority Bank
     */
    public function pushPoultryExpense($expense): bool
    {
        try {
            $result = $this->client->pushExpense(
                systemId: 'priority_agriculture',
                externalTransactionId: 'agri_poultry_expense_' . $expense->id,
                amount: (float) $expense->amount,
                date: $expense->date->format('Y-m-d'),
                channel: 'cash',
                options: [
                    'notes' => $expense->description,
                    'expense_category_name' => $this->mapExpenseCategory($expense->category?->name),
                    'metadata' => [
                        'operation' => 'poultry_farm',
                        'category' => $expense->category?->name,
                    ],
                ]
            );

            return $result && $result['success'];
        } catch (\Exception $e) {
            Log::error('Exception pushing poultry expense to Priority Bank', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Push crop input expense to Priority Bank
     */
    public function pushCropInputExpense($expense): bool
    {
        try {
            $result = $this->client->pushExpense(
                systemId: 'priority_agriculture',
                externalTransactionId: 'agri_crop_expense_' . $expense->id,
                amount: (float) $expense->amount,
                date: $expense->date->format('Y-m-d'),
                channel: 'cash',
                options: [
                    'notes' => $expense->description,
                    'expense_category_name' => $this->mapExpenseCategory($expense->category?->name),
                    'metadata' => [
                        'operation' => 'crop_farm',
                    ],
                ]
            );

            return $result && $result['success'];
        } catch (\Exception $e) {
            Log::error('Exception pushing crop expense to Priority Bank', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Map expense category
     */
    protected function mapExpenseCategory(?string $category): string
    {
        $mapping = [
            'Feed' => 'Feed',
            'Vet Services' => 'Veterinary Services',
            'Labor' => 'Labor',
            'Medication' => 'Medication',
        ];

        return $mapping[$category] ?? 'Other Expenses';
    }
}

