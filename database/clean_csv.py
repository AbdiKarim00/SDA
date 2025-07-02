import pandas as pd # type: ignore
import numpy as np # type: ignore
import re
import os

def clean_amount(amount_str):
    if pd.isna(amount_str) or amount_str == '' or amount_str == '-':
        return None
    # Remove commas and % symbol
    cleaned = str(amount_str).replace(',', '').replace('%', '')
    try:
        return float(cleaned)
    except ValueError:
        return None

def clean_year(year_str):
    if pd.isna(year_str) or year_str == '' or year_str == '-':
        return None
    try:
        # Try to convert to integer
        return int(str(year_str).strip())
    except ValueError:
        return None

def clean_csv():
    # Get the current directory
    current_dir = os.path.dirname(os.path.abspath(__file__))
    
    # Construct the full path to the input CSV file
    input_csv_path = os.path.join(current_dir, 'MV REGISTER (2)(1).csv')
    # Construct the full path to the output CSV file
    output_csv_path = os.path.join(current_dir, 'cleaned_vehicles.csv')
    
    print(f"Reading from: {input_csv_path}")
    print(f"Will save to: {output_csv_path}")
    
    # Read the CSV file
    df = pd.read_csv(input_csv_path, skiprows=1)
    
    # Clean column names
    df.columns = df.columns.str.strip()
    
    # Create a copy for cleaning
    cleaned_df = df.copy()
    
    # Fix column data mismatches
    for index, row in df.iterrows():
        # Check if Year of purchase is not a valid year
        if not pd.isna(row['Year of purchase']) and not str(row['Year of purchase']).isdigit():
            # Move the value to the appropriate column
            if 'POOL' in str(row['Year of purchase']):
                cleaned_df.at[index, 'Original Location'] = row['Year of purchase']
                cleaned_df.at[index, 'Year of purchase'] = None
            elif 'PAS' in str(row['Year of purchase']):
                cleaned_df.at[index, 'PV number'] = row['Year of purchase']
                cleaned_df.at[index, 'Year of purchase'] = None
        
        # Check if Current Location contains an amount
        if not pd.isna(row['Current Location']) and str(row['Current Location']).replace(',', '').replace('.', '').isdigit():
            # Move the amount to the Amount column
            cleaned_df.at[index, 'Amount'] = row['Current Location']
            cleaned_df.at[index, 'Current Location'] = row['Original Location']
    
    # Clean numeric fields
    numeric_columns = ['Amount', 'Depreciation rate', 'Annual depreciation', 
                      'Accumulated depreciation', 'Net Book Value', 'Disposal value']
    
    for col in numeric_columns:
        cleaned_df[col] = cleaned_df[col].apply(clean_amount)
    
    # Clean year fields
    year_columns = ['Year of purchase', 'Replacement Date (if applicable)', 'Date of disposal']
    for col in year_columns:
        cleaned_df[col] = cleaned_df[col].apply(clean_year)
    
    # Remove duplicate registration numbers (keep the first occurrence)
    cleaned_df = cleaned_df.drop_duplicates(subset=['Vehicle Registration No.'], keep='first')
    
    # Save the cleaned data
    cleaned_df.to_csv(output_csv_path, index=False)
    print("CSV file cleaned and saved as 'cleaned_vehicles.csv'")
    
    # Print summary of changes
    print("\nSummary of changes:")
    print(f"Original row count: {len(df)}")
    print(f"Cleaned row count: {len(cleaned_df)}")
    print(f"Removed duplicates: {len(df) - len(cleaned_df)}")

if __name__ == "__main__":
    clean_csv() 