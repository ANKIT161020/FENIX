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

Below is the content you can copy into a file named `README.md`:

````markdown
# FenX Project Permissions Setup

This document outlines the required file and directory permissions for the FenX project. Follow these steps to ensure that your uploads, downloads, and EasyOCR directories are correctly configured for both Linux (LAMPP) and Windows (XAMPP).

## Linux Permissions Setup (LAMPP)

Make sure your project is located at `/opt/lampp/htdocs/fenix/` and that the web server user (typically `daemon`) has the correct permissions.

### 1. Uploads Folder

Grant ownership and set permissions for the uploads folder:

```bash
sudo chown -R daemon:daemon /opt/lampp/htdocs/fenix/uploads
sudo chmod -R 775 /opt/lampp/htdocs/fenix/uploads
```
````

### 2. Downloads Folder

Grant ownership and set permissions for the downloads folder:

```bash
sudo chown -R daemon:daemon /opt/lampp/htdocs/fenix/downloads
sudo chmod -R 775 /opt/lampp/htdocs/fenix/downloads
```

### 3. EasyOCR Directories

Create and set permissions for the EasyOCR directories:

```bash
sudo mkdir -p /opt/lampp/htdocs/fenix/easyocr_models
sudo chown -R daemon:daemon /opt/lampp/htdocs/fenix/easyocr_models
sudo chmod -R 775 /opt/lampp/htdocs/fenix/easyocr_models

sudo mkdir -p /opt/lampp/htdocs/fenix/easyocr_user_network
sudo chown -R daemon:daemon /opt/lampp/htdocs/fenix/easyocr_user_network
sudo chmod -R 775 /opt/lampp/htdocs/fenix/easyocr_user_network
```

## Windows Permissions Setup (XAMPP)

Assuming your project is located at `C:\xampp\htdocs\fenix\`, you can set the permissions as follows:

### Using Command Prompt (Run as Administrator)

#### 1. Uploads Folder

Open a Command Prompt as Administrator and run:

```cmd
icacls "C:\xampp\htdocs\fenix\uploads" /grant "Everyone":(OI)(CI)F /T
```

#### 2. Downloads Folder

```cmd
icacls "C:\xampp\htdocs\fenix\downloads" /grant "Everyone":(OI)(CI)F /T
```

#### 3. EasyOCR Directories

```cmd
icacls "C:\xampp\htdocs\fenix\easyocr_models" /grant "Everyone":(OI)(CI)F /T
icacls "C:\xampp\htdocs\fenix\easyocr_user_network" /grant "Everyone":(OI)(CI)F /T
```

### Using Windows File Explorer

1. Navigate to the folder (e.g., `C:\xampp\htdocs\fenix\uploads`).
2. Right-click the folder and choose **Properties**.
3. Go to the **Security** tab.
4. Click **Edit**, then **Add** (if needed, add the user under which Apache runs or simply "Everyone").
5. Grant the desired permissions (typically "Modify" or "Full Control").
6. Click **Apply** and **OK**.
7. Repeat these steps for:
   - `C:\xampp\htdocs\fenix\downloads`
   - `C:\xampp\htdocs\fenix\easyocr_models`
   - `C:\xampp\htdocs\fenix\easyocr_user_network`

---

By following these steps, your project directories will be correctly configured to allow your web server (Apache) to read from and write to the necessary folders.
