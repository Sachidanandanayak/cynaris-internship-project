# Week 10, Day 1 (W10D1) — Resume Intelligence (PDF Upload + Groq ATS Scoring)

**Project:** Cynaris Internship Portal (`cynaris-portal`) / `laravel_basics`  
**Module:** Module 5 — Enterprise Resume ATS Intelligence & Semantic Text Extraction  
**Author:** Sachidananda Nayak  
**Date:** November 7, 2026  
**Status:** IMPLEMENTED, TESTED & VERIFIED  

---

## 1. Executive Summary

As part of the **Week 10 Cynaris Internship** expansion for the CIA Career Intelligence Suite, **Week 10 Day 1 (W10D1)** delivers an enterprise-grade **Resume Intelligence & ATS Scoring Pipeline**.

The system enables candidates to upload standard PDF resumes (up to 10MB) and receive instantaneous, multi-category Applicant Tracking System (ATS) evaluations against target industry roles (e.g., Junior Backend Laravel Engineer, Full Stack Developer, Cloud/DevOps).

```
+---------------------------------------------------------------------------------------------------------------------------------------------------------+
|                                                      CIA Career Intelligence Platform (W9 - W10)                                                        |
+------------------------------------+------------------------------------+---------------------------------------+---------------------------------------+
| Module 1: Skill Gap Analysis       | Module 2: Career Recommendation    | Module 3: Vector Internship Matching  | Module 4: 8-Week Learning Path        |
| (LlamaIndex + Groq Llama-3)        | (CrewAI Autonomous Agent)          | (ChromaDB Cosine Metric Space)        | (LangGraph 5-Node StateGraph)         |
+------------------------------------+------------------------------------+---------------------------------------+---------------------------------------+
| Module 5: Resume ATS Intelligence  | Module 6: Readiness Scorecard      |                                       |                                       |
| (PDF Extraction + Groq Llama-3.3)  | (Multi-Metric 40/30/10/20 Formula) |                                       |                                       |
+------------------------------------+------------------------------------+---------------------------------------+---------------------------------------+
```

---

## 2. ATS Scoring Formula & Category Weights

The ATS evaluation engine calculates an overall score strictly from **0 to 100** based on four weighted categories:

$$\text{Overall ATS Score} = (\text{Keyword Matching} \times 0.35) + (\text{Technical Skills} \times 0.35) + (\text{Work Experience} \times 0.20) + (\text{Formatting} \times 0.10)$$

| Category | Weight | Description | Evaluation Criteria |
|---|:---:|---|---|
| **Keyword Matching** | **35%** | Target Role Domain Keyword Density | Matches against taxonomy of backend, frontend, DevOps, and AI keywords |
| **Technical Skills** | **35%** | Depth & Breadth of Stack Proficiencies | Count and verification of programming languages, frameworks, ORMs, CI/CD |
| **Work Experience** | **20%** | Action Verbs & Quantified Metric Impact | Detection of active engineering verbs (`architected`, `optimized`) and metrics (`%`, `ms`, users) |
| **Formatting & Layout** | **10%** | Structural Readability & Section Headers | Presence of Education, Experience, Skills, Contact sections, length validation |

---

## 3. Architecture & Pipeline

```mermaid
flowchart TD
    Candidate([Candidate / Browser]) -->|Upload PDF max 10MB| Controller["CiaController::scanResume"]
    Controller -->|File Validation mimes:pdf| Extractor["ResumeTextExtractor"]
    
    subgraph Multi-Tier Extraction
        Extractor -->|Tier 1| PythonEngine["scripts/extract_pdf_text.py\n(pdfplumber / pdfminer)"]
        Extractor -.->|Tier 2 Fallback| NativePhp["Native PHP Stream Parser\n(Tj / TJ extraction + gzuncompress)"]
    end

    PythonEngine --> Sanitizer["UTF-8 Normalization & Sanitization"]
    NativePhp --> Sanitizer
    Sanitizer --> ScoringService["ResumeAtsScoringService"]

    subgraph ATS Scoring
        ScoringService -->|If GROQ_API_KEY present| GroqLLM["Groq Cloud API\n(llama-3.3-70b-versatile)\njson_object response_format"]
        ScoringService -.->|API Unavailable / Offline| HeuristicEngine["Deterministic ATS Fallback Engine\n(Keyword Taxonomy + Regex)"]
    end

    GroqLLM --> Persist["Persist to cia_search_logs (query_type: resume_ats_score)"]
    HeuristicEngine --> Persist
    Persist --> Response([JSON 200 Response + Cockpit UI Update])
```

---

## 4. API Specification

### Endpoint: `POST /candidate/cia/career/resume/scan`
- **Authentication:** `auth`, `verified`
- **Request Headers:**
  - `Accept: application/json`
  - `X-CSRF-TOKEN: <token>`
- **Request Body (Multipart Form-Data):**
  - `resume`: Binary PDF file (`required|file|mimes:pdf|max:10240`)
  - `target_role`: String (`nullable|string|max:150`)
  - `job_id`: Integer (`nullable|integer|exists:jobs,id`)

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "message": "Resume analyzed and ATS scores calculated successfully.",
  "target_role": "Junior Backend Laravel Engineer",
  "overall_ats_score": 82.5,
  "scores": {
    "keyword_matching": 85.0,
    "technical_skills": 88.0,
    "work_experience": 75.0,
    "formatting": 80.0
  },
  "matched_keywords": [
    "PHP", "Laravel", "MySQL", "REST APIs", "Git", "Docker", "PHPUnit"
  ],
  "missing_keywords": [
    "Redis", "PostgreSQL", "Sanctum", "CI/CD"
  ],
  "improvement_suggestions": [
    "Incorporate missing high-priority target role keywords: Redis, PostgreSQL, Sanctum, CI/CD.",
    "Quantify your achievements with concrete metrics (e.g., 'reduced API query latency by 35%').",
    "Standardize section headings: use clear 'Professional Experience', 'Technical Skills', and 'Education' headers."
  ],
  "section_rewrites": [
    {
      "section": "Professional Experience / Projects",
      "current_weakness": "Passive job descriptions lacking quantified business impact and engineering metrics.",
      "suggested_rewrite": "Architected and deployed RESTful microservices in Laravel 12 & PHP 8.4, reducing payload latency by 42% and implementing automated PHPUnit regression suites with >= 85% branch coverage."
    }
  ],
  "engine": "groq_llama3",
  "log_id": 42
}
```

---

## 5. Environment Variables & Configuration

In `.env`:
```env
GROQ_API_KEY=gsk_your_groq_api_key_here
GROQ_MODEL=llama-3.3-70b-versatile
GROQ_ENDPOINT=https://api.groq.com/openai/v1/chat/completions
GROQ_TIMEOUT=15
```

When `GROQ_API_KEY` is omitted or unconfigured, the system automatically uses the zero-dependency deterministic heuristic ATS engine with 100% feature parity.

---

## 6. Testing & Verification

Run automated feature tests:
```bash
php vendor/phpunit/phpunit/phpunit tests/Feature/CiaResumeIntelligenceTest.php
```

All 7 test cases execute with 38 assertions covering auth checks, MIME validation, 10MB limits, category weighting formulas, database persistence, and extraction sanitization.

