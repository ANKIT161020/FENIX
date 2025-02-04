# FENIX

## 🚀 Overview

FENIX is an automated certificate processing system that extracts text from certificates using OCR (EasyOCR & Tesseract), sends the extracted data to **DeepSeek-R1**, and structures it into a readable format.

---

## 🔧 Installation Guide

### **1️⃣ Prerequisites**

Before running the project, ensure you have the following installed on your system:

- **Python 3.8+**
- **pip (Python Package Manager)**
- **Tesseract OCR** (for image text extraction)
- **DeepSeek-R1 Model** running on `localhost:11434`
- **Virtual Environment (Recommended)**

---

### **2️⃣ Installing Tesseract OCR**

Tesseract is required for text extraction from certificates.

#### **🔹 Ubuntu/Debian**

```bash
sudo apt update
sudo apt install tesseract-ocr -y
```

#### **🔹 macOS**

```bash
brew install tesseract
```

#### **🔹 Windows**

1. Download [Tesseract-OCR](https://github.com/UB-Mannheim/tesseract/wiki)
2. Install it and add the installation path to **System Environment Variables**.
3. Verify the installation by running:
   ```bash
   tesseract --version
   ```

---

### **3️⃣ Setting Up the Project**

1. Clone the repository:

   ```bash
   git clone https://github.com/your-username/fenix.git
   cd fenix
   ```

2. **(Optional but Recommended)** Create a Virtual Environment:

   ```bash
   python3 -m venv env
   source env/bin/activate  # Linux/Mac
   env\Scriptsctivate  # Windows
   ```

3. **Install Dependencies:**
   ```bash
   pip install -r requirements.txt
   ```

---

### **4️⃣ Running DeepSeek-R1 Locally**

DeepSeek-R1 should be running before executing the script.

1. Install **Ollama** (if not installed):

   - **Linux/macOS:**
     ```bash
     curl -fsSL https://ollama.ai/install.sh | sh
     ```
   - **Windows:**  
     Download and install from [Ollama](https://ollama.com)

2. Pull and start DeepSeek-R1:

   ```bash
   ollama pull deepseek-ai/deepseek-r1
   ollama run deepseek-ai/deepseek-r1 --port 11434
   ```

3. Verify DeepSeek-R1 is running:
   ```bash
   curl http://localhost:11434/api/generate -X POST -d '{"model": "deepseek-r1:1.5b", "prompt": "Hello", "stream": false}'
   ```

---

### **5️⃣ Running the Certificate Processing Script**

After completing the setup, run:

```bash
python3 process_certificates.py
```

**Expected Output:**

- Extracted text from certificates
- DeepSeek-R1 formatted output

---

## 📜 **Project Structure**

```
📂 fenix
│── 📂 certificate_layouts/     # Folder containing certificate images
│── 📜 process_certificates.py  # Main script
│── 📜 requirements.txt         # Required Python packages
│── 📜 README.md                # Setup instructions
```

---

## ⚙️ **Troubleshooting**

### ❌ **Error: "CUDA out of memory"**

- **Fix:** Run EasyOCR in CPU mode (`gpu=False`) in `process_certificates.py`.

### ❌ **Error: "DeepSeek-R1 not responding"**

- **Fix:** Ensure `ollama run deepseek-ai/deepseek-r1 --port 11434` is running.

### ❌ **Error: "Tesseract not found"**

- **Fix:** Ensure **Tesseract OCR** is installed and accessible via `tesseract --version`.

---

## 🛠 **Future Improvements**

- Add support for **PDF certificate extraction**
- Optimize OCR accuracy with **pre-processing techniques**
- Improve error handling for DeepSeek API failures

---

## ✨ **Contributors**

- **[Your Name]** - Developer
- **[Other Contributors]** - Contributions

---

🚀 **Now you're ready to automate certificate extraction & processing with FENIX!**

