<?php
require_once '../../config/database.php';
require_once '../../utils/response.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    // Fetch all active vehicles with their current locations
    $stmt = $pdo->query("
        SELECT 
            v.id,
            v.registration_number,
            v.make,
            v.model,
            v.latitude,
            v.longitude,
            v.status,
            v.location_updated_at as last_updated,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name
        FROM vehicles v
        LEFT JOIN drivers d ON v.current_driver_id = d.id
        WHERE v.status != 'decommissioned'
        ORDER BY v.registration_number
    ");
    
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $response = array_map(function($vehicle) {
        return [
            'id' => $vehicle['id'],
            'registration_number' => $vehicle['registration_number'],
            'make' => $vehicle['make'],
            'model' => $vehicle['model'],
            'latitude' => (float)$vehicle['latitude'],
            'longitude' => (float)$vehicle['longitude'],
            'status' => $vehicle['status'],
            'driver_name' => $vehicle['driver_name'] ?: 'Unassigned',
            'last_updated' => $vehicle['last_updated']
        ];
    }, $vehicles);
    
    sendSuccess('Vehicle locations retrieved successfully', $response);
} catch (Exception $e) {
    sendError('Failed to fetch vehicle locations: ' . $e->getMessage());
} 