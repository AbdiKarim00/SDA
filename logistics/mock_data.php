<?php
function get_mock_data($type) {
    $data = [
        'drivers' => [
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+254712345678',
                'license_number' => 'DL123456',
                'license_expiry' => '2024-12-31',
                'status' => 'Active'
            ],
            [
                'id' => 2,
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+254723456789',
                'license_number' => 'DL234567',
                'license_expiry' => '2024-11-30',
                'status' => 'Active'
            ]
        ],
        'vehicles' => [
            [
                'id' => 1,
                'registration_number' => 'GKB 673S',
                'make' => 'Toyota',
                'model' => 'Hilux',
                'year' => 2020,
                'status' => 'Available',
                'current_odometer' => 15000,
                'assigned_driver' => 'John Doe',
                'last_maintenance' => '2024-01-15',
                'next_maintenance' => '2024-07-15',
                'fuel_tank_capacity' => 80, // in litres
                'fuel_type' => 'Diesel'
            ],
            [
                'id' => 2,
                'registration_number' => 'KCB 123A',
                'make' => 'Isuzu',
                'model' => 'D-Max',
                'year' => 2021,
                'status' => 'In Use',
                'current_odometer' => 25000,
                'assigned_driver' => 'Jane Smith',
                'last_maintenance' => '2024-02-01',
                'next_maintenance' => '2024-08-01',
                'fuel_tank_capacity' => 75, // in litres
                'fuel_type' => 'Diesel'
            ]
        ],
        'trips' => [
            [
                'id' => 1,
                'driver_id' => 1,
                'vehicle_id' => 1,
                'start_location' => 'Nairobi CBD',
                'end_location' => 'Mombasa Port',
                'start_time' => '2024-03-15 08:00:00',
                'end_time' => null,
                'status' => 'In Progress',
                'purpose' => 'Cargo Delivery',
                'distance' => 500,
                'fuel_used_litres' => 0,
                'notes' => 'Regular delivery route'
            ],
            [
                'id' => 2,
                'driver_id' => 2,
                'vehicle_id' => 2,
                'start_location' => 'Nairobi CBD',
                'end_location' => 'Kisumu Port',
                'start_time' => '2024-03-14 07:00:00',
                'end_time' => '2024-03-14 18:00:00',
                'status' => 'Completed',
                'purpose' => 'Cargo Delivery',
                'distance' => 350,
                'fuel_used_litres' => 45,
                'notes' => 'Completed successfully'
            ]
        ],
        'fuel_cards' => [
            [
                'id' => 1,
                'driver_id' => 1,
                'card_number' => 'FC123456789',
                'balance_currency' => 5000.00,
                'balance_litres' => 100.00,
                'daily_limit_litres' => 50.00,
                'expiry_date' => '2025-12-31',
                'status' => 'Active',
                'last_used' => '2024-03-14',
                'last_amount_currency' => 2500.00,
                'last_amount_litres' => 50.00
            ],
            [
                'id' => 2,
                'driver_id' => 2,
                'card_number' => 'FC987654321',
                'balance_currency' => 7500.00,
                'balance_litres' => 150.00,
                'daily_limit_litres' => 60.00,
                'expiry_date' => '2025-12-31',
                'status' => 'Active',
                'last_used' => '2024-03-13',
                'last_amount_currency' => 3000.00,
                'last_amount_litres' => 60.00
            ]
        ],
        'maintenance' => [
            [
                'id' => 1,
                'vehicle_id' => 1,
                'type' => 'Regular Service',
                'date' => '2024-01-15',
                'cost' => 25000.00,
                'description' => 'Oil change, filters, and general inspection',
                'status' => 'Completed'
            ],
            [
                'id' => 2,
                'vehicle_id' => 2,
                'type' => 'Brake Service',
                'date' => '2024-02-01',
                'cost' => 35000.00,
                'description' => 'Brake pad replacement and system check',
                'status' => 'Completed'
            ]
        ],
        'incidents' => [
            [
                'id' => 1,
                'driver_id' => 1,
                'vehicle_id' => 1,
                'type' => 'Minor Accident',
                'date' => '2024-02-15',
                'description' => 'Minor fender bender in traffic',
                'status' => 'Resolved',
                'resolution' => 'Insurance claim processed'
            ],
            [
                'id' => 2,
                'driver_id' => 2,
                'vehicle_id' => 2,
                'type' => 'Mechanical Failure',
                'date' => '2024-03-01',
                'description' => 'Engine overheating issue',
                'status' => 'Under Review',
                'resolution' => null
            ]
        ]
    ];

    return $data[$type] ?? [];
} 