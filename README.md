# Transport IMS (Transport Information Management System)

A comprehensive transportation management system that includes a driver portal for managing trips, vehicles, and fuel consumption.

## Driver Portal Features

### 1. Dashboard (Home)
- Active trip information
- Vehicle status and details
- Fuel card management
  - Available fuel balance in litres
  - Daily fuel limits
  - Recent fuel history
  - Vehicle fuel information (tank capacity and type)

### 2. Trips
- View all assigned trips
- Trip details including:
  - Start and end locations
  - Trip purpose
  - Status tracking
  - Start and end times
  - Vehicle information
- Active trip management
  - Navigation assistance
  - Trip completion

### 3. Vehicle
- Current vehicle details
  - Registration number
  - Make and model
  - Current odometer reading
  - Maintenance schedule
- Maintenance history
  - Service records
  - Cost tracking
  - Issue reporting
- Odometer updates
- Maintenance issue reporting

### 4. Profile
- Personal information
  - Driver details
  - Contact information
- Compliance information
  - Driver's license status
  - Expiry tracking
- Incident reports
  - Historical incidents
  - Status tracking
  - Resolution details

## Technical Features

### Mobile-First Design
- Responsive layout for all screen sizes
- Bottom navigation for easy access
- Optimized for mobile devices

### Real-time Updates
- Live trip status (via API polling or WebSockets in the future)
- Instant fuel balance updates (via API)
- Maintenance status tracking (via API)

### Security
- **Authentication:** Handled by the Laravel API backend using token-based authentication (Laravel Sanctum).
- **Authorization:** Role-based access control is implemented in the Laravel API.
- **Data Encryption:** HTTPS should be used for all communication with the API. Database-level encryption handled by PostgreSQL if configured.

## System Architecture
The Transport IMS consists of two main parts:
1.  **PHP Frontend:** The user interface built with PHP, Alpine.js, and Tailwind CSS. This is the part you interact with directly in the browser (root directory of this project).
2.  **Laravel API Backend:** A Laravel 12 application located in the `transportims-laravel/` directory. This backend provides all data and handles business logic via a RESTful API.

**The PHP frontend is now dependent on the Laravel API backend.**

## Color Scheme
- Primary: #10B981 (Emerald Green)
- Success: #10B981
- Warning: #F59E0B
- Error: #EF4444
- Text: #1F2937
- Background: #F9FAFB

## Dependencies

### Frontend (this project):
- PHP 7.4+ (for serving the frontend files)
- Web server (e.g., Apache, Nginx)
- Alpine.js for interactivity
- Tailwind CSS for styling
- Bootstrap Icons for icons

### Backend (`transportims-laravel/` directory):
- PHP 8.0+
- Composer
- PostgreSQL Database
- Laravel 12.x
- (See `transportims-laravel/composer.json` for more backend dependencies)

## Installation

Setting up the Transport IMS involves running both the frontend and the backend.

### 1. Backend Setup (Laravel API)
   Ensure you have PHP, Composer, and PostgreSQL installed.
   ```bash
   # Navigate to the Laravel project directory
   cd transportims-laravel

   # Install PHP dependencies
   composer install

   # Create environment file (and configure it, especially DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
   cp .env.example .env
   php artisan key:generate

   # IMPORTANT: Update your .env file with your PostgreSQL database credentials.
   # Example .env settings (ensure these match your PostgreSQL setup):
   # DB_CONNECTION=pgsql
   # DB_HOST=127.0.0.1
   # DB_PORT=5432
   # DB_DATABASE=transport_ims_laravel # Ensure this database exists
   # DB_USERNAME=your_postgres_user
   # DB_PASSWORD=your_postgres_password

   # Run database migrations and seed initial data (users, roles, sample vehicles)
   php artisan migrate --seed

   # Start the Laravel development server (usually on http://localhost:8000)
   php artisan serve
   ```
   The API will be available at `http://localhost:8000/api/v1`.

### 2. Frontend Setup (PHP Application)
   This is the root of the current project.
   ```bash
   # 1. Clone the repository (if you haven't already)
   # git clone ...

   # 2. Configure your web server (Apache/Nginx) to serve the PHP files from the root directory of this project.
   #    Ensure URL rewriting is enabled if using .htaccess files (though not explicitly used by the refactored frontend pages yet for routing).
   #    The application assumes the Laravel API is running at http://localhost:8000.
   #    If the API is on a different URL, you may need to update the `apiBaseUrl` in the Alpine.js components within the PHP files (e.g., driver/index.php).

   # 3. Access the application through your web server's configured URL (e.g., http://localhost/transport-ims/).
   ```

### 3. Logging In
   - Access the main page (e.g., `index.php`).
   - You will need to implement or use a login mechanism that authenticates against the Laravel API (`POST /api/v1/login`).
   - Upon successful login, the API returns a token. This token needs to be stored (e.g., in `localStorage` as `driver_token`, `admin_token`, or a general `api_token`) and included in the `Authorization: Bearer <token>` header for subsequent API requests made by the Alpine.js components.
   - Default credentials (from `LARAVEL_MIGRATION_COMPLETE.md`):
     - Admin: `admin@transportims.com` / `password123`
     - Logistics: `logistics@transportims.com` / `password123`
     - Driver: `james.wilson@transportims.com` / `password123`

## Development Notes
- The PHP frontend pages in `driver/` and parts of `logistics/` have been refactored to fetch data from and submit data to the Laravel API.
- Many report pages and some detail pages in `logistics/` have had their mock data dependencies removed but are not yet fully refactored to use the API. They will currently show no data or limited functionality.
- API Base URL for frontend calls is generally `/api/v1` relative to the Laravel backend URL (assumed `http://localhost:8000`).

## Contributing
Please read our contributing guidelines before submitting pull requests.

## License
This project is licensed under the MIT License - see the LICENSE file for details.