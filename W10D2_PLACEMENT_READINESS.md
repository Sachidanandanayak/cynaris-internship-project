# Week 10, Day 2 (W10D2) — Placement Readiness Score & Interview Readiness Score

**Project:** Cynaris Internship Portal (`cynaris-portal`) / `laravel_basics`  
**Module:** Module 6 — Multi-Metric Placement & Interview Readiness Scorecard  
**Author:** Sachidananda Nayak  
**Date:** November 7, 2026  
**Status:** IMPLEMENTED, TESTED & VERIFIED  

---

## 1. Executive Summary

**Week 10 Day 2 (W10D2)** implements the **Placement Readiness Score & Interview Readiness Scorecard** engine for the Cynaris Internship Platform.

This engine synthesizes a candidate's holistic performance across all dimensions of their engineering lifecycle:
- Relational lesson milestone completions and quiz percentages
- Telemetry activity logs from the CIA Career Intelligence Cockpit
- Mentor evaluations and structured technical assessments
- Viva examinations, peer code reviews, demo clarity, and Git hygiene

All scores are mathematically calculated via strictly defined weighted linear combinations, strictly clamped between **0.0 and 100.0**, and permanently audited in the `placement_readiness_scores` database table.

---

## 2. Mathematical Scoring Formulas & Weights

### 2.1 Placement Readiness Score

The primary hiring readiness index evaluates programmatic coursework, assessment rigor, platform engagement, and project portfolio documentation:

$$\text{Placement Readiness} = (\text{Project Completion} \times 0.40) + (\text{Assessment} \times 0.30) + (\text{CIA Usage} \times 0.10) + (\text{Documentation} \times 0.20)$$

| Metric | Weight | Source & Derivation |
|---|:---:|---|
| **Project Completion** | **40%** | Percentage of completed curriculum modules and capstone milestones |
| **Assessment** | **30%** | Weighted average score achieved across all technical quizzes and exams |
| **CIA Usage** | **10%** | Platform telemetry: skill gap queries, vector match queries, learning path generation |
| **Documentation** | **20%** | Architecture designs, API contracts, README documentation, and code annotations |

### 2.2 Interview Readiness Score

The interview readiness benchmark assesses an engineer's technical communication, code explanation, presentation articulation, and commit craftsmanship:

$$\text{Interview Readiness} = (\text{Viva} \times 0.40) + (\text{Code Review} \times 0.30) + (\text{Demo Clarity} \times 0.20) + (\text{Git Quality} \times 0.10)$$

| Metric | Weight | Source & Derivation |
|---|:---:|---|
| **Technical Viva** | **40%** | Live conceptual oral examination, system design rationale, and verbal defense |
| **Code Review Rigor** | **30%** | Adherence to PSR-12, clean architecture, automated testing, and peer feedback |
| **Demo Clarity** | **20%** | Live feature walkthrough, error handling explanation, and UI presentation |
| **Git Quality** | **10%** | Atomic commits, conventional commit messages, feature branching, and clean PR history |

---

## 3. Database Schema (`placement_readiness_scores`)

Migration: `2026_11_07_000001_create_placement_readiness_scores_table.php`

```sql
CREATE TABLE placement_readiness_scores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    project_completion_score FLOAT DEFAULT 0.0,
    assessment_score FLOAT DEFAULT 0.0,
    cia_usage_score FLOAT DEFAULT 0.0,
    documentation_score FLOAT DEFAULT 0.0,
    placement_readiness_score FLOAT DEFAULT 0.0,
    viva_score FLOAT DEFAULT 0.0,
    code_review_score DEFAULT 0.0,
    demo_clarity_score DEFAULT 0.0,
    git_quality_score DEFAULT 0.0,
    interview_readiness_score FLOAT DEFAULT 0.0,
    breakdown JSON,
    status VARCHAR(255) DEFAULT 'evaluated',
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 4. API Endpoints

### 4.1 Calculate & Persist Readiness: `POST /candidate/cia/career/readiness/calculate`
- **Authentication:** `auth`, `verified`
- **Request Body (JSON):**
```json
{
  "project_completion_score": 90.0,
  "assessment_score": 85.0,
  "cia_usage_score": 75.0,
  "documentation_score": 85.0,
  "viva_score": 80.0,
  "code_review_score": 90.0,
  "demo_clarity_score": 85.0,
  "git_quality_score": 95.0,
  "notes": "Mid-term comprehensive evaluation."
}
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "message": "Placement and Interview readiness scores evaluated successfully.",
  "data": {
    "user_id": 1,
    "project_completion_score": 90.0,
    "assessment_score": 85.0,
    "cia_usage_score": 75.0,
    "documentation_score": 85.0,
    "placement_readiness_score": 86.0,
    "viva_score": 80.0,
    "code_review_score": 90.0,
    "demo_clarity_score": 85.0,
    "git_quality_score": 95.0,
    "interview_readiness_score": 85.5,
    "breakdown": {
      "placement": {
        "formula": "(Project Completion * 0.40) + (Assessment * 0.30) + (CIA Usage * 0.10) + (Documentation * 0.20)",
        "final_score": 86.0,
        "status_tier": "Placement Ready (Exceptional)"
      },
      "interview": {
        "formula": "(Viva * 0.40) + (Code Review * 0.30) + (Demo Clarity * 0.20) + (Git Quality * 0.10)",
        "final_score": 85.5,
        "status_tier": "Placement Ready (Exceptional)"
      }
    }
  }
}
```

### 4.2 Retrieve Latest Scorecard: `GET /candidate/cia/career/readiness`
- **Authentication:** `auth`, `verified`
- **Response:** Returns the candidate's latest evaluated scorecard.

---

## 5. Automated Testing & Verification

Run automated feature tests:
```bash
php vendor/phpunit/phpunit/phpunit tests/Feature/CiaReadinessScoreTest.php
```

All 7 test cases execute with 29 assertions covering:
- Auth protection (401 status for unauthenticated requests)
- Placement readiness exact weighted mathematical calculation (84.0%)
- Interview readiness exact weighted mathematical calculation (81.5%)
- Strict numerical clamping to [0, 100]
- Zero-coursework candidate safety and graceful default derivation
- Database persistence in `placement_readiness_scores`
- API retrieval via `GET /candidate/cia/career/readiness`
