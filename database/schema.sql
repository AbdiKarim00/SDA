-- Create the database


-- Create vehicle table
CREATE TABLE vehicles (
    id SERIAL PRIMARY KEY,
    registration_no VARCHAR(20) UNIQUE NOT NULL,
    financed_by VARCHAR(100),
    engine_no VARCHAR(50),
    chassis_no VARCHAR(50),
    tag_number VARCHAR(50),
    make_model VARCHAR(100),
    year_of_purchase INTEGER,
    pv_number VARCHAR(50),
    original_location VARCHAR(100),
    current_location VARCHAR(100),
    replacement_date DATE,
    amount DECIMAL(15,2),
    depreciation_rate DECIMAL(15,2),
    annual_depreciation DECIMAL(15,2),
    accumulated_depreciation DECIMAL(15,2),
    net_book_value DECIMAL(15,2),
    disposal_date DATE,
    disposal_value DECIMAL(15,2),
    responsible_officer VARCHAR(100),
    asset_condition VARCHAR(50),
    has_logbook BOOLEAN,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index on registration number for faster lookups
CREATE INDEX idx_vehicles_registration ON vehicles(registration_no);

-- Create function to update timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create trigger to automatically update updated_at
CREATE TRIGGER update_vehicles_updated_at
    BEFORE UPDATE ON vehicles
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column(); 