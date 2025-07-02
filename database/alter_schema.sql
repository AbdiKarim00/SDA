-- Alter the depreciation_rate column to increase precision
ALTER TABLE vehicles ALTER COLUMN depreciation_rate TYPE DECIMAL(15,2);

-- Alter the year_of_purchase column to use DATE type
ALTER TABLE vehicles ALTER COLUMN year_of_purchase TYPE DATE USING (year_of_purchase || '-01-01')::date; 