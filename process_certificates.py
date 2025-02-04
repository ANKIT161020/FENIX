import requests
import json
import pytesseract
import easyocr
from PIL import Image

# Initialize EasyOCR (CPU mode)
reader = easyocr.Reader(['en'], gpu=False)
TESSERACT_CONFIG = "--psm 6"

# List of certificate image file paths
file_paths = [
    "./certificate_layouts/25.jpeg",
    "./certificate_layouts/29.png",
    # Add more file paths if needed
]

# DeepSeek-R1 API endpoint
DEEPSEEK_R1_URL = "http://localhost:11434/api/generate"

# The keys you expect DeepSeek to include in its JSON response.
EXPECTED_JSON_KEYS = [
    "Recipient_Name",
    "Certificate_ID",
    "Verification_Link",
    "Start_Date",
    "End_Date",
    "Organization_Name"
]


def extract_text_easyocr(image_path):
    """Extract text using EasyOCR."""
    try:
        result = reader.readtext(image_path, detail=0)
        return ' '.join(result).strip()
    except Exception as e:
        print(f"Error extracting text with EasyOCR from {image_path}: {e}")
        return ""


def extract_text_tesseract(image_path):
    """Extract text using Tesseract OCR."""
    try:
        image = Image.open(image_path)
        text = pytesseract.image_to_string(image, config=TESSERACT_CONFIG)
        return text.strip()
    except Exception as e:
        print(f"Error extracting text with Tesseract from {image_path}: {e}")
        return ""


def extract_text_from_image(image_path):
    """Combine EasyOCR and Tesseract outputs for better accuracy."""
    text_easyocr = extract_text_easyocr(image_path)
    text_tesseract = extract_text_tesseract(image_path)
    # Merge both OCR outputs
    combined_text = text_easyocr + "\n" + text_tesseract
    return combined_text.strip()


def send_to_deepseek(extracted_text):
    """
    Sends the extracted text to DeepSeek-R1.
    The prompt instructs DeepSeek to extract the required fields and return only JSON.
    """
    if not extracted_text:
        return ""

    prompt = f"""
Extract the following details for each certificate found in the text below:
- Recipient_Name
- Certificate_ID
- Verification_Link (if present)
- Start_Date
- End_Date
- Organization_Name

Return the extracted details as valid JSON but do not include you thinking part. The JSON should be an array of objects, each with the following keys exactly:
{json.dumps(EXPECTED_JSON_KEYS)}

If a field is missing for a certificate, set its value to an empty string.
Do not include any additional commentary or markdown.

Certificate Text:
{extracted_text}
"""

    payload = {
        "model": "deepseek-r1:1.5b",
        "prompt": prompt,
        "stream": False
    }
    headers = {"Content-Type": "application/json"}

    try:
        response = requests.post(DEEPSEEK_R1_URL, json=payload, headers=headers)
        response.raise_for_status()
        return response.text  # Raw response text
    except requests.exceptions.RequestException as e:
        print(f"Error sending request to DeepSeek-R1: {e}")
        return ""


def print_deepseek_response(response_text):
    """
    Tries to parse the DeepSeek response as JSON and prints only the 'response' field.
    If parsing fails, prints the raw response.
    """
    try:
        # Remove any leading/trailing whitespace
        response_text = response_text.strip()
        data = json.loads(response_text)
        # Print only the value of the "response" key.
        # (This omits other keys like model, created_at, context, etc.)
        print("\nDeepSeek-R1 Response:")
        print(data.get("response", "No 'response' field found in the output."))
    except json.JSONDecodeError as e:
        print("Error parsing JSON from DeepSeek response:")
        print(e)
        print("\nRaw DeepSeek-R1 Response:")
        print(response_text)


def main():
    for image_path in file_paths:
        print(f"\nProcessing: {image_path}")

        # Step 1: Extract text from image using OCR
        extracted_text = extract_text_from_image(image_path)
        print("\nExtracted Text:")
        print(extracted_text)

        # Step 2: Send extracted text to DeepSeek-R1
        deepseek_raw_response = send_to_deepseek(extracted_text)
        # Step 3: Print only the 'response' field from DeepSeek's JSON output
        print_deepseek_response(deepseek_raw_response)


if __name__ == "__main__":
    main()
