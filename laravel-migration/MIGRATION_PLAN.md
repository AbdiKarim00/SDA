# Laravel Migration Plan for Transport IMS

## 1. Current Project Analysis

### Existing Structure:
- **Custom PHP Application** with role-based access (Admin, Logistics, Driver)
- **PostgreSQL Database** with vehicles table
- **Mock Data System** for testing
- **Alpine.js + Tailwind CSS** frontend
- **Manual authentication** and session management

### Key Entities Identified:
- Users (Admin, Logistics, Driver roles)
- Vehicles (with comprehensive vehicle data)
- Drivers (with license information)
- Trips (assignment and tracking)
- Fuel Cards (balance and limits)
- Maintenance Records
- Incidents/Reports

## 2. Laravel Setup Strategy

### Phase 1: Initial Laravel Setup (Week 1)

#### Step 1: Create New Laravel Project
```bash
# Create new Laravel project alongside existing one
composer create-project laravel/laravel transportims-laravel
cd transportims-laravel

# Install additional packages
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require league/flysystem-aws-s3-v3
```

#### Step 2: Environment Configuration
```bash
# Configure .env file
cp .env.example .env
php artisan key:generate
```

#### Step 3: Database Configuration
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=transport_ims_laravel
DB_USERNAME=postgres
DB_PASSWORD=1212
```

### Phase 2: Database Migration (Week 1-2)

#### Step 1: Create Migration Files
```bash
# User and authentication migrations
php artisan make:migration create_users_table --create=users
php artisan make:migration create_password_resets_table --create=password_resets
php artisan make:migration create_failed_jobs_table --create=failed_jobs
php artisan make:migration create_personal_access_tokens_table --create=personal_access_tokens

# Core business migrations
php artisan make:migration create_vehicles_table --create=vehicles
php artisan make:migration create_drivers_table --create=drivers
php artisan make:migration create_trips_table --create=trips
php artisan make:migration create_fuel_cards_table --create=fuel_cards
php artisan make:migration create_maintenance_records_table --create=maintenance_records
php artisan make:migration create_incidents_table --create=incidents
php artisan make:migration create_vehicle_assignments_table --create=vehicle_assignments
```

#### Step 2: Migration Content Structure

**Users Migration:**
- id, name, email, password, role, phone, license_number, license_expiry, status, created_at, updated_at

**Vehicles Migration:**
- id, registration_no, make, model, chassis_no, engine_no, vehicle_type, capacity, fuel_type, purchase_date, funded_by, current_mileage, insurance_expiry, road_license_expiry, next_service_due, status, notes, created_at, updated_at

**Drivers Migration:**
- id, user_id (FK), license_number, license_expiry, status, vehicle_id (FK), created_at, updated_at

**Trips Migration:**
- id, driver_id (FK), vehicle_id (FK), start_location, end_location, purpose, status, start_time, end_time, odometer_start, odometer_end, created_at, updated_at

**Fuel Cards Migration:**
- id, card_number, driver_id (FK), vehicle_id (FK), balance, daily_limit, status, created_at, updated_at

**Maintenance Records Migration:**
- id, vehicle_id (FK), type, description, cost, service_date, next_service_date, status, created_at, updated_at

### Phase 3: Model Creation (Week 2)

#### Step 1: Create Eloquent Models
```bash
php artisan make:model User
php artisan make:model Vehicle
php artisan make:model Driver
php artisan make:model Trip
php artisan make:model FuelCard
php artisan make:model MaintenanceRecord
php artisan make:model Incident
php artisan make:model VehicleAssignment
```

#### Step 2: Define Model Relationships
- User hasOne Driver
- Driver belongsTo User
- Driver hasMany Trips
- Vehicle hasMany Trips
- Vehicle hasOne FuelCard
- Vehicle hasMany MaintenanceRecords

### Phase 4: Authentication & Authorization (Week 2-3)

#### Step 1: Install Laravel Sanctum
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

#### Step 2: Install Spatie Permission Package
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

#### Step 3: Create Role-Based Access Control
- Admin: Full system access
- Logistics: Vehicle and trip management
- Driver: Personal dashboard and trip updates

### Phase 5: API Development (Week 3-4)

#### Step 1: Create API Controllers
```bash
php artisan make:controller Api\AuthController
php artisan make:controller Api\DashboardController
php artisan make:controller Api\VehicleController
php artisan make:controller Api\TripController
php artisan make:controller Api\DriverController
php artisan make:controller Api\FuelCardController
php artisan make:controller Api\MaintenanceController
```

#### Step 2: Define API Routes
```php
// API Routes Structure
Route::prefix('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::apiResource('vehicles', VehicleController::class);
        Route::apiResource('trips', TripController::class);
        Route::apiResource('drivers', DriverController::class);
        Route::apiResource('fuel-cards', FuelCardController::class);
        Route::apiResource('maintenance', MaintenanceController::class);
    });
});
```

### Phase 6: Frontend Integration (Week 4-5)

#### Step 1: Update Frontend API Calls
- Replace mock data calls with Laravel API endpoints
- Implement authentication token management
- Add loading states and error handling

#### Step 2: Frontend File Structure
```
resources/
├── views/
│   ├── admin/
│   ├── logistics/
│   ├── driver/
│   └── auth/
├── js/
│   ├── components/
│   ├── pages/
│   └── services/
└── css/
```

#### Step 3: Alpine.js Integration
```javascript
// Example: Update Alpine.js data fetching
document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        trips: [],
        vehicles: [],
        loading: false,
        
        async fetchData() {
            this.loading = true;
            try {
                const response = await fetch('/api/dashboard', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                this.trips = data.trips;
                this.vehicles = data.vehicles;
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                this.loading = false;
            }
        }
    }));
});
```

### Phase 7: Data Migration (Week 5)

#### Step 1: Create Seeders
```bash
php artisan make:seeder UserSeeder
php artisan make:seeder VehicleSeeder
php artisan make:seeder DriverSeeder
php artisan make:seeder RoleSeeder
```

#### Step 2: Import Existing Data
- Convert existing CSV files to Laravel seeders
- Migrate existing vehicle data from PostgreSQL
- Create default admin user

#### Step 3: Data Validation
- Verify data integrity after migration
- Test all relationships
- Validate business logic

### Phase 8: Testing (Week 6)

#### Step 1: Create Test Cases
```bash
php artisan make:test UserTest
php artisan make:test VehicleTest
php artisan make:test TripTest
php artisan make:test AuthenticationTest
```

#### Step 2: API Testing
- Test all API endpoints
- Verify authentication and authorization
- Test role-based access control

#### Step 3: Frontend Testing
- Test Alpine.js components
- Verify data flow between frontend and backend
- Test responsive design

### Phase 9: Deployment Preparation (Week 7)

#### Step 1: Environment Setup
- Configure production environment
- Set up database connections
- Configure caching and queues

#### Step 2: Performance Optimization
- Implement caching strategies
- Optimize database queries
- Add API rate limiting

#### Step 3: Security Hardening
- Implement HTTPS
- Add CSRF protection
- Configure proper file permissions

## 3. Migration Timeline

### Week 1-2: Foundation
- [ ] Laravel project setup
- [ ] Database schema design
- [ ] Migration files creation
- [ ] Basic model setup

### Week 3-4: Backend Development
- [ ] API controller development
- [ ] Authentication system
- [ ] Role-based access control
- [ ] API testing

### Week 5-6: Frontend Integration
- [ ] Update Alpine.js components
- [ ] API integration
- [ ] Data migration
- [ ] User interface testing

### Week 7: Deployment & Testing
- [ ] Production environment setup
- [ ] Performance optimization
- [ ] Security implementation
- [ ] Final testing

## 4. Key Migration Considerations

### Data Integrity
- Backup existing data before migration
- Validate all data relationships
- Test data migration in staging environment

### User Experience
- Maintain existing UI/UX design
- Ensure seamless transition
- Provide user training if needed

### Performance
- Implement proper indexing
- Use Laravel's caching mechanisms
- Optimize database queries

### Security
- Implement proper authentication
- Use Laravel's built-in security features
- Regular security audits

## 5. Post-Migration Benefits

### Developer Experience
- Better code organization
- Built-in ORM and migration system
- Comprehensive testing framework

### Maintainability
- MVC architecture
- Dependency injection
- Automated testing

### Scalability
- Queue system for background jobs
- Caching mechanisms
- Database optimization tools

### Security
- Built-in security features
- Regular security updates
- Community support

## 6. Risk Mitigation

### Technical Risks
- **Data Loss**: Complete backup before migration
- **Downtime**: Parallel development and testing
- **Performance Issues**: Load testing and optimization

### Business Risks
- **User Disruption**: Gradual rollout strategy
- **Training**: Comprehensive user documentation
- **Support**: Dedicated support during transition

## 7. Success Metrics

### Technical Metrics
- API response time < 200ms
- Database query optimization
- 99.9% uptime
- Zero data loss

### Business Metrics
- User adoption rate
- Reduced support tickets
- Improved system reliability
- Enhanced user satisfaction

---

**Next Steps:**
1. Review and approve this migration plan
2. Set up development environment
3. Begin Phase 1 implementation
4. Regular progress reviews and adjustments