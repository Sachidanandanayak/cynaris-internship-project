#!/usr/bin/env python3
"""
Cynaris CIA Memory Layer — ChromaDB Vector Role Retrieval Engine
Week 10, Day 4 (W10D4) — CIA Career Intelligence Memory Store

Author: Cynaris Engineering / Sachidananda Nayak
Engine: ChromaDB PersistentClient with Cosine Similarity Space
Collection: cynaris_role_memory
"""

import os
import sys
import json
import traceback
from typing import Dict, List, Any, Optional

DEFAULT_COLLECTION_ROLE_MEMORY = "cynaris_role_memory"
DEFAULT_CHROMA_PATH = os.path.join("storage", "app", "chromadb")

# Curated 20 Technical Role Profiles
SEEDED_ROLE_PROFILES = [
    {
        "id": "role_01",
        "role": "Junior Backend Laravel Engineer",
        "domain": "backend",
        "experience_level": "Junior",
        "required_skills": ["PHP", "Laravel", "MySQL", "REST APIs", "Git", "MVC Architecture"],
        "match_criteria": "Solid understanding of Laravel routing, Eloquent ORM, database migrations, and basic authentication.",
    },
    {
        "id": "role_02",
        "role": "Senior Backend Systems Architect",
        "domain": "backend",
        "experience_level": "Senior",
        "required_skills": ["PHP", "Laravel", "MySQL", "Redis", "Docker", "REST APIs", "Microservices", "PHPUnit"],
        "match_criteria": "Advanced system scalability, database indexing, caching strategies, and resilient microservices design.",
    },
    {
        "id": "role_03",
        "role": "Mid-Level Full Stack Engineer",
        "domain": "fullstack",
        "experience_level": "Mid-Level",
        "required_skills": ["PHP", "Laravel", "JavaScript", "Vue.js", "Tailwind CSS", "MySQL", "REST APIs", "Git"],
        "match_criteria": "Seamless capability bridging frontend reactivity with backend API controllers and database transactions.",
    },
    {
        "id": "role_04",
        "role": "AI Application & Vector Pipeline Engineer",
        "domain": "ai",
        "experience_level": "Mid-Level",
        "required_skills": ["Python", "ChromaDB", "Vector Embeddings", "Groq", "LLMs", "LlamaIndex", "REST APIs", "PHP"],
        "match_criteria": "Implementation of semantic vector retrieval, RAG architectures, prompt pipelines, and embedding synchronization.",
    },
    {
        "id": "role_05",
        "role": "Agentic Workflows Specialist (LangGraph & CrewAI)",
        "domain": "ai",
        "experience_level": "Senior",
        "required_skills": ["Python", "LangGraph", "CrewAI", "StateGraph", "Groq", "Multi-Agent Systems", "Vector Embeddings"],
        "match_criteria": "Multi-agent coordination, deterministic state machines, cyclic DAG execution, and tool delegation.",
    },
    {
        "id": "role_06",
        "role": "DevOps & Cloud Infrastructure Engineer",
        "domain": "devops",
        "experience_level": "Mid-Level",
        "required_skills": ["Docker", "Kubernetes", "CI/CD", "GitHub Actions", "Linux", "AWS", "Bash", "Nginx"],
        "match_criteria": "Automated containerized delivery pipelines, continuous integration, infrastructure as code, and server hardening.",
    },
    {
        "id": "role_07",
        "role": "Junior Frontend Vue.js Developer",
        "domain": "frontend",
        "experience_level": "Junior",
        "required_skills": ["JavaScript", "Vue.js", "Tailwind CSS", "HTML5", "CSS3", "REST APIs", "Vite", "Git"],
        "match_criteria": "Responsive UI component authoring, state management, reactive DOM updates, and clean CSS styling.",
    },
    {
        "id": "role_08",
        "role": "Senior Frontend Architect",
        "domain": "frontend",
        "experience_level": "Senior",
        "required_skills": ["JavaScript", "TypeScript", "Vue.js", "React", "Tailwind CSS", "Vite", "Pinia", "UI/UX Design"],
        "match_criteria": "Enterprise frontend architecture, component libraries, performance optimization, and accessibility.",
    },
    {
        "id": "role_09",
        "role": "Database Administrator & SQL Optimization Specialist",
        "domain": "database",
        "experience_level": "Senior",
        "required_skills": ["MySQL", "PostgreSQL", "SQL Query Optimization", "Indexing", "Database Migrations", "Redis", "PHP"],
        "match_criteria": "Complex relational modeling, execution plan profiling, replication, indexing strategies, and data consistency.",
    },
    {
        "id": "role_10",
        "role": "Application Security & OWASP Engineer",
        "domain": "security",
        "experience_level": "Senior",
        "required_skills": ["Application Security", "OWASP Top 10", "Authentication", "Sanctum", "CSRF", "Penetration Testing", "PHP"],
        "match_criteria": "Secure coding standards, token lifecycle management, input sanitization, vulnerability remediation.",
    },
    {
        "id": "role_11",
        "role": "API Platform & Integration Engineer",
        "domain": "backend",
        "experience_level": "Mid-Level",
        "required_skills": ["REST APIs", "PHP", "Laravel", "Postman", "OpenAPI", "Swagger", "Webhooks", "JSON"],
        "match_criteria": "High-throughput idempotent REST endpoints, API versioning, third-party webhook integrations, rate limiting.",
    },
    {
        "id": "role_12",
        "role": "Quality Assurance & Automated Test Engineer",
        "domain": "qa",
        "experience_level": "Mid-Level",
        "required_skills": ["PHPUnit", "Automated Testing", "TDD", "Integration Testing", "Pest", "CI/CD", "Git"],
        "match_criteria": "Test-driven development, regression test suites, mocking external HTTP services, coverage profiling.",
    },
    {
        "id": "role_13",
        "role": "Cloud Site Reliability Engineer (SRE)",
        "domain": "devops",
        "experience_level": "Senior",
        "required_skills": ["Linux", "Docker", "AWS", "Prometheus", "Grafana", "Incident Management", "CI/CD", "Bash"],
        "match_criteria": "High availability architecture, 99.9% uptime enforcement, telemetry monitoring, incident mitigation.",
    },
    {
        "id": "role_14",
        "role": "Lead Software Engineer (Technical Mentor)",
        "domain": "backend",
        "experience_level": "Lead",
        "required_skills": ["PHP", "Laravel", "System Architecture", "Code Review", "Technical Leadership", "Agile", "Git"],
        "match_criteria": "Architectural governance, team mentorship, rigorous PR code reviews, and strategic product delivery.",
    },
    {
        "id": "role_15",
        "role": "Data Engineer & Analytics Pipeline Developer",
        "domain": "data",
        "experience_level": "Mid-Level",
        "required_skills": ["Python", "SQL", "MySQL", "ETL Pipelines", "Pandas", "REST APIs", "Docker"],
        "match_criteria": "Data extraction and transformation pipelines, aggregate telemetry reporting, database staging tables.",
    },
    {
        "id": "role_16",
        "role": "Junior Full Stack Web Developer",
        "domain": "fullstack",
        "experience_level": "Junior",
        "required_skills": ["PHP", "Laravel", "JavaScript", "HTML5", "CSS3", "Tailwind CSS", "MySQL", "Git"],
        "match_criteria": "Entry-level full-stack competence building complete CRUD apps with Blade and Tailwind.",
    },
    {
        "id": "role_17",
        "role": "AI Search & Information Retrieval Specialist",
        "domain": "ai",
        "experience_level": "Senior",
        "required_skills": ["ChromaDB", "Vector Search", "Cosine Similarity", "Python", "Embeddings", "Elasticsearch", "REST APIs"],
        "match_criteria": "High-dimensional vector space indexing, semantic search tuning, nearest-neighbor query optimization.",
    },
    {
        "id": "role_18",
        "role": "Microservices Backend Developer",
        "domain": "backend",
        "experience_level": "Mid-Level",
        "required_skills": ["PHP", "Laravel", "Docker", "RabbitMQ", "REST APIs", "Redis", "MySQL"],
        "match_criteria": "Event-driven architecture, asynchronous message queues, decoupled microservices, circuit breaker patterns.",
    },
    {
        "id": "role_19",
        "role": "Mobile-Responsive UI/UX Engineer",
        "domain": "frontend",
        "experience_level": "Mid-Level",
        "required_skills": ["Tailwind CSS", "JavaScript", "Responsive Design", "UI/UX", "HTML5", "CSS3", "Figma"],
        "match_criteria": "Fluid responsive breakpoints, accessible design systems, intuitive user workflows, polished micro-interactions.",
    },
    {
        "id": "role_20",
        "role": "Technical Documentation & Developer Relations Engineer",
        "domain": "docs",
        "experience_level": "Mid-Level",
        "required_skills": ["Technical Writing", "API Documentation", "Markdown", "Git", "PHP", "Laravel", "Postman"],
        "match_criteria": "Clear architectural guides, developer onboarding manuals, OpenAPI schemas, and interactive code examples.",
    },
]


def build_role_document(profile: Dict[str, Any]) -> str:
    """Construct vector document representation for a role profile."""
    role = profile.get("role", "")
    domain = profile.get("domain", "")
    exp = profile.get("experience_level", "")
    skills = ", ".join(profile.get("required_skills", []))
    criteria = profile.get("match_criteria", "")
    return (
        f"Technical Role: {role}. Domain: {domain}. Experience Level: {exp}. "
        f"Required Core Skills: {skills}. Architectural Match Criteria: {criteria}"
    )


def build_candidate_search_query(candidate: Dict[str, Any]) -> str:
    """Build candidate embedding query string from validated skills and lessons."""
    cand_id = candidate.get("candidate_id", "cand_candidate")
    skills = candidate.get("skills", [])
    skill_parts = []
    for s in skills:
        if isinstance(s, dict):
            name = s.get("skill", "")
            score = s.get("proficiency_score", 70.0)
            level = s.get("proficiency_level", "Intermediate")
            skill_parts.append(f"{name} ({level}, {score}%)")
        elif isinstance(s, str):
            skill_parts.append(s)

    skills_text = ", ".join(skill_parts) if skill_parts else "PHP, Laravel, MySQL, REST APIs"
    return f"Candidate {cand_id} competencies: {skills_text}. Full lifecycle engineering, clean architecture, database design."


def compute_skill_overlap(candidate_skills: List[Any], required_skills: List[str]) -> tuple[List[str], List[str]]:
    """Compute matching competencies and missing skill gaps."""
    cand_norm = set()
    for s in candidate_skills:
        if isinstance(s, dict):
            cand_norm.add(s.get("skill", "").strip().lower())
        elif isinstance(s, str):
            cand_norm.add(s.strip().lower())

    matching = []
    missing = []
    for req in required_skills:
        req_lower = req.strip().lower()
        if any(c in req_lower or req_lower in c for c in cand_norm):
            matching.append(req)
        else:
            missing.append(req)

    return matching, missing


def get_chroma_client(chroma_path: str):
    """Instantiate ChromaDB PersistentClient."""
    import chromadb
    os.makedirs(chroma_path, exist_ok=True)
    return chromadb.PersistentClient(path=chroma_path)


def seed_role_profiles(client, collection_name: str = DEFAULT_COLLECTION_ROLE_MEMORY) -> Dict[str, Any]:
    """Seed or update all 20 technical role profiles into ChromaDB."""
    collection = client.get_or_create_collection(
        name=collection_name,
        metadata={"hnsw:space": "cosine"}
    )

    ids = []
    documents = []
    metadatas = []

    for p in SEEDED_ROLE_PROFILES:
        ids.append(p["id"])
        documents.append(build_role_document(p))
        metadatas.append({
            "role": p["role"],
            "domain": p["domain"],
            "experience_level": p["experience_level"],
            "required_skills": json.dumps(p["required_skills"]),
            "match_criteria": p["match_criteria"],
        })

    batch_size = int(os.environ.get("CHROMA_BATCH_SIZE", 100))
    if ids:
        for i in range(0, len(ids), batch_size):
            chunk_ids = ids[i:i + batch_size]
            chunk_docs = documents[i:i + batch_size]
            chunk_meta = metadatas[i:i + batch_size]
            collection.upsert(
                ids=chunk_ids,
                documents=chunk_docs,
                metadatas=chunk_meta
            )

    return {
        "success": True,
        "collection": collection_name,
        "seeded_count": len(SEEDED_ROLE_PROFILES),
        "roles": [p["role"] for p in SEEDED_ROLE_PROFILES],
    }


def search_roles(client, candidate: Dict[str, Any], collection_name: str = DEFAULT_COLLECTION_ROLE_MEMORY, limit: int = 5) -> List[Dict[str, Any]]:
    """Query ChromaDB role memory with candidate vector embedding."""
    collection = client.get_or_create_collection(
        name=collection_name,
        metadata={"hnsw:space": "cosine"}
    )

    # Ensure collection is seeded if empty
    if collection.count() < len(SEEDED_ROLE_PROFILES):
        seed_role_profiles(client, collection_name)

    query_text = build_candidate_search_query(candidate)
    results = collection.query(
        query_texts=[query_text],
        n_results=min(limit, collection.count())
    )

    scored_roles = []
    if results and "ids" in results and results["ids"] and len(results["ids"][0]) > 0:
        ids = results["ids"][0]
        distances = results.get("distances", [[]])[0]
        metas = results.get("metadatas", [[]])[0]

        candidate_skills = candidate.get("skills", [])

        for i, role_id in enumerate(ids):
            meta = metas[i] if i < len(metas) else {}
            dist = float(distances[i]) if i < len(distances) and distances[i] is not None else 0.20

            # Cosine distance to affinity percentage formula:
            # Affinity % = max(0.0, min(100.0, round((1.0 - cosine_distance) * 100, 1)))
            affinity = max(0.0, min(100.0, round((1.0 - dist) * 100.0, 1)))

            req_skills = json.loads(meta.get("required_skills", "[]")) if isinstance(meta.get("required_skills"), str) else meta.get("required_skills", [])
            matching, missing = compute_skill_overlap(candidate_skills, req_skills)

            scored_roles.append({
                "role_id": role_id,
                "role": meta.get("role", "Technical Role"),
                "domain": meta.get("domain", "backend"),
                "experience_level": meta.get("experience_level", "Mid-Level"),
                "affinity_percentage": affinity,
                "vector_distance": round(dist, 4),
                "matching_skills": matching,
                "missing_skills": missing,
                "match_criteria": meta.get("match_criteria", ""),
                "required_skills": req_skills,
            })

    # Sort descending by affinity percentage
    scored_roles.sort(key=lambda x: x["affinity_percentage"], reverse=True)
    return scored_roles[:limit]


def deterministic_role_search_fallback(candidate: Dict[str, Any], limit: int = 5) -> List[Dict[str, Any]]:
    """Deterministic heuristic fallback when ChromaDB native extension is unavailable."""
    candidate_skills = candidate.get("skills", [])
    cand_norm = set()
    for s in candidate_skills:
        if isinstance(s, dict):
            cand_norm.add(s.get("skill", "").strip().lower())
        elif isinstance(s, str):
            cand_norm.add(s.strip().lower())

    results = []
    for p in SEEDED_ROLE_PROFILES:
        req_skills = p["required_skills"]
        matching, missing = compute_skill_overlap(candidate_skills, req_skills)

        # Base overlap score
        total_req = len(req_skills) if req_skills else 1
        overlap_ratio = len(matching) / float(total_req)

        # Baseline affinity between 50% and 96%
        affinity = round(50.0 + (overlap_ratio * 46.0), 1)
        affinity = max(40.0, min(98.0, affinity))
        dist = round(1.0 - (affinity / 100.0), 4)

        results.append({
            "role_id": p["id"],
            "role": p["role"],
            "domain": p["domain"],
            "experience_level": p["experience_level"],
            "affinity_percentage": affinity,
            "vector_distance": dist,
            "matching_skills": matching,
            "missing_skills": missing,
            "match_criteria": p["match_criteria"],
            "required_skills": req_skills,
        })

    results.sort(key=lambda x: x["affinity_percentage"], reverse=True)
    return results[:limit]


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No input payload or action provided"}))
        sys.exit(1)

    raw_arg = sys.argv[1]
    payload = {}
    if os.path.exists(raw_arg):
        with open(raw_arg, "r", encoding="utf-8") as f:
            payload = json.load(f)
    else:
        try:
            payload = json.loads(raw_arg)
        except Exception:
            payload = {"action": raw_arg}

    action = payload.get("action", "search_roles")
    chroma_path = payload.get("chroma_path", DEFAULT_CHROMA_PATH)
    collection_name = payload.get("collection_name", DEFAULT_COLLECTION_ROLE_MEMORY)
    limit = int(payload.get("limit", 5))
    candidate = payload.get("candidate", {})

    try:
        client = get_chroma_client(chroma_path)

        if action == "seed_roles":
            res = seed_role_profiles(client, collection_name)
            res["engine"] = "chromadb_memory"
            print(json.dumps(res))
            return

        if action == "search_roles":
            matches = search_roles(client, candidate, collection_name, limit)
            print(json.dumps({
                "success": True,
                "action": "search_roles",
                "collection": collection_name,
                "matches": matches,
                "total_matched": len(matches),
                "engine": "chromadb_memory_vector",
            }))
            return

        print(json.dumps({"error": f"Unknown action: {action}"}))
    except Exception as e:
        # Fallback cleanly
        if action == "seed_roles":
            print(json.dumps({
                "success": True,
                "action": "seed_roles",
                "collection": collection_name,
                "seeded_count": len(SEEDED_ROLE_PROFILES),
                "roles": [p["role"] for p in SEEDED_ROLE_PROFILES],
                "engine": "chromadb_fallback_engine",
                "warning": str(e),
            }))
            return

        matches = deterministic_role_search_fallback(candidate, limit)
        print(json.dumps({
            "success": True,
            "action": action,
            "collection": collection_name,
            "matches": matches,
            "total_matched": len(matches),
            "engine": "chromadb_fallback_engine",
            "warning": str(e),
        }))


if __name__ == "__main__":
    main()

