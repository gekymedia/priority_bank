<?php

namespace Database\Seeders;

use App\Models\SystemRegistry;
use Illuminate\Database\Seeder;

class SystemsRegistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systems = [
            [
                'system_id' => 'gekymedia',
                'name' => 'Gekymedia System',
                'type' => 'hybrid',
                'description' => 'Multi-Directorate System: Geky Dev, Geky Studios, Geky Prints, Geky Stations (TV)',
                'active_status' => true,
                'metadata' => [
                    'directorates' => [
                        'geky_dev' => [
                            'name' => 'Geky Dev',
                            'income_sources' => [
                                'Fabamall',
                                'Priority Solutions Agency',
                                'TK Innovate',
                                'Sunyani Technical University',
                            ],
                        ],
                        'geky_studios' => ['name' => 'Geky Studios'],
                        'geky_prints' => ['name' => 'Geky Prints'],
                        'geky_stations' => ['name' => 'Geky Stations (TV)'],
                    ],
                ],
            ],
            [
                'system_id' => 'priority_solutions_agency',
                'name' => 'Priority Solutions Agency',
                'type' => 'hybrid',
                'description' => 'Includes Priority Travels, Priority Nova, University contracts (CUG, ANGUTECH)',
                'active_status' => true,
                'metadata' => [
                    'subsidiaries' => [
                        'priority_travels',
                        'priority_nova',
                    ],
                    'contracts' => [
                        'CUG',
                        'ANGUTECH',
                    ],
                ],
            ],
            [
                'system_id' => 'priority_accommodation',
                'name' => 'Priority Accommodation',
                'type' => 'manual',
                'description' => 'Rent/bookings income, maintenance & utilities expenses',
                'active_status' => true,
            ],
            [
                'system_id' => 'priority_agriculture',
                'name' => 'Priority Agriculture',
                'type' => 'hybrid',
                'description' => 'Poultry farm and crop farm - produce sales, feed, vet services, labor',
                'active_status' => true,
                'metadata' => [
                    'operations' => [
                        'poultry_farm',
                        'crop_farm',
                    ],
                ],
            ],
            [
                'system_id' => 'schoolsgh',
                'name' => 'SchoolsGH',
                'type' => 'automated',
                'description' => 'Independent SaaS - School term subscription payments, domain, hosting, SMS packages',
                'active_status' => true,
                'metadata' => [
                    'income_types' => ['subscription_payments'],
                    'expense_types' => ['domain', 'hosting', 'sms_packages'],
                ],
            ],
            [
                'system_id' => 'priority_admissions',
                'name' => 'Priority Admissions System',
                'type' => 'hybrid',
                'description' => 'CUG form sales (online, automated), other universities form sales (manual), document requests, dues & services',
                'active_status' => true,
                'metadata' => [
                    'income_sources' => [
                        'cug_form_sales' => 'automated',
                        'other_university_forms' => 'manual',
                        'document_requests',
                        'dues_services',
                    ],
                    'expense_types' => [
                        'hosting',
                        'sms',
                        'printing',
                        'staff_commissions',
                    ],
                ],
            ],
        ];

        foreach ($systems as $system) {
            SystemRegistry::updateOrCreate(
                ['system_id' => $system['system_id']],
                $system
            );
        }
    }
}

