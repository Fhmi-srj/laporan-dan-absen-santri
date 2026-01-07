"""
Import Data Induk from Excel to MySQL
Database: diantar2_absen
Table: data_induk
"""

import pandas as pd
import mysql.connector
from datetime import datetime
import re

# Database configuration
DB_CONFIG = {
    'host': '127.0.0.1',
    'port': 3306,
    'user': 'root',
    'password': '',
    'database': 'diantar2_absen'
}

# Excel file path
EXCEL_PATH = r"c:\laragon\www\Santri - Copy\data import\25_26 DATA INDUK MADIN .xlsx"

def clean_phone(phone):
    """Clean phone number - keep only digits"""
    if pd.isna(phone):
        return None
    phone = str(phone).strip()
    # Remove non-digit characters
    phone = re.sub(r'[^0-9]', '', phone)
    if not phone:
        return None
    return phone

def clean_date(date_val):
    """Convert date to MySQL format"""
    if pd.isna(date_val):
        return None
    if isinstance(date_val, datetime):
        return date_val.strftime('%Y-%m-%d')
    return None

def clean_string(val):
    """Clean string value"""
    if pd.isna(val):
        return None
    return str(val).strip() if val else None

def clean_nisn(val):
    """Clean NISN - convert number to string without scientific notation"""
    if pd.isna(val):
        return None
    # Handle numeric values (prevent scientific notation)
    if isinstance(val, (int, float)):
        return str(int(val))
    val = str(val).strip()
    # Remove any decimal points from scientific notation conversion
    if '.' in val and 'e' in val.lower():
        return None  # Invalid format
    return val if val else None

def clean_int(val):
    """Clean integer value"""
    if pd.isna(val):
        return None
    try:
        return int(float(val))
    except:
        return None

def clean_gender(val):
    """Convert gender to L/P"""
    if pd.isna(val):
        return None
    val = str(val).upper().strip()
    if val in ['L', 'LAKI-LAKI', 'LAKI', 'MALE']:
        return 'L'
    if val in ['P', 'PEREMPUAN', 'WANITA', 'FEMALE']:
        return 'P'
    return None

def main():
    print("=" * 60)
    print("Import Data Induk dari Excel ke MySQL")
    print("=" * 60)
    
    # Read Excel
    print(f"\nMembaca file: {EXCEL_PATH}")
    df = pd.read_excel(EXCEL_PATH, sheet_name='DATA INDUK', header=0)
    print(f"Total baris: {len(df)}")
    
    # Connect to database
    print(f"\nMenghubungkan ke database: {DB_CONFIG['database']}")
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    # Truncate existing data (optional - comment out if you want to append)
    cursor.execute("TRUNCATE TABLE data_induk")
    print("Tabel data_induk dikosongkan")
    
    # Prepare insert statement
    insert_sql = """
        INSERT INTO data_induk (
            no_urut, nama_lengkap, kelas, quran, kategori,
            nisn, lembaga_sekolah, status,
            tempat_lahir, tanggal_lahir, jenis_kelamin, jumlah_saudara,
            nomor_kk, nik, kecamatan, kabupaten, alamat,
            asal_sekolah, status_mukim,
            nama_ayah, tempat_lahir_ayah, tanggal_lahir_ayah, nik_ayah, pekerjaan_ayah, penghasilan_ayah,
            nama_ibu, tempat_lahir_ibu, tanggal_lahir_ibu, nik_ibu, pekerjaan_ibu, penghasilan_ibu,
            no_wa_wali
        ) VALUES (
            %s, %s, %s, %s, %s,
            %s, %s, %s,
            %s, %s, %s, %s,
            %s, %s, %s, %s, %s,
            %s, %s,
            %s, %s, %s, %s, %s, %s,
            %s, %s, %s, %s, %s, %s,
            %s
        )
    """
    
    # Import data
    success = 0
    errors = 0
    
    for idx, row in df.iterrows():
        try:
            data = (
                clean_int(row.get('NO')),
                clean_string(row.get('NAMA CALON SISWA')),
                clean_string(row.get('KELAS')),
                clean_string(row.get('QURAN')),
                clean_string(row.get('KATEGORI')),
                clean_nisn(row.get('NISN')),
                clean_string(row.get('LEMBAGA SEKOLAH')),
                clean_string(row.get('STATUS')) or 'AKTIF',
                clean_string(row.get('TEMPAT LAHIR')),
                clean_date(row.get('TANGGAL LAHIR')),
                clean_gender(row.get('JENIS KELAMIN')),
                clean_int(row.get('JUMLAH SAUDARA KANDUNG')),
                clean_string(row.get('NOMOR KK')),
                clean_string(row.get('NOMOR NIK ( Sesuai KK )')),
                clean_string(row.get('KECAMATAN')),
                clean_string(row.get('KABUPATEN')),
                clean_string(row.get('ALAMAT')),
                clean_string(row.get('ASAL SEKOLAH')),
                clean_string(row.get('STATUS MUKIM')),
                clean_string(row.get('NAMA AYAH')),
                clean_string(row.get('KOTA KELAHIRAN AYAH')),
                clean_date(row.get('TANGGAL KELAHIRAN AYAH')),
                clean_string(row.get('NIK AYAH')),
                clean_string(row.get('PEKERJAAN AYAH')),
                clean_string(row.get('PENGHASILAN PERBULAN')),
                clean_string(row.get('NAMA IBU')),
                clean_string(row.get('KOTA KELAHIRAN IBU')),
                clean_date(row.get('TANGGAL KELAHIRAN IBU')),
                clean_string(row.get('NIK IBU')),
                clean_string(row.get('PEKERJAAN IBU')),
                clean_string(row.get('PENGHASILAN PERBULAN.1')),
                clean_phone(row.get('NOMERHPWALIYANGAKTIF(WHATSAPP)'))
            )
            
            # Skip if no name
            if not data[1]:
                continue
                
            cursor.execute(insert_sql, data)
            success += 1
            
            if success % 50 == 0:
                print(f"  Imported: {success} rows...")
                
        except Exception as e:
            errors += 1
            print(f"  Error row {idx}: {e}")
    
    # Commit
    conn.commit()
    
    # Summary
    print("\n" + "=" * 60)
    print(f"SELESAI!")
    print(f"  Berhasil: {success} rows")
    print(f"  Error: {errors} rows")
    print("=" * 60)
    
    # Verify
    cursor.execute("SELECT COUNT(*) FROM data_induk")
    count = cursor.fetchone()[0]
    print(f"\nTotal data di tabel data_induk: {count}")
    
    cursor.close()
    conn.close()

if __name__ == "__main__":
    main()
