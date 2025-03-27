#!/usr/bin/env python3
import os

# Unset LD_LIBRARY_PATH to avoid conflicts with LAMPP libraries
if 'LD_LIBRARY_PATH' in os.environ:
    del os.environ['LD_LIBRARY_PATH']

import sys
import json
import csv
import requests
import pytesseract
import easyocr
from PIL import Image

# Set absolute paths for EasyOCR directories
model_dir = "/opt/lampp/htdocs/fenix/easyocr_models"
user_network_dir = "/opt/lampp/htdocs/fenix/easyocr_user_network"

# Create the directories if they don't exist
if not os.path.exists(model_dir):
    os.makedirs(model_dir, exist_ok=True)
if not os.path.exists(user_network_dir):
    os.makedirs(user_network_dir, exist_ok=True)

# Initialize EasyOCR with custom directories
reader = easyocr.Reader(
    ['en'],
    gpu=False,
    model_storage_directory=model_dir,
    user_network_directory=user_network_dir
)

# Tesseract config
TESSERACT_CONFIG = "--psm 6"

# DeepSeek API endpoint
DEEPSEEK_R1_URL = "http://localhost:11434/api/generate"

# The keys we expect in the final JSON for a marksheet
EXPECTED_JSON_KEYS = [
    "Student_Name",
    "Seat_Number",
    "Examination",
    "Semester",
    "Institute_Name",
    "Subjects",         # Possibly an array of subject details
    "Total_Marks",
    "SGPA",
    "CGPA",
    "Held_In"           # e.g. "December 2022"
]

def extract_text_easyocr(image_path):
    """Extract text using EasyOCR."""
    try:
        result = reader.readtext(image_path, detail=0)
        ocr_text = ' '.join(result).strip()
        print(f"[DEBUG] EasyOCR result for {image_path}: {ocr_text}", file=sys.stderr)
        return ocr_text
    except Exception as e:
        print(f"[ERROR] EasyOCR error on {image_path}: {e}", file=sys.stderr)
        return ""

def extract_text_tesseract(image_path):
    """Extract text using Tesseract OCR."""
    try:
        image = Image.open(image_path)
        text = pytesseract.image_to_string(image, config=TESSERACT_CONFIG).strip()
        print(f"[DEBUG] Tesseract result for {image_path}: {text}", file=sys.stderr)
        return text
    except Exception as e:
        print(f"[ERROR] Tesseract error on {image_path}: {e}", file=sys.stderr)
        return ""

def extract_text_from_image(image_path):
    """Combine EasyOCR and Tesseract outputs for better accuracy."""
    text_easyocr = extract_text_easyocr(image_path)
    text_tesseract = extract_text_tesseract(image_path)
    combined_text = text_easyocr + "\n" + text_tesseract
    print(f"[DEBUG] Combined OCR text for {image_path}: {combined_text}", file=sys.stderr)
    return combined_text.strip()

def send_to_deepseek(extracted_text):
    """
    Send the extracted text to DeepSeek-R1 and return the parsed JSON response.
    We'll ask it to parse typical marksheet fields as defined in EXPECTED_JSON_KEYS.
    """
    if not extracted_text:
        print("[WARNING] No extracted text to send to DeepSeek.", file=sys.stderr)
        return []

    prompt = f"""
You are an AI that extracts data from a university marksheet. 
Please return a valid JSON array of exactly one object (if the marksheet is for one student) 
with the following keys: {EXPECTED_JSON_KEYS}.

Definitions:
- Student_Name: The full name of the student.
- Seat_Number: The seat or roll number assigned to the student.
- Examination: The exam name (e.g. "F.E. Sem 1", "BE Semester VII").
- Semester: The specific semester if provided (or just replicate Examination).
- Institute_Name: The name of the college or university (e.g. "K.J. Somaiya Institute ...").
- Subjects: A list (array) of subject details, each with sub-keys like subject code, subject name, credits, grade, etc.
- Total_Marks: The total marks obtained or out of.
- SGPA: The semester GPA if shown.
- CGPA: The cumulative GPA if shown.
- Held_In: The month/year or session (e.g. "Dec 2021").

If any field is not found, set its value to an empty string or an empty array (for Subjects).
Do not include any explanation or markdown, just return the JSON array.
Text to parse:
{extracted_text}
"""

    payload = {
        "model": "deepseek-r1:1.5b",
        "prompt": prompt,
        "stream": False
    }
    headers = {"Content-Type": "application/json"}

    print("[DEBUG] Sending payload to DeepSeek:", file=sys.stderr)
    print(json.dumps(payload, indent=2), file=sys.stderr)

    try:
        response = requests.post(DEEPSEEK_R1_URL, json=payload, headers=headers)
        response.raise_for_status()
        raw_response = response.text.strip()
        print("[DEBUG] Raw response from DeepSeek:", file=sys.stderr)
        print(raw_response, file=sys.stderr)

        data = response.json()
        if "response" in data:
            response_text = data["response"]
            # Extract the JSON array from the response text.
            json_start = response_text.find('[')
            if json_start != -1:
                json_text = response_text[json_start:]
                try:
                    parsed = json.loads(json_text)
                    return parsed
                except Exception as parse_err:
                    print(f"[ERROR] Could not parse JSON from extracted text: {parse_err}", file=sys.stderr)
                    print(f"[DEBUG] Extracted JSON text: {json_text}", file=sys.stderr)
                    return []
            else:
                print("[ERROR] Could not find JSON array in the response.", file=sys.stderr)
                return []
        else:
            print("[ERROR] 'response' field not found in DeepSeek output.", file=sys.stderr)
            return data
    except Exception as e:
        print(f"[ERROR] Error calling DeepSeek: {e}", file=sys.stderr)
        return []

def main():
    if len(sys.argv) < 2:
        print("No file paths provided.", file=sys.stderr)
        sys.exit(1)

    # 1. Parse the JSON array of file paths
    file_paths_json = sys.argv[1]
    try:
        file_paths = json.loads(file_paths_json)
    except json.JSONDecodeError as e:
        print(f"[ERROR] Invalid JSON input: {e}", file=sys.stderr)
        sys.exit(1)

    # 2. For each marksheet, do OCR + send to DeepSeek
    all_marksheet_data = []
    for path in file_paths:
        if not os.path.isfile(path):
            print(f"[ERROR] File not found: {path}", file=sys.stderr)
            continue

        print(f"[INFO] Processing file: {path}", file=sys.stderr)
        extracted_text = extract_text_from_image(path)
        print(f"[INFO] Extracted text (length {len(extracted_text)}): {extracted_text}", file=sys.stderr)

        # Attempt to parse the extracted text via DeepSeek
        deepseek_result = send_to_deepseek(extracted_text)
        # We expect a list of (usually 1) JSON objects
        for item in deepseek_result:
            # Tag with file path so we know which marksheet it came from
            item["File_Path"] = path
            all_marksheet_data.append(item)

    # 3. Generate a CSV file if any data was extracted
    if not all_marksheet_data:
        print("[ERROR] No data extracted. Exiting.", file=sys.stderr)
        sys.exit(1)

    output_dir = os.path.join(os.path.dirname(__file__), "downloads")
    if not os.path.isdir(output_dir):
        os.makedirs(output_dir, exist_ok=True)

    import uuid
    csv_filename = "marksheets_extracted_" + uuid.uuid4().hex + ".csv"
    csv_path = os.path.join(output_dir, csv_filename)

    # CSV fieldnames
    # We'll store "Subjects" as a string unless you want more advanced splitting
    fieldnames = EXPECTED_JSON_KEYS + ["File_Path"]
    with open(csv_path, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        for row in all_marksheet_data:
            # Ensure every field is present
            for key in fieldnames:
                if key not in row:
                    row[key] = ""
                # Convert the array of subjects to JSON if needed
                if key == "Subjects" and isinstance(row[key], list):
                    row[key] = json.dumps(row[key])
            writer.writerow(row)

    # 4. Print the CSV path for your PHP script to pick up
    print(csv_path)

if __name__ == "__main__":
    main()
