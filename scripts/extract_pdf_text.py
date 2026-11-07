#!/usr/bin/env python3
"""
PDF Text Extraction Script
Uses pdfplumber / pdfminer with robust error handling.
"""

import sys
import json
import os

def extract_text(pdf_path: str) -> str:
    if not os.path.isfile(pdf_path):
        return ""
    
    # Try pdfplumber first
    try:
        import pdfplumber
        text_parts = []
        with pdfplumber.open(pdf_path) as pdf:
            for page in pdf.pages:
                page_text = page.extract_text()
                if page_text:
                    text_parts.append(page_text)
        if text_parts:
            return "\n\n".join(text_parts).strip()
    except Exception as e:
        sys.stderr.write(f"pdfplumber failed: {e}\n")

    # Try pdfminer.high_level as backup
    try:
        from pdfminer.high_level import extract_text as miner_extract
        text = miner_extract(pdf_path)
        if text and text.strip():
            return text.strip()
    except Exception as e:
        sys.stderr.write(f"pdfminer failed: {e}\n")

    # Try pypdf / pypdf2 as backup
    try:
        import pypdf
        reader = pypdf.PdfReader(pdf_path)
        parts = []
        for p in reader.pages:
            t = p.extract_text()
            if t:
                parts.append(t)
        if parts:
            return "\n\n".join(parts).strip()
    except Exception:
        pass

    return ""

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No file provided"}))
        sys.exit(1)
        
    pdf_path = sys.argv[1]
    text = extract_text(pdf_path)
    print(json.dumps({"success": True, "text": text, "length": len(text)}))

if __name__ == "__main__":
    main()
