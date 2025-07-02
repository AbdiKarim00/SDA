<?php
require_once '../includes/bootstrap.php';

// Mock data for search
$mockData = [
    [
        'id' => 1,
        'type' => 'vehicle',
        'title' => 'KAA 123A',
        'subtitle' => 'Toyota Land Cruiser - Active',
        'icon' => 'bi-truck',
        'url' => 'vehicles.php?id=1'
    ],
    [
        'id' => 2,
        'type' => 'vehicle',
        'title' => 'KAA 456B',
        'subtitle' => 'Mitsubishi Fuso - In Maintenance',
        'icon' => 'bi-truck',
        'url' => 'vehicles.php?id=2'
    ],
    [
        'id' => 3,
        'type' => 'driver',
        'title' => 'John Doe',
        'subtitle' => 'Driver - Active',
        'icon' => 'bi-person',
        'url' => 'drivers.php?id=1'
    ],
    [
        'id' => 4,
        'type' => 'driver',
        'title' => 'Jane Smith',
        'subtitle' => 'Driver - On Leave',
        'icon' => 'bi-person',
        'url' => 'drivers.php?id=2'
    ],
    [
        'id' => 5,
        'type' => 'maintenance',
        'title' => 'Service #123',
        'subtitle' => 'KAA 123A - Routine Service',
        'icon' => 'bi-tools',
        'url' => 'maintenance.php?id=1'
    ]
];

// Get search query
$query = $_GET['q'] ?? '';

// Filter mock data based on query
$results = array_filter($mockData, function($item) use ($query) {
    $searchableText = strtolower($item['title'] . ' ' . $item['subtitle']);
    return strpos($searchableText, strtolower($query)) !== false;
});

// Return results as JSON
header('Content-Type: application/json');
echo json_encode(array_values($results)); 