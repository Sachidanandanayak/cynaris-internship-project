# Week 9, Day 4 (W9D4) — ChromaDB Internship Matching Engine

**Project:** Cynaris Internship Portal (`cynaris-portal`) / `laravel_basics`  
**Module:** Module 3 — Vector-Based Semantic Internship Matching Engine  
**Author:** Sachidananda Nayak  
**Date:** November 6, 2026  
**Status:** IMPLEMENTED, TESTED & VERIFIED  

---

## 1. Executive Summary

Building upon the foundations established in **W9D1** (Architecture & Collection Schemas), **W9D2** (Skill Gap Analysis with LlamaIndex + Groq), and **W9D3** (CrewAI Career Recommendation Engine), **Week 9 Day 4 (W9D4)** delivers the **ChromaDB Internship Matching Engine**.

This module integrates high-performance dense semantic vector retrieval using **ChromaDB `PersistentClient`**, establishing cosine similarity vector search between candidates and curated internship job profiles. Candidates are semantically matched to opportunities based on verified lesson completions, assessment quiz scores, and competency narratives, with complete Zero-PII privacy compliance and deterministic multi-tier fallbacks.

```
+-------------------------------------------------------------------------------------------------------+
|                                    CIA Career Intelligence Architecture                               |
+------------------------------------+-----------------------------------+------------------------------+
| Module 1: Skill Gap Analysis       | Module 2: Career Recommendation   | Module 3: Vector Matching    |
| • LlamaIndex + Groq Llama-3        | • CrewAI Multi-Agent Advisor      | • ChromaDB PersistentClient  |
| • Real-time Radar Chart (Chart.js) | • 3 Tailored Pathways & Roadmaps  | • Cosine Metric Vector Search|
| • Priority Learning Curriculum     | • Persistent in cia_search_logs   | • Top-5 Affinity Matching    |
+------------------------------------+-----------------------------------+------------------------------+
```

---

## 2. System Architecture & Collection Schemas

The matching engine employs ChromaDB's embedded Python `PersistentClient`, persisting data to `storage/app/chromadb` without requiring an external daemon process.

```mermaid
flowchart TD
    Candidate[Authenticated Candidate] -->|POST /candidate/cia/career/profile/sync| SyncAPI[CiaController@syncProfile]
    Candidate -->|GET /candidate/cia/career/matches| MatchAPI[CiaController@matches]

    SyncAPI --> ChromaService[App\\Services\\Vector\\ChromaDbService]
    MatchAPI --> ChromaService

    subgraph ServiceLayer [Laravel Vector Service Layer]
        ChromaService --> BuildProfile[User::buildSkillProfile]
        ChromaService --> SanitizeZeroPII[Zero-PII Anonymization Filter]
        ChromaService --> SafeTempJson[Safe Temp JSON IPC Mechanism]
    end

    SafeTempJson --> PythonEngine[scripts/chromadb_matching_engine.py]

    subgraph ChromaStore [ChromaDB Embedded Vector Store]
        PythonEngine --> ColProfiles[(cynaris_intern_profiles\nhnsw:space: cosine)]
        PythonEngine --> ColJobs[(cynaris_internship_jobs\nhnsw:space: cosine)]
    end

    ChromaService --> Hydrate[Eloquent Job Hydration]
    ChromaService --> LogAudit[(cia_search_logs\nquery_type: semantic_match)]
```

### Collection 1: `cynaris_intern_profiles`
* **Distance Metric:** Cosine similarity (`"hnsw:space": "cosine"`).
* **Document Text:** Sanitized, anonymized candidate narrative describing technical competencies, proficiency levels, and completed course milestones.
* **Document ID:** Pseudonymous identifier: `cand_{user_id}`.
* **Metadata Schema:**
  - `candidate_id`: String (e.g. `cand_1`)
  - `primary_domain`: String (`backend`, `frontend`, `devops`, `ai`, `database`)
  - `composite_score`: Float (`0.0` - `100.0`)
  - `verified_skills_count`: Integer
  - `modules_completed_count`: Integer
  - `is_active`: Boolean (`true`)
  - `last_synced_at`: ISO 8601 Timestamp

### Collection 2: `cynaris_internship_jobs`
* **Distance Metric:** Cosine similarity (`"hnsw:space": "cosine"`).
* **Indexed Opportunities:** 20 realistic, curated internship opportunity profiles covering full-stack, cloud, AI, DevOps, frontend, backend, and security domains.
* **Document Text:** Combined job title, company, domain, workplace mode, location, requirements, and responsibilities.
* **Metadata Schema:**
  - `job_id`: Numeric/String identifier referencing relational `jobs.id`
  - `title`: Job Title
  - `company`: Enterprise hiring partner
  - `domain`: Normalized domain tag (`backend`, `frontend`, `fullstack`, `devops`, `ai`, `database`, `security`)
  - `location_type`: Workplace mode (`remote`, `hybrid`, `onsite`)
  - `salary_range`: Stipend/compensation string
  - `posted_at`: ISO 8601 Timestamp

---

## 3. Zero-PII Compliance & Privacy Framework

In compliance with GDPR (Article 17 & 25) and DPDP regulatory safeguards:
1. **Pseudonymization:** All candidate documents and vectors in ChromaDB use cryptographically isolated, pseudonymous keys (`cand_{user_id}`).
2. **Zero PII Exposure:**
   - Candidate legal names, personal emails, phone numbers, and physical addresses are strictly excluded from vector document strings and metadata payloads.
   - Vector narratives only capture verified technical competencies, framework proficiencies, and coursework milestones.
3. **Internal Boundary:** Direct browser-to-vector access is prevented; all requests route through Laravel's authenticated controller layer with session validation and CSRF protection.

---

## 4. Curated 20 Internship Profiles Matrix

The `InternshipOpportunitySeeder` seeds 20 distinct, realistic internship opportunities into the `jobs` table, representing key modern software engineering disciplines:

| ID | Title | Company | Domain | Workplace | Stipend |
|---|---|---|---|---|---|
| 1 | Graduate Software Engineer — Laravel Intern | Cynaris Systems | Backend | On-site (Pune) | ₹30,000 - ₹45,000 / mo |
| 2 | Backend Laravel & API Services Intern | Cynaris Cloud Labs | Backend | Hybrid (BLR) | ₹35,000 - ₹50,000 / mo |
| 3 | Full-Stack PHP & Vue.js Developer Intern | NovaCore Digital | Full-Stack | Remote | ₹32,000 - ₹45,000 / mo |
| 4 | Cloud Infrastructure & DevOps Intern | Apex Cloud Solutions | DevOps | Hybrid (HYD) | ₹35,000 - ₹48,000 / mo |
| 5 | AI & LLM Integration Engineering Intern | Cynaris AI Research | AI / LLM | Hybrid (BLR) | ₹40,000 - ₹55,000 / mo |
| 6 | Frontend UI/UX & Tailwind CSS Intern | Vanguard Studios | Frontend | Remote | ₹28,000 - ₹40,000 / mo |
| 7 | Database Optimization & SQL Performance Intern | HyperScale Data | Database | On-site (MUM) | ₹32,000 - ₹44,000 / mo |
| 8 | Cybersecurity & Application Security Intern | ShieldGuard Cyber | Security | Hybrid (PUN) | ₹36,000 - ₹50,000 / mo |
| 9 | Microservices & Distributed Architecture Intern | Nexus Cloud Foundry | Backend | Remote | ₹38,000 - ₹52,000 / mo |
| 10 | React & Modern Frontend Engineering Intern | PixelPulse Media | Frontend | Remote | ₹30,000 - ₹42,000 / mo |
| 11 | Docker & Kubernetes Cloud Platform Intern | ContainerWave Ops | DevOps | Hybrid (BLR) | ₹36,000 - ₹50,000 / mo |
| 12 | Natural Language Processing & Vector Search Intern | SemanticLabs AI | AI / LLM | Remote | ₹42,000 - ₹58,000 / mo |
| 13 | High-Concurrency Redis & Caching Systems Intern | FastPath Networks | Backend | On-site (BLR) | ₹34,000 - ₹48,000 / mo |
| 14 | Automated QA & PHPUnit Testing Intern | TestCraft Automation | Backend | Remote | ₹28,000 - ₹38,000 / mo |
| 15 | RESTful API & GraphQL Services Intern | Synapse Integrations | Backend | Hybrid (HYD) | ₹33,000 - ₹46,000 / mo |
| 16 | Mobile & Responsive Web UI Intern | AppVibe Interactive | Frontend | Remote | ₹29,000 - ₹41,000 / mo |
| 17 | Linux Systems & Site Reliability Intern | InfraGuard Systems | DevOps | On-site (PUN) | ₹35,000 - ₹48,000 / mo |
| 18 | Event-Driven Architecture & Message Queues Intern | StreamSync Digital | Backend | Hybrid (BLR) | ₹37,000 - ₹52,000 / mo |
| 19 | Enterprise CRM & EspoCRM Integration Intern | Cynaris Solutions | Full-Stack | Hybrid (PUN) | ₹32,000 - ₹45,000 / mo |
| 20 | Telemetry, Metrics & Observability Intern | PulseWatch Data | DevOps | Remote | ₹34,000 - ₹47,000 / mo |

---

## 5. Affinity & Semantic Scoring Formula

For each candidate query, ChromaDB returns the raw cosine distance $d \in [0, 2]$:
$$\text{Affinity Percentage} = \text{round}\Big(\max\big(0.0, \min(100.0, (1.0 - d) \times 100.0)\big), 1\Big)$$

- Identical semantic profiles ($d \approx 0.05$): $\approx 95\%$ Affinity
- Strong domain alignment ($d \approx 0.15$): $\approx 85\%$ Affinity
- Moderate alignment ($d \approx 0.35$): $\approx 65\%$ Affinity

---

## 6. API Endpoint Contracts

### `POST /candidate/cia/career/profile/sync`
* **Route Name:** `cia.career.profile.sync`
* **Auth Middleware:** `auth`
* **Response Status:** `202 Accepted`
```json
{
  "success": true,
  "message": "Candidate vector profile synchronized successfully.",
  "data": {
    "sync_job_id": "job_sync_1_20261106232500",
    "candidate_id": "cand_1",
    "status": "SYNCHRONIZED",
    "target_collection": "cynaris_intern_profiles",
    "estimated_completion_seconds": 1,
    "queued_at": "2026-11-06T17:55:00.000000Z",
    "last_synced_at": "2026-11-06T17:55:01.000000Z"
  }
}
```

### `GET /candidate/cia/career/matches`
* **Route Name:** `cia.career.matches`
* **Auth Middleware:** `auth`
* **Query Parameters:**
  - `domain`: `backend`, `frontend`, `fullstack`, `devops`, `ai`, `database`, `security`, or `all`
  - `location_type`: `remote`, `hybrid`, `onsite`, or `all`
  - `min_affinity`: Integer / Float (`0` - `100`)
  - `limit`: Integer (`1` - `20`, default `5`)
* **Response Status:** `200 OK`
```json
{
  "success": true,
  "meta": {
    "candidate_id": "cand_1",
    "total_matched": 3,
    "query_parameters": {
      "domain": "backend",
      "min_affinity": 60,
      "location_type": "all",
      "limit": 5
    },
    "engine": "chromadb"
  },
  "data": [
    {
      "job_id": 1,
      "title": "Graduate Software Engineer — Laravel Intern",
      "company": "Cynaris Systems",
      "company_pseudonym": "Cynaris Systems",
      "location": "Pune, India (On-site)",
      "location_type": "Onsite",
      "domain": "Backend",
      "salary_range": "₹30,000 - ₹45,000 / mo",
      "affinity_percentage": 88.5,
      "vector_distance": 0.115,
      "matching_skills": ["PHP", "Laravel", "MySQL", "Git"],
      "missing_skills": ["Docker", "Redis"],
      "posted_at": "2026-11-06T17:40:00.000000Z",
      "application_deadline": "2026-12-06",
      "application_url": "/jobs/1"
    }
  ]
}
```

---

## 7. Verification & Automated Test Evidence

Automated verification was conducted across all CIA modules using PHPUnit:
* **`CiaChromaDbMatchingTest.php`:** 10 Feature Tests, covering vector sync, 5-match limit, affinity scoring, cosine distance, domain filtering, location filtering, min-affinity threshold, search logs persistence, fallback behavior, and zero-PII privacy.
* **`CiaSkillGapTest.php`:** 9 Feature Tests (Groq + LlamaIndex gap pipeline).
* **`CiaCareerRecommendationTest.php`:** 6 Feature Tests (CrewAI CareerAdvisorAgent).
* **Total CIA Test Suite:** 25 Tests, 100% Passing.

