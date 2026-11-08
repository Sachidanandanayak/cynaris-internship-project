# 🧠 Week 10 – Day 4: CIA Memory Layer (ChromaDB Vector Role Profile Matching)

## 1. Executive Summary

Week 10 Day 4 implements **Module 8: CIA Memory Layer** within the Cynaris Intelligence Agent (CIA) Cockpit. Expanding upon the vector search foundations established in W9D4, the Memory Layer introduces a persistent vector memory store that indexes 20 industry-standard technical role profiles into high-dimensional cosine space using **ChromaDB PersistentClient**.

Candidate skill profiles, coursework completion, and assessment scores are projected into the vector space to perform cosine similarity searches, calculating exact percentage affinities, vector distances, matching competencies, and missing skill gaps.

---

## 2. Architecture & Vector Space Specifications

```text
+-----------------------------------------------------------------------------------+
|                           CIA Memory Layer Architecture                           |
+-----------------------------------------------------------------------------------+
|  [ Candidate Verified Profile ]  -->  [ Python Embedding Engine ]                 |
|  - Skills & Proficiency Scores        - scripts/chromadb_memory_engine.py         |
|  - Coursework Milestones              - Anonymized Query Vector (Zero PII)        |
+---------------------------------------------------+-------------------------------+
                                                    |
                                                    v
+-----------------------------------------------------------------------------------+
|                     ChromaDB Persistent Store (storage/app/chromadb)              |
|                     Collection: "cynaris_role_memory"                             |
|                     Distance Space: HNSW Cosine (hnsw:space = "cosine")           |
+---------------------------------------------------+-------------------------------+
                                                    |
                                                    v
+-----------------------------------------------------------------------------------+
|                     Top-5 Nearest Neighbor Retrieval                              |
|  - Vector Distance: Cosine Distance in [0.0, 2.0]                                 |
|  - Affinity % = max(0.0, min(100.0, round((1.0 - cosine_dist) * 100, 1)))        |
|  - Skill Overlap: Matching Competencies vs. Missing Gap Partitioning              |
+-----------------------------------------------------------------------------------+
```

---

## 3. Curated 20 Technical Role Profiles

The memory layer indexes 20 persistent technical role profiles spanning backend, frontend, full-stack, AI, DevOps, data, security, and quality engineering:

| ID | Role Title | Domain | Level | Required Core Skills |
| :--- | :--- | :--- | :--- | :--- |
| `role_01` | **Junior Backend Laravel Engineer** | Backend | Junior | PHP, Laravel, MySQL, REST APIs, Git, MVC Architecture |
| `role_02` | **Senior Backend Systems Architect** | Backend | Senior | PHP, Laravel, MySQL, Redis, Docker, REST APIs, Microservices, PHPUnit |
| `role_03` | **Mid-Level Full Stack Engineer** | Fullstack | Mid-Level | PHP, Laravel, JavaScript, Vue.js, Tailwind CSS, MySQL, REST APIs, Git |
| `role_04` | **AI Application & Vector Pipeline Engineer** | AI | Mid-Level | Python, ChromaDB, Vector Embeddings, Groq, LLMs, LlamaIndex, REST APIs, PHP |
| `role_05` | **Agentic Workflows Specialist (LangGraph & CrewAI)** | AI | Senior | Python, LangGraph, CrewAI, StateGraph, Groq, Multi-Agent Systems, Vector Embeddings |
| `role_06` | **DevOps & Cloud Infrastructure Engineer** | DevOps | Mid-Level | Docker, Kubernetes, CI/CD, GitHub Actions, Linux, AWS, Bash, Nginx |
| `role_07` | **Junior Frontend Vue.js Developer** | Frontend | Junior | JavaScript, Vue.js, Tailwind CSS, HTML5, CSS3, REST APIs, Vite, Git |
| `role_08` | **Senior Frontend Architect** | Frontend | Senior | JavaScript, TypeScript, Vue.js, React, Tailwind CSS, Vite, Pinia, UI/UX Design |
| `role_09` | **Database Administrator & SQL Optimization Specialist** | Database | Senior | MySQL, PostgreSQL, SQL Query Optimization, Indexing, Database Migrations, Redis, PHP |
| `role_10` | **Application Security & OWASP Engineer** | Security | Senior | Application Security, OWASP Top 10, Authentication, Sanctum, CSRF, Pentesting, PHP |
| `role_11` | **API Platform & Integration Engineer** | Backend | Mid-Level | REST APIs, PHP, Laravel, Postman, OpenAPI, Swagger, Webhooks, JSON |
| `role_12` | **Quality Assurance & Automated Test Engineer** | QA | Mid-Level | PHPUnit, Automated Testing, TDD, Integration Testing, Pest, CI/CD, Git |
| `role_13` | **Cloud Site Reliability Engineer (SRE)** | DevOps | Senior | Linux, Docker, AWS, Prometheus, Grafana, Incident Management, CI/CD, Bash |
| `role_14` | **Lead Software Engineer (Technical Mentor)** | Backend | Lead | PHP, Laravel, System Architecture, Code Review, Technical Leadership, Agile, Git |
| `role_15` | **Data Engineer & Analytics Pipeline Developer** | Data | Mid-Level | Python, SQL, MySQL, ETL Pipelines, Pandas, REST APIs, Docker |
| `role_16` | **Junior Full Stack Web Developer** | Fullstack | Junior | PHP, Laravel, JavaScript, HTML5, CSS3, Tailwind CSS, MySQL, Git |
| `role_17` | **AI Search & Information Retrieval Specialist** | AI | Senior | ChromaDB, Vector Search, Cosine Similarity, Python, Embeddings, Elasticsearch, REST APIs |
| `role_18` | **Microservices Backend Developer** | Backend | Mid-Level | PHP, Laravel, Docker, RabbitMQ, REST APIs, Redis, MySQL |
| `role_19` | **Mobile-Responsive UI/UX Engineer** | Frontend | Mid-Level | Tailwind CSS, JavaScript, Responsive Design, UI/UX, HTML5, CSS3, Figma |
| `role_20` | **Technical Documentation & DevRel Engineer** | Docs | Mid-Level | Technical Writing, API Documentation, Markdown, Git, PHP, Laravel, Postman |

---

## 4. Mathematical Cosine Distance to Affinity Mapping

In ChromaDB cosine distance space, distance $d \in [0.0, 2.0]$. The affinity percentage translates vector distance into an intuitive candidate match percentage:

$$\text{Affinity \%} = \max\left(0.0, \min\left(100.0, \text{round}((1.0 - d) \times 100, 1)\right)\right)$$

### Properties:
- Identical vector ($d = 0.0$) $\implies 100.0\%$ affinity.
- Near-neighbor vector ($d = 0.20$) $\implies 80.0\%$ affinity.
- Orthogonal vector ($d \ge 1.0$) $\implies 0.0\%$ affinity (clamped).

---

## 5. Competency Overlap & Skill Gap Detection

For each retrieved role match:
- **Matching Competencies:** Intersection between candidate verified skills and role requirements.
- **Missing Skill Gaps:** Set difference of required skills not yet present or verified in candidate history.

---

## 6. API Endpoints

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/candidate/cia/career/memory/match` | Returns top-5 vector matched role profiles with affinity and skill gaps. | Yes (`auth`) |
| `POST` | `/candidate/cia/career/memory/seed` | Synchronizes the 20 technical role profiles into the ChromaDB memory store. | Yes (`auth`) |

### Example Response:
```json
{
  "success": true,
  "meta": {
    "candidate_id": "cand_1",
    "total_matched": 5,
    "limit": 5,
    "engine": "chromadb_memory_vector",
    "collection": "cynaris_role_memory"
  },
  "data": [
    {
      "role_id": "role_01",
      "role": "Junior Backend Laravel Engineer",
      "domain": "backend",
      "experience_level": "Junior",
      "affinity_percentage": 70.2,
      "vector_distance": 0.2984,
      "matching_skills": ["PHP", "Laravel", "MySQL"],
      "missing_skills": ["REST APIs", "Git", "MVC Architecture"],
      "match_criteria": "Solid understanding of Laravel routing, Eloquent ORM, database migrations, and basic authentication."
    }
  ]
}
```

---

## 7. Audit Logging (`cia_search_logs`)

Every memory search query logs an audit record to the database:
- `user_id`: Authenticated candidate ID.
- `target_role`: Top matched role name.
- `query_type`: `memory_role_match`.
- `readiness_score`: Top affinity percentage.
- `gaps`: Missing skills from the top matched role.
- `recommendations`: Top 5 matched roles.
- `status`: `completed`.

---

## 8. Test Suite Verification

Comprehensive feature tests in `tests/Feature/CiaMemoryLayerTest.php` cover:
- Authentication enforcement (401 on unauthenticated access).
- Seeding endpoint synchronization of all 20 role profiles.
- Nearest-neighbor retrieval returning top-5 ranked role matches.
- Verification of cosine distance to affinity percentage formula.
- Exact partitioning of matching competencies vs. missing skill gaps.
- Database audit logging in `cia_search_logs`.
- Safe handling of empty candidate coursework.
- Seamless fallback when python subprocess is mocked/offline.

```bash
php vendor/phpunit/phpunit/phpunit tests/Feature/CiaMemoryLayerTest.php --testdox
OK (8 tests, 112 assertions)
```
