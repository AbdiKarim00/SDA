import pandas as pd # type: ignore
import psycopg2 # type: ignore
from psycopg2 import sql # type: ignore
import os
from datetime import datetime
import re

def clean_amount(amount_str):
    if pd.isna(amount_str) or amount_str == '' or amount_str == '-':
        return None
    # Remove commas and % symbol
    cleaned = str(amount_str).replace(',', '').replace('%', '')
    try:
        return float(cleaned)
    except ValueError:
        return None

def clean_date(date_str):
    """Clean and convert date string to 'YYYY-01-01' or None."""
    if pd.isna(date_str):
        return None
    try:
        # If it's already a float/int, convert to int and format
        if isinstance(date_str, (float, int)):
            year = int(date_str)
            return f"{year}-01-01"
        # If it's a string, try to extract year
        if isinstance(date_str, str):
            # Try to extract year from YYYY-MM-DD format
            if '-' in date_str:
                year = int(date_str.split('-')[0])
                return f"{year}-01-01"
            # Try to extract year from other formats
            year = int(date_str)
            return f"{year}-01-01"
        return None
    except (ValueError, TypeError):
        return None

def clean_boolean(value):
    if pd.isna(value) or value == '' or value == '-':
        return False
    return str(value).upper() == 'Y'

def clean_year(year_str):
    if pd.isna(year_str) or year_str == '' or year_str == '-':
        return None
    try:
        # Try to convert to integer
        return int(str(year_str).strip())
    except ValueError:
        return None

def connect_to_db():
    return psycopg2.connect(
        dbname="transport_ims",
        user="postgres",  # Replace with your PostgreSQL username
        password="1212",      # Replace with your PostgreSQL password
        host="localhost",
        port="5432"
    )

def import_vehicles():
    # Get the current directory
    current_dir = os.path.dirname(os.path.abspath(__file__))
    # Construct the full path to the cleaned CSV file
    csv_path = os.path.join(current_dir, 'cleaned_vehicles.csv')
    
    # Read the CSV file
    print(f"Attempting to read cleaned CSV file from: {csv_path}")
    df = pd.read_csv(csv_path)
    
    # Print column names to debug
    print("\nCSV Column names:")
    print(df.columns.tolist())
    print("\nFirst row of data:")
    print(df.iloc[0])
    
    # Connect to the database
    conn = connect_to_db()
    cur = conn.cursor()
    
    # Prepare the insert statement
    insert_query = sql.SQL("""
        INSERT INTO vehicles (
            registration_no, financed_by, engine_no, chassis_no, tag_number,
            make_model, year_of_purchase, pv_number, original_location,
            current_location, replacement_date, amount, depreciation_rate,
            annual_depreciation, accumulated_depreciation, net_book_value,
            disposal_date, disposal_value, responsible_officer, asset_condition,
            has_logbook, notes
        ) VALUES (
            %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
            %s, %s, %s, %s, %s, %s
        )
        ON CONFLICT (registration_no) DO UPDATE SET
            financed_by = EXCLUDED.financed_by,
            engine_no = EXCLUDED.engine_no,
            chassis_no = EXCLUDED.chassis_no,
            tag_number = EXCLUDED.tag_number,
            make_model = EXCLUDED.make_model,
            year_of_purchase = EXCLUDED.year_of_purchase,
            pv_number = EXCLUDED.pv_number,
            original_location = EXCLUDED.original_location,
            current_location = EXCLUDED.current_location,
            replacement_date = EXCLUDED.replacement_date,
            amount = EXCLUDED.amount,
            depreciation_rate = EXCLUDED.depreciation_rate,
            annual_depreciation = EXCLUDED.annual_depreciation,
            accumulated_depreciation = EXCLUDED.accumulated_depreciation,
            net_book_value = EXCLUDED.net_book_value,
            disposal_date = EXCLUDED.disposal_date,
            disposal_value = EXCLUDED.disposal_value,
            responsible_officer = EXCLUDED.responsible_officer,
            asset_condition = EXCLUDED.asset_condition,
            has_logbook = EXCLUDED.has_logbook,
            notes = EXCLUDED.notes
    """)
    
    # Process each row
    for index, row in df.iterrows():
        try:
            # Start a new transaction for each row
            cur.execute("BEGIN")
            
            # Clean and prepare the data
            values = (
                str(row['Vehicle Registration No.']).strip(),
                str(row['Financed by/ source of funds']).strip() if pd.notna(row['Financed by/ source of funds']) else None,
                str(row['Engine No.']).strip() if pd.notna(row['Engine No.']) else None,
                str(row['Chassis No.']).strip() if pd.notna(row['Chassis No.']) else None,
                str(row['Tag number/']).strip() if pd.notna(row['Tag number/']) else None,
                str(row['Make & Model']).strip() if pd.notna(row['Make & Model']) else None,
                clean_date(row['Year of purchase']),  # Convert to date
                str(row['PV number']).strip() if pd.notna(row['PV number']) else None,
                str(row['Original Location']).strip() if pd.notna(row['Original Location']) else None,
                str(row['Current Location']).strip() if pd.notna(row['Current Location']) else None,
                clean_date(row['Replacement Date (if applicable)']),  # Convert to date
                clean_amount(row['Amount']),  # Use clean_amount function
                clean_amount(row['Depreciation rate']),  # Use clean_amount function
                clean_amount(row['Annual depreciation']),  # Use clean_amount function
                clean_amount(row['Accumulated depreciation']),  # Use clean_amount function
                clean_amount(row['Net Book Value']),  # Use clean_amount function
                clean_date(row['Date of disposal']),  # Convert to date
                clean_amount(row['Disposal value']),  # Use clean_amount function
                str(row['Responsible officer']).strip() if pd.notna(row['Responsible officer']) else None,
                str(row['Asset condition']).strip() if pd.notna(row['Asset condition']) else None,
                clean_boolean(row['Does the MV have a log book(Y/N)']),
                str(row['Notes']).strip() if pd.notna(row['Notes']) else None
            )
            
            # Execute the insert
            cur.execute(insert_query, values)
            cur.execute("COMMIT")
            print(f"Successfully imported vehicle: {values[0]}")  # Print registration number for each successful import
            
        except Exception as e:
            cur.execute("ROLLBACK")
            print(f"Error processing row {index + 1}: {row.get('Vehicle Registration No.', 'Unknown')}")
            print(f"Error details: {str(e)}")
            print(f"Row data: {row.to_dict()}")  # Print the full row data for debugging
            continue
    
    # Close the connection
    cur.close()
    conn.close()
    print("Import completed!")

if __name__ == "__main__":
    import_vehicles() 