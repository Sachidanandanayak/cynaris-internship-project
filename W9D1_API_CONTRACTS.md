# W9D1 — API Contracts: CIA Career Mode

**Project:** CIA Career Intelligence (`cynaris-portal`)
**Specification Version:** 1.0.0 (Design & Architecture Phase)
**Status:** PROPOSED API CONTRACTS — NOT YET IMPLEMENTED
**Date:** October 26, 2026

---

## 1. Overview & Contract Standards

This document specifies the authoritative API contracts for the seven proposed CIA Career Mode sub-routes. These contracts define the input/output schemas, validation rules, HTTP status codes, and error structures for subsequent implementation.

### Global Conventions
* **Base URL:** `/career`
* **Transport:** HTTPS with strict TLS 1.3
* **Content Negotiation:** All endpoints accept `Accept: application/json` and state-mutating requests require `Content-Type: application/json`.
* **Authentication Guard:** Session-based Laravel Breeze authentication (`web` guard) with CSRF protection (`X-CSRF-TOKEN`) for web application clients. Token-based Bearer authentication (`auth:sanctum`) is supported for headless clients.
* **Privacy by Design:** Candidate data is referenced strictly via pseudonymous `candidate_id` (e.g. `cand_01h8x9p3q1`). Personal Identifiable Information (PII) such as full legal names, email addresses, phone numbers, and physical addresses is strictly omitted from payloads.
* **Timestamp Standard:** ISO 8601 UTC format (`YYYY-MM-DDTHH:mm:ssZ`).

---

## 2. Standardized Error Response Format

All 4xx and 5xx responses conform to Laravel's standard RFC 7807-compatible JSON error format:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "error_code": "ERR_VALIDATION_FAILED",
  "errors": {
    "target_role_id": [
      "The selected target role id is invalid."
    ]
  },
  "timestamp": "2026-10-26T14:30:00Z"
}
```

### Common HTTP Status Codes Reference
| Status Code | Description | Meaning in CIA Career Mode |
|---|---|---|
| `200 OK` | Standard success | Resource successfully retrieved or synchronously processed. |
| `201 Created` | Creation success | Assessment score successfully ingested and appended to history. |
| `202 Accepted` | Asynchronous job queued | Vector profile sync request accepted and queued for worker execution. |
| `400 Bad Request` | Client request malformed | Missing or invalid query parameters or malformed JSON syntax. |
| `401 Unauthorized` | Unauthenticated | User session expired or missing valid authentication credentials. |
| `403 Forbidden` | Access denied | User is unverified (`verified` middleware) or attempting cross-tenant access. |
| `404 Not Found` | Resource not found | Target role ID, assessment ID, or candidate profile not found. |
| `422 Unprocessable`| Validation error | Request payload failed field-level schema validation constraints. |
| `429 Too Many Req`| Rate limited | Sync requests exceed rate limit (maximum 1 sync per 60 seconds). |
| `500 Server Error` | Internal error | Unhandled application exception in Laravel controller or service. |
| `503 Unavailable` | External service down | ChromaDB vector service unreachable or degraded. |

---

## 3. Detailed Endpoint Contracts

---

### Endpoint 1: Career Mode Dashboard / Cockpit
* **Status:** PROPOSED CONTRACT (Design Stage)
* **Purpose:** Retrieves the authenticated candidate's career intelligence overview, including the composite career readiness index, active track, top role match previews, and vector sync state.
* **HTTP Method:** `GET`
* **Path:** `/career`
* **Route Name:** `career.dashboard`
* **Authentication:** `auth` (session) or `auth:sanctum`
* **Authorization:** Candidate context derived strictly from authenticated user.

#### Request Headers & Parameters
```http
GET /career HTTP/1.1
Host: portal.cynaris.local
Accept: application/json
```
*(No URL query parameters or request body required).*

#### Response Schema (200 OK)
```json
{
  "success": true,
  "data": {
    "candidate_id": "cand_01h8x9p3q1",
    "career_track": "Full-Stack Software Engineering",
    "readiness_index": 87.5,
    "readiness_tier": "Job-Ready",
    "vector_profile_status": {
      "is_synchronized": true,
      "last_synced_at": "2026-10-25T18:45:12Z",
      "vector_dimensions": 384
    },
    "competency_highlights": [
      { "domain": "Backend Systems", "level": "Advanced", "percentile": 92 },
      { "domain": "Relational Databases", "level": "Intermediate", "percentile": 85 },
      { "domain": "API Design & REST", "level": "Advanced", "percentile": 90 }
    ],
    "top_matches_preview": [
      {
        "role_id": "job_01h8x2k9m4",
        "title": "Junior Laravel Engineer",
        "match_percentage": 94.2,
        "location_type": "Hybrid"
      },
      {
        "role_id": "job_01h8x2k9m7",
        "title": "Full-Stack Intern (PHP/Vue)",
        "match_percentage": 88.6,
        "location_type": "Remote"
      }
    ]
  }
}
```

---

### Endpoint 2: Candidate Skill Matrix & Vector State
* **Status:** PROPOSED CONTRACT (Design Stage)
* **Purpose:** Returns the complete, granular skill matrix, categorized technical proficiencies, verified quiz/Moodle scores, and vector document summary for the candidate.
* **HTTP Method:** `GET`
* **Path:** `/career/profile`
* **Route Name:** `career.profile.show`
* **Authentication:** `auth`, `verified`

#### Request Headers & Parameters
```http
GET /career/profile HTTP/1.1
Host: portal.cynaris.local
Accept: application/json
```

#### Response Schema (200 OK)
```json
{
  "success": true,
  "data": {
    "candidate_id": "cand_01h8x9p3q1",
    "updated_at": "2026-10-25T18:45:12Z",
    "skills": [
      {
        "name": "PHP",
        "category": "Programming Languages",
        "proficiency": "Advanced",
        "score": 90,
        "verified": true,
        "verified_via": "Moodle Assessment W3D5"
      },
      {
        "name": "Laravel",
        "category": "Backend Frameworks",
        "proficiency": "Advanced",
        "score": 88,
        "verified": true,
        "verified_via": "Capstone Job Board Project"
      },
      {
        "name": "MySQL",
        "category": "Databases",
        "proficiency": "Intermediate",
        "score": 82,
        "verified": true,
        "verified_via": "W3D4 Database Integration Test"
      },
      {
        "name": "Vector Databases / ChromaDB",
        "category": "AI & Search Architecture",
        "proficiency": "Foundational",
        "score": 75,
        "verified": false,
        "verified_via": "Self-Assessed"
      }
    ],
    "scores": {
      "technical_quiz_average": 88.4,
      "project_execution_score": 92.0,
      "code_quality_rating": 86.5,
      "composite_score": 88.9
    },
    "history_summary": {
      "total_assessments_taken": 8,
      "modules_completed": ["W1_Basics", "W2_AdvancedPHP", "W3_LaravelMVC", "W4_AuthAndJobs"],
      "learning_velocity_hours_per_week": 14.5
    }
  }
}
```

---

### Endpoint 3: Profile Vector Synchronization (ChromaDB)
* **Status:** PROPOSED CONTRACT (Design Stage)
* **Purpose:** Triggers asynchronous synchronization of the intern's current relational skill profile, verified scores, and activity history into ChromaDB collection `cynaris_intern_profiles`.
* **HTTP Method:** `POST`
* **Path:** `/career/profile/sync`
* **Route Name:** `career.profile.sync`
* **Authentication:** `auth`, `verified`
* **Rate Limit:** 1 request per 60 seconds per candidate.

#### Request Headers & Body
```http
POST /career/profile/sync HTTP/1.1
Host: portal.cynaris.local
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: a8f9c1e2d3b4...
```

```json
{
  "force_refresh": false,
  "sync_source": "user_manual_trigger"
}
```

#### Validation Rules
* `force_refresh`: `nullable|boolean` (optional, default `false`).
* `sync_source`: `nullable|string|in:user_manual_trigger,assessment_completion,scheduled_cron`.

#### Response Schema (202 Accepted)
```json
{
  "success": true,
  "message": "Candidate vector profile synchronization queued successfully.",
  "data": {
    "sync_job_id": "job_sync_01h8x9p3q1_202610261435",
    "candidate_id": "cand_01h8x9p3q1",
    "status": "QUEUED",
    "target_collection": "cynaris_intern_profiles",
    "estimated_completion_seconds": 3,
    "queued_at": "2026-10-26T14:35:00Z"
  }
}
```

#### Error Response (429 Too Many Requests)
```json
{
  "success": false,
  "message": "Vector profile synchronization was requested too recently. Please wait 45 seconds.",
  "error_code": "ERR_SYNC_RATE_LIMITED",
  "retry_after_seconds": 45
}
```

---

### Endpoint 4: AI Semantic Role Matching
* **Status:** PROPOSED CONTRACT (Design Stage)
* **Purpose:** Queries ChromaDB for job postings and internship tracks semantically similar to the intern's vector profile, with optional domain and threshold filtering.
* **HTTP Method:** `GET`
* **Path:** `/career/matches`
* **Route Name:** `career.matches.index`
* **Authentication:** `auth`, `verified`

#### Query Parameters
| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
| `domain` | `string` | No | Filter matches by technical domain | `backend` |
| `min_affinity` | `integer` | No | Minimum cosine similarity affinity percentage (0-100, default `60`) | `75` |
| `limit` | `integer` | No | Maximum matched roles to return (1-20, default `10`) | `5` |
| `location_type` | `string` | No | Filter by workplace mode: `remote`, `hybrid`, `onsite` | `hybrid` |

#### Request Example
```http
GET /career/matches?domain=backend&min_affinity=75&limit=5 HTTP/1.1
Host: portal.cynaris.local
Accept: application/json
```

#### Response Schema (200 OK)
```json
{
  "success": true,
  "meta": {
    "candidate_id": "cand_01h8x9p3q1",
    "total_matched": 2,
    "query_parameters": {
      "domain": "backend",
      "min_affinity": 75,
      "limit": 5
    }
  },
  "data": [
    {
      "job_id": "job_01h8x2k9m4",
      "title": "Junior Laravel Engineer",
      "company_pseudonym": "Cynaris Enterprise Client A",
      "affinity_percentage": 94.2,
      "vector_distance": 0.058,
      "matching_skills": ["PHP", "Laravel", "MySQL", "REST APIs"],
      "missing_skills": ["Redis", "Docker CI/CD"],
      "location_type": "Hybrid",
      "posted_at": "2026-10-20T10:00:00Z"
    },
    {
      "job_id": "job_01h8x2k9m7",
      "title": "Full-Stack Intern (PHP/Vue)",
      "company_pseudonym": "Cynaris Labs",
      "affinity_percentage": 88.6,
      "vector_distance": 0.114,
      "matching_skills": ["PHP", "Laravel", "HTML/CSS"],
      "missing_skills": ["Vue.js 3", "Pinia"],
      "location_type": "Remote",
      "posted_at": "2026-10-22T14:30:00Z"
    }
  ]
}
```

---

### Endpoint 5: Career Competency Gap Analysis
* **Status:** PROPOSED CONTRACT (Design Stage)
* **Purpose:** Computes the detailed competency delta between the candidate's vector profile and a specific benchmark job or career role requirements.
* **HTTP Method:** `POST`
* **Path:** `/career/gap-analysis`
* **Route Name:** `career.gap-analysis`
* **Authentication:** `auth`, `verified`

#### Request Headers & Body
```http
POST /career/gap-analysis HTTP/1.1
Host: portal.cynaris.local
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: a8f9c1e2d3b4...
```

```json
{
  "target_role_id": "job_01h8x2k9m4",
  "include_learning_recommendations": true
}
```

#### Validation Rules
* `target_role_id`: `required|string|regex:/^job_[a-z0-9]+$/|exists:jobs,uuid`.
* `include_learning_recommendations`: `nullable|boolean`.

#### Response Schema (200 OK)
```json
{
  "success": true,
  "data": {
    "candidate_id": "cand_01h8x9p3q1",
    "target_role": {
      "role_id": "job_01h8x2k9m4",
      "title": "Junior Laravel Engineer",
      "target_level": "Entry to Mid-Level"
    },
    "overall_readiness_score": 82.0,
    "competency_analysis": {
      "matched_competencies": [
        { "skill": "PHP 8.x", "candidate_proficiency": "Advanced", "required_level": "Intermediate", "status": "EXCEEDS" },
        { "skill": "Laravel 13", "candidate_proficiency": "Advanced", "required_level": "Intermediate", "status": "MEETS" },
        { "skill": "Relational SQL (MySQL)", "candidate_proficiency": "Intermediate", "required_level": "Intermediate", "status": "MEETS" }
      ],
      "gap_competencies": [
        { "skill": "Docker Containerization", "candidate_proficiency": "Novice", "required_level": "Intermediate", "impact": "High" },
        { "skill": "Redis Caching", "candidate_proficiency": "None", "required_level": "Foundational", "impact": "Medium" }
      ]
    },
    "recommended_learning_modules": [
      {
        "module_code": "W4D6_DOCKER_CI",
        "title": "Dockerizing Laravel Applications & CI/CD Pipelines",
        "estimated_hours": 4.5
      },
      {
        "module_code": "ADV_REDIS_01",
        "title": "Laravel Cache & Queue Management with Redis",
        "estimated_hours": 3.0
      }
    ]
  }
}
```

---

### Endpoint 6: Assessment & Challenge Score Ingestion
* **Status:** PROPOSED CONTRACT (Design Stage)
* **Purpose:** Ingests a new assessment, quiz, or challenge result into the candidate's longitudinal progression log, updates aggregate scores, and triggers vector recalculation.
* **HTTP Method:** `POST`
* **Path:** `/career/assessments`
* **Route Name:** `career.assessments.store`
* **Authentication:** `auth`, `verified`

#### Request Headers & Body
```http
POST /career/assessments HTTP/1.1
Host: portal.cynaris.local
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: a8f9c1e2d3b4...
```

```json
{
  "assessment_id": "asm_w8d2_ai_quiz",
  "assessment_type": "technical_quiz",
  "topic": "Laravel AI Tutor & Vector Retrieval",
  "score": 94.5,
  "max_score": 100.0,
  "time_taken_seconds": 780,
  "skill_tags": ["PHP", "ChromaDB", "Vector Embeddings", "AI Integration"],
  "completed_at": "2026-10-26T14:40:00Z"
}
```

#### Validation Rules
* `assessment_id`: `required|string|max:64`.
* `assessment_type`: `required|string|in:technical_quiz,coding_challenge,project_review,capstone_defense`.
* `topic`: `required|string|min:3|max:128`.
* `score`: `required|numeric|min:0|max:100`.
* `max_score`: `required|numeric|gte:score`.
* `time_taken_seconds`: `required|integer|min:30|max:14400`.
* `skill_tags`: `required|array|min:1|max:10`.
* `skill_tags.*`: `string|max:32`.
* `completed_at`: `required|date|before_or_equal:now`.

#### Response Schema (201 Created)
```json
{
  "success": true,
  "message": "Assessment result successfully recorded and profile updated.",
  "data": {
    "record_id": "rec_01h8x9p3q1_8821",
    "candidate_id": "cand_01h8x9p3q1",
    "assessment_id": "asm_w8d2_ai_quiz",
    "score_recorded": 94.5,
    "updated_composite_score": 89.6,
    "vector_refresh_queued": true,
    "recorded_at": "2026-10-26T14:41:00Z"
  }
}
```

---

### Endpoint 7: Career Readiness Passport / Report Export
* **Status:** PROPOSED CONTRACT (Design Stage)
* **Purpose:** Generates a structured, privacy-preserving Career Readiness Passport export containing verified competencies, longitudinal score trends, and gap analysis results for mentors or prospective hiring partners.
* **HTTP Method:** `GET`
* **Path:** `/career/report`
* **Route Name:** `career.report.export`
* **Authentication:** `auth`, `verified`

#### Query Parameters
| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
| `format` | `string` | No | Export format: `json` or `pdf` (default: `json`) | `json` |
| `include_history`| `boolean` | No | Include granular module timeline (default: `true`) | `true` |
| `anonymized` | `boolean` | No | Must strictly be `true` for third-party export (default: `true`)| `true` |

#### Request Example
```http
GET /career/report?format=json&anonymized=true HTTP/1.1
Host: portal.cynaris.local
Accept: application/json
```

#### Response Schema (200 OK)
```json
{
  "success": true,
  "document_type": "CIA_CAREER_READINESS_PASSPORT",
  "document_id": "PASSPORT-CYN-2026-01H8X9",
  "issued_at": "2026-10-26T14:45:00Z",
  "anonymization_verified": true,
  "candidate": {
    "identifier": "cand_01h8x9p3q1",
    "track": "Full-Stack Software Engineering",
    "internship_cohort": "Cynaris 2026 Cohort 2",
    "overall_readiness_tier": "Job-Ready (Top 10%)"
  },
  "verified_competencies": [
    { "competency": "PHP 8.x Architecture", "level": "Advanced", "assessment_count": 4, "mean_score": 91.2 },
    { "competency": "Laravel Framework & MVC", "level": "Advanced", "assessment_count": 5, "mean_score": 90.0 },
    { "competency": "Database Schema & Query Optimization", "level": "Intermediate", "assessment_count": 3, "mean_score": 84.5 },
    { "competency": "RESTful API Design & Security", "level": "Advanced", "assessment_count": 3, "mean_score": 89.0 }
  ],
  "performance_metrics": {
    "quiz_composite_percentile": 92.4,
    "capstone_delivery_rating": "Distinction",
    "code_review_acceptance_rate": 96.0
  },
  "verification_seal": {
    "issuer": "Cynaris Career Intelligence (CIA)",
    "digital_signature_hash": "sha256_e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
  }
}
```
