# 📄 Week 10 – Day 3: Career Intelligence Report & 6 Engineering Archetypes

## 1. Executive Summary

Week 10 Day 3 implements **Module 7: Career Intelligence Report & 6 Engineering Archetypes** within the Cynaris Intelligence Agent (CIA) Cockpit. This system synthesizes coursework milestones, assessment accuracy, interview readiness, and platform telemetry into a holistic, explainable engineering profile.

The engine introduces a mathematical 6-archetype scoring model, computes an explainable compound Employability Score (0–100), generates executive career narratives using **Groq LLaMA-3.3-70b-versatile** (with deterministic heuristic fallback), and provides professional downloadable PDF reports rendered via **Barryvdh Laravel DomPDF**.

---

## 2. Six Engineering Archetypes Taxonomy

Every candidate is systematically scored from `0.0` to `100.0` across six distinct engineering archetypes based on verified platform signals:

| Archetype | Tagline | Primary Competency Focus | Mathematical Derivation |
| :--- | :--- | :--- | :--- |
| **Builder** | Systems Architect & Craftsperson | Database schema design, REST APIs, production code quality, system construction. | $(Project \times 0.40) + (CodeReview \times 0.30) + (TopSkill \times 0.20) + (Git \times 0.10)$ |
| **Analyst** | Data-Driven & Algorithmic Problem Solver | Systematic decomposition, debugging, automated test rigor, performance profiling. | $(Assessment \times 0.45) + (CodeReview \times 0.25) + (SkillAvg \times 0.20) + (CiaUsage \times 0.10)$ |
| **Leader** | Technical Communicator & Champion | System defense, trade-off articulation, documentation, peer mentorship. | $(Viva \times 0.40) + (DemoClarity \times 0.30) + (Documentation \times 0.20) + (Git \times 0.10)$ |
| **Innovator** | Adaptive Pioneer & AI Integrator | Rapid adoption of modern AI paradigms (ChromaDB, LangGraph, CrewAI, ATS). | $(CiaUsage \times 0.40) + (Project \times 0.25) + (TelemetryDiversity \times 1.5) + (Assessment \times 0.05)$ |
| **Specialist** | Deep Domain Authority | Vertical expertise in core backend, Laravel framework internals, and relational engines. | $(TopSkill \times 0.50) + (Assessment \times 0.25) + (Project \times 0.10) + VerifiedBonus$ |
| **Generalist** | Full-Spectrum Swiss Army Knife | Cross-functional agility bridging backend, frontend, DevOps, docs, and QA. | $(SkillDiversity \times 0.40) + (ReadinessAvg \times 0.40) + (Doc \times 0.10) + (Demo \times 0.10)$ |

- **Primary Archetype:** The archetype achieving the candidate's highest numerical score.
- **Secondary Archetype:** The archetype achieving the candidate's second highest score.

---

## 3. Explainable Compound Employability Score

The **Employability Score** provides hiring squads and candidates with a transparent, explainable metric reflecting workforce readiness.

### Mathematical Formula:
$$\text{Employability Score} = (\text{Primary Archetype} \times 0.25) + (\text{Placement Readiness} \times 0.35) + (\text{Interview Readiness} \times 0.25) + (\text{Skill Average} \times 0.15)$$

All component inputs and the resulting compound score are strictly clamped to $[0.0, 100.0]$:

```php
public function clampScore(float $value): float
{
    return (float) min(100.0, max(0.0, round($value, 1)));
}
```

### Readiness Status Tiers:
- **$\ge 85.0\%$**: Placement Ready (Tier 1 Priority)
- **$70.0\% - 84.9\%$**: Competitive Candidate (Tier 2 Ready)
- **$50.0\% - 69.9\%$**: In Development (Tier 3 Progressing)
- **$< 50.0\%$**: Foundational (Tier 4 Focus Required)

---

## 4. AI Narrative & Report Generation Pipeline

### Groq Pipeline:
- **Model:** `llama-3.3-70b-versatile`
- **Response Format:** `json_object`
- **Output Schema:**
  - `executive_summary`: 2–3 sentences contextualizing score and archetype alignment.
  - `archetype_analysis`: Interplay between dominant and complementary archetypes.
  - `strengths`: 3 concrete technical strengths.
  - `growth_areas`: 3 actionable growth priorities.
  - `roadmap_90_days`: 3 phased milestones (Foundation, System Design, Enterprise Placement).
  - `hiring_pitch`: 2-sentence elevator pitch tailored for recruiters.

### Deterministic Heuristic Fallback:
If Groq is unreachable, times out, or returns invalid JSON, the service seamlessly synthesizes an explainable report from candidate database records without failing the request:
- Engine identifier flagged as `deterministic_heuristic_engine`.
- Zero 500 errors or application disruptions.

---

## 5. DomPDF Official Report Export

Leverages **Barryvdh Laravel DomPDF** (`barryvdh/laravel-dompdf: ^3.1`) to render publication-ready PDF career dossiers:
- Standard `A4` portrait format.
- Confidential CIA branding header with Report ID and generation timestamp.
- Employability Score callout banner with status tier.
- Comparative Archetype score grid with visual meters.
- Executive Narrative & Recruiter Pitch callout.
- Two-column Strengths and Growth Areas breakdown.
- 90-Day Career Acceleration Roadmap table.
- Accessible directly via `GET /candidate/cia/career/report/{report}/download` with strict authorization checks.

---

## 6. Database Schema (`candidate_reports`)

```sql
CREATE TABLE candidate_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    report_type VARCHAR(255) DEFAULT 'career_intelligence',
    archetype_scores TEXT NOT NULL,         -- JSON: {Builder: 85, Analyst: 78, ...}
    primary_archetype VARCHAR(255) NOT NULL,
    secondary_archetype VARCHAR(255) NULL,
    employability_score REAL DEFAULT 0.0,
    narrative TEXT NOT NULL,
    recommendations TEXT NOT NULL,          -- JSON
    breakdown TEXT NULL,                    -- JSON: explainability weights
    pdf_path VARCHAR(255) NULL,
    metadata TEXT NULL,                     -- JSON: engine, roadmap, pitch
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 7. API Endpoints

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `POST` | `/candidate/cia/career/report/generate` | Generates 6 archetypes, employability score, narrative, and PDF. | Yes (`auth`) |
| `GET` | `/candidate/cia/career/report/latest` | Retrieves candidate's latest intelligence report. | Yes (`auth`) |
| `GET` | `/candidate/cia/career/report/{report}/download` | Streams downloadable publication-grade PDF dossier. | Yes (`auth`) |

---

## 8. Test Suite Verification

Comprehensive feature tests in `tests/Feature/CiaCareerIntelligenceReportTest.php` cover:
- Authentication guards (401 on unauthenticated requests).
- Mathematical correctness of Employability Score formula.
- Evaluation of all six engineering archetypes.
- Strict score clamping $[0.0, 100.0]$.
- Groq structured JSON parsing and deterministic fallback resilience.
- PDF generation and authorized binary download.
- 403 Forbidden protection on unauthorized cross-candidate report access.
- Audit persistence in `cia_search_logs` table.

```bash
php vendor/phpunit/phpunit/phpunit tests/Feature/CiaCareerIntelligenceReportTest.php --testdox
OK (10 tests, 56 assertions)
```

