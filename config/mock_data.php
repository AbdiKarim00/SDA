<?php
// Mock data for testing
$mock_users = [
    [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'logistics@example.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'role' => 'logistics'
    ]
];

$mock_vehicles = [
    [
        'id' => 1,
        'registration_number' => 'KAA 123A',
        'make' => 'Toyota',
        'model' => 'Land Cruiser',
        'chassis_number' => 'JTEHT05JX00000001',
        'engine_number' => '1HD-FTE0000001',
        'vehicle_type' => 'SUV',
        'capacity' => '7 passengers',
        'fuel_type' => 'Diesel',
        'date_of_purchase' => '2023-01-15',
        'funded_by' => 'Government',
        'current_mileage' => 15000,
        'insurance_expiry' => '2024-12-31',
        'road_license_expiry' => '2024-12-31',
        'next_service_due' => '2024-03-15',
        'status' => 'Available',
        'date_added' => '2023-01-15 10:00:00'
    ],
    [
        'id' => 2,
        'registration_number' => 'KAA 456B',
        'make' => 'Mitsubishi',
        'model' => 'L200',
        'chassis_number' => 'MMBJNKB70CD000001',
        'engine_number' => '4D56-0000001',
        'vehicle_type' => 'Truck',
        'capacity' => '2 tons',
        'fuel_type' => 'Diesel',
        'date_of_purchase' => '2023-02-20',
        'funded_by' => 'Government',
        'current_mileage' => 25000,
        'insurance_expiry' => '2024-12-31',
        'road_license_expiry' => '2024-12-31',
        'next_service_due' => '2024-02-20',
        'status' => 'In Maintenance',
        'date_added' => '2023-02-20 14:30:00'
    ],
    [
        'id' => 3,
        'registration_number' => 'KAA 789C',
        'make' => 'Toyota',
        'model' => 'Hiace',
        'chassis_number' => 'LHGDM81H00000001',
        'engine_number' => '2KD-FTV0000001',
        'vehicle_type' => 'Van',
        'capacity' => '14 passengers',
        'fuel_type' => 'Diesel',
        'date_of_purchase' => '2023-03-10',
        'funded_by' => 'Government',
        'current_mileage' => 18000,
        'insurance_expiry' => '2024-12-31',
        'road_license_expiry' => '2024-12-31',
        'next_service_due' => '2024-03-10',
        'status' => 'On Trip',
        'date_added' => '2023-03-10 09:15:00'
    ],
    [
        'id' => 4,
        'registration_number' => 'KAA 012D',
        'make' => 'Toyota',
        'model' => 'Corolla',
        'chassis_number' => '2T1BURHE00000001',
        'engine_number' => '2ZR-FE0000001',
        'vehicle_type' => 'Sedan',
        'capacity' => '5 passengers',
        'fuel_type' => 'Petrol',
        'date_of_purchase' => '2023-04-05',
        'funded_by' => 'Government',
        'current_mileage' => 12000,
        'insurance_expiry' => '2024-12-31',
        'road_license_expiry' => '2024-12-31',
        'next_service_due' => '2024-04-05',
        'status' => 'Available',
        'date_added' => '2023-04-05 11:45:00'
    ],
    [
        'id' => 5,
        'registration_number' => 'KAA 345E',
        'make' => 'Isuzu',
        'model' => 'D-Max',
        'chassis_number' => 'MPATFS85J00000001',
        'engine_number' => '4JJ1-TC0000001',
        'vehicle_type' => 'Truck',
        'capacity' => '1.5 tons',
        'fuel_type' => 'Diesel',
        'date_of_purchase' => '2023-05-15',
        'funded_by' => 'Government',
        'current_mileage' => 20000,
        'insurance_expiry' => '2024-12-31',
        'road_license_expiry' => '2024-12-31',
        'next_service_due' => '2024-05-15',
        'status' => 'Decommissioned',
        'date_added' => '2023-05-15 16:20:00'
    ]
];

$mock_drivers = [
    [
        'id' => 1,
        'name' => 'James Wilson',
        'license_number' => 'DL123456',
        'license_expiry' => '2024-12-31',
        'status' => 'Available'
    ],
    [
        'id' => 2,
        'name' => 'Robert Brown',
        'license_number' => 'DL789012',
        'license_expiry' => '2024-12-31',
        'status' => 'On Trip'
    ]
];

// Function to get mock data
function get_mock_data($type) {
    global $mock_users, $mock_vehicles, $mock_drivers;
    
    switch ($type) {
        case 'users':
            return $mock_users;
        case 'vehicles':
            return $mock_vehicles;
        case 'drivers':
            return $mock_drivers;
        default:
            return [];
    }
}

// Function to get mock user by email
function get_mock_user_by_email($email) {
    global $mock_users;
    foreach ($mock_users as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

// Function to get mock vehicle by id
function get_mock_vehicle_by_id($id) {
    global $mock_vehicles;
    foreach ($mock_vehicles as $vehicle) {
        if ($vehicle['id'] == $id) {
            return $vehicle;
        }
    }
    return null;
}

// Function to get mock driver by id
function get_mock_driver_by_id($id) {
    global $mock_drivers;
    foreach ($mock_drivers as $driver) {
        if ($driver['id'] == $id) {
            return $driver;
        }
    }
    return null;
}
?> 