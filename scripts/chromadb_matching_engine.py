#!/usr/bin/env python3
"""
Cynaris Internship Matching Engine — ChromaDB Vector Retrieval
Week 9, Day 4 (W9D4) — CIA Career Intelligence

Author: Cynaris Engineering / Sachidananda Nayak
Engine: ChromaDB PersistentClient with Cosine Similarity Space
Collections:
  - cynaris_intern_profiles (pseudonymous candidate profiles, zero PII)
  - cynaris_internship_jobs (curated 20 internship job profiles)
"""

import os
import sys
import json
import re
import traceback
from datetime import datetime, timezone
from typing import Dict, List, Any, Optional

# Constants
DEFAULT_COLLECTION_PROFILES = "cynaris_intern_profiles"
DEFAULT_COLLECTION_JOBS = "cynaris_internship_jobs"
DEFAULT_CHROMA_PATH = os.path.join("storage", "app", "chromadb")


def infer_domain(title: str, text: str) -> str:
    """Infer technical domain category from job title and text."""
    combined = f"{title} {text}".lower()
    if any(k in combined for k in ["ai", "llm", "chromadb", "machine learning", "nlp", "prompt", "research"]):
        return "ai"
    if any(k in combined for k in ["devops", "cloud", "docker", "kubernetes", "ci/cd", "aws", "infrastructure", "linux"]):
        return "devops"
    if any(k in combined for k in ["frontend", "vue", "react", "ui/ux", "tailwind", "css", "html", "javascript"]):
        return "frontend"
    if any(k in combined for k in ["database", "sql", "mysql", "postgresql", "query optimization", "indexing"]):
        return "database"
    if any(k in combined for k in ["security", "appsec", "pentest", "owasp", "infosec"]):
        return "security"
    if any(k in combined for k in ["full-stack", "full stack", "fullstack"]):
        return "fullstack"
    return "backend"


def infer_location_type(location_str: str) -> str:
    """Normalize location string into 'remote', 'hybrid', or 'onsite'."""
    loc = location_str.lower()
    if "remote" in loc:
        return "remote"
    if "hybrid" in loc:
        return "hybrid"
    return "onsite"


def build_candidate_narrative(candidate_data: Dict[str, Any]) -> str:
    """
    Construct a rich, anonymized candidate narrative text.
    STRICT PRIVACY: Enforces zero PII (no names, emails, phones, or addresses).
    """
    cand_id = candidate_data.get("candidate_id", "cand_anonymous")
    primary_domain = candidate_data.get("primary_domain", "backend")
    composite_score = candidate_data.get("composite_score", 85.0)
    skills = candidate_data.get("skills", [])
    modules_count = candidate_data.get("modules_completed_count", 0)

    skill_phrases = []
    for s in skills:
        if isinstance(s, dict):
            name = s.get("skill", "")
            level = s.get("proficiency_level", "Intermediate")
            score = s.get("proficiency_score", 75.0)
            verified = "verified" if s.get("verified") else "assessed"
            skill_phrases.append(f"{name} ({level}, {verified} at {score}%)")
        elif isinstance(s, str):
            skill_phrases.append(s)

    skills_text = ", ".join(skill_phrases) if skill_phrases else "PHP, Laravel, MySQL, REST APIs, Git"

    narrative = (
        f"Candidate {cand_id} technical profile in {primary_domain} engineering. "
        f"Demonstrated competencies: {skills_text}. "
        f"Completed {modules_count} structured learning modules with assessment composite score of {composite_score} out of 100. "
        f"Proficient in clean architecture, automated testing, and agile team workflows."
    )
    return narrative


def build_job_document(job: Dict[str, Any]) -> str:
    """Construct document representation for a job posting."""
    title = job.get("title", "Internship Role")
    company = job.get("company", "Cynaris Partner")
    domain = job.get("domain", infer_domain(title, job.get("requirements", "")))
    location_type = job.get("location_type", infer_location_type(job.get("location", "Remote")))
    location = job.get("location", "")
    requirements = job.get("requirements", "")
    description = job.get("description", "")

    return (
        f"Job Title: {title}. Company: {company}. Domain: {domain}. "
        f"Workplace: {location_type}. Location: {location}. "
        f"Requirements: {requirements}. Description: {description}"
    )


def extract_skills_delta(candidate_skills: List[str], job_requirements: str, job_text: str) -> tuple[List[str], List[str]]:
    """
    Extract matching skills (candidate has them and job asks for them)
    and missing skills (job mentions them but candidate doesn't have them).
    """
    combined_job = f"{job_requirements} {job_text}".lower()

    # Normalize candidate skills
    norm_candidate = set()
    for s in candidate_skills:
        if isinstance(s, dict):
            norm_candidate.add(s.get("skill", "").strip().lower())
        elif isinstance(s, str):
            norm_candidate.add(s.strip().lower())

    # Well-known technology keywords to watch for
    known_tech = [
        "php", "laravel", "mysql", "postgresql", "rest apis", "restful api", "git", "github",
        "docker", "kubernetes", "ci/cd", "github actions", "vue.js", "vue", "react", "javascript",
        "tailwind css", "tailwind", "html5", "css3", "python", "chromadb", "vector embeddings",
        "groq ai", "llamaindex", "prompt engineering", "linux", "redis", "postman", "phpunit",
        "cloud deployment", "aws", "relational database design", "sql query optimization", "indexing"
    ]

    matching = []
    missing = []

    for tech in known_tech:
        # Check if job mentions this tech
        if tech in combined_job:
            # Format nicely
            display_tech = tech.title()
            if tech in ["php", "mysql", "sql", "aws", "html5", "css3", "ui/ux", "ci/cd", "rest apis"]:
                display_tech = tech.upper()
            elif tech == "vue.js" or tech == "vue":
                display_tech = "Vue.js"
            elif tech == "tailwind css" or tech == "tailwind":
                display_tech = "Tailwind CSS"
            elif tech == "chromadb":
                display_tech = "ChromaDB"
            elif tech == "llamaindex":
                display_tech = "LlamaIndex"

            # Check if candidate has it
            has_skill = any(c in tech or tech in c for c in norm_candidate)
            if has_skill:
                if display_tech not in matching:
                    matching.append(display_tech)
            else:
                if display_tech not in missing:
                    missing.append(display_tech)

    # If matching is empty, fallback to candidate skills present
    if not matching:
        for s in norm_candidate:
            if s in combined_job:
                matching.append(s.title())

    return matching[:6], missing[:5]


class ChromaDbMatchingEngine:
    """Manages ChromaDB persistent collections, profile synchronization, and semantic matching."""

    def __init__(self, chroma_path: Optional[str] = None):
        self.chroma_path = chroma_path or os.environ.get("CHROMA_PATH", DEFAULT_CHROMA_PATH)
        self.client = None
        self.profiles_collection = None
        self.jobs_collection = None
        self._init_chroma()

    def _init_chroma(self):
        """Initialize ChromaDB PersistentClient and get/create collections."""
        try:
            import chromadb
            os.makedirs(self.chroma_path, exist_ok=True)
            self.client = chromadb.PersistentClient(path=self.chroma_path)
            self.profiles_collection = self.client.get_or_create_collection(
                name=DEFAULT_COLLECTION_PROFILES,
                metadata={"hnsw:space": "cosine"}
            )
            self.jobs_collection = self.client.get_or_create_collection(
                name=DEFAULT_COLLECTION_JOBS,
                metadata={"hnsw:space": "cosine"}
            )
        except Exception as e:
            # Keep client as None for fallback mode
            sys.stderr.write(f"Warning: Failed to initialize ChromaDB PersistentClient: {e}\n")
            self.client = None

    def sync_jobs(self, jobs: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Index or update jobs in the cynaris_internship_jobs collection."""
        if not jobs:
            return {"success": False, "error": "No jobs provided for indexing.", "indexed_count": 0}

        if self.jobs_collection is None:
            return {"success": True, "indexed_count": len(jobs), "engine": "in_memory_fallback"}

        ids = []
        documents = []
        metadatas = []

        for job in jobs:
            job_id = str(job.get("id") or job.get("job_id"))
            if not job_id:
                continue

            title = str(job.get("title", ""))
            requirements = str(job.get("requirements", ""))
            domain = str(job.get("domain") or infer_domain(title, requirements))
            location_type = str(job.get("location_type") or infer_location_type(job.get("location", "")))
            company = str(job.get("company", "Cynaris Partner"))
            posted_at = str(job.get("posted_at") or job.get("created_at") or datetime.now(timezone.utc).isoformat())

            doc = build_job_document(job)

            ids.append(f"job_{job_id}")
            documents.append(doc)
            metadatas.append({
                "job_id": job_id,
                "title": title,
                "company": company,
                "domain": domain.lower(),
                "location_type": location_type.lower(),
                "salary_range": str(job.get("salary_range", "")),
                "posted_at": posted_at,
            })

        if ids:
            batch_size = int(os.environ.get("CHROMA_BATCH_SIZE", 100))
            for i in range(0, len(ids), batch_size):
                chunk_ids = ids[i:i + batch_size]
                chunk_docs = documents[i:i + batch_size]
                chunk_meta = metadatas[i:i + batch_size]
                self.jobs_collection.upsert(
                    ids=chunk_ids,
                    documents=chunk_docs,
                    metadatas=chunk_meta
                )

        return {
            "success": True,
            "indexed_count": len(ids),
            "collection": DEFAULT_COLLECTION_JOBS,
            "engine": "chromadb"
        }

    def sync_profile(self, candidate_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Synchronize candidate profile to cynaris_intern_profiles.
        Enforces pseudonymous candidate ID and zero PII.
        """
        raw_id = candidate_data.get("candidate_id", "0")
        cand_id = f"cand_{raw_id}" if not str(raw_id).startswith("cand_") else str(raw_id)
        
        primary_domain = str(candidate_data.get("primary_domain", "backend")).lower()
        composite_score = float(candidate_data.get("composite_score", 85.0))
        verified_count = int(candidate_data.get("verified_skills_count", 0))
        modules_count = int(candidate_data.get("modules_completed_count", 0))
        now_iso = datetime.now(timezone.utc).isoformat()

        doc = build_candidate_narrative(candidate_data)

        if self.profiles_collection is not None:
            self.profiles_collection.upsert(
                ids=[cand_id],
                documents=[doc],
                metadatas=[{
                    "candidate_id": cand_id,
                    "primary_domain": primary_domain,
                    "composite_score": composite_score,
                    "verified_skills_count": verified_count,
                    "modules_completed_count": modules_count,
                    "is_active": True,
                    "last_synced_at": now_iso,
                }]
            )

        return {
            "success": True,
            "candidate_id": cand_id,
            "target_collection": DEFAULT_COLLECTION_PROFILES,
            "last_synced_at": now_iso,
            "document_preview": doc[:120] + "...",
            "engine": "chromadb" if self.profiles_collection is not None else "fallback"
        }

    def match(
        self,
        candidate_data: Dict[str, Any],
        jobs: Optional[List[Dict[str, Any]]] = None,
        filters: Optional[Dict[str, Any]] = None
    ) -> Dict[str, Any]:
        """
        Query top-5 semantic internship matches with filters.
        """
        filters = filters or {}
        limit = int(filters.get("limit", 5))
        domain_filter = filters.get("domain")
        location_type_filter = filters.get("location_type")
        min_affinity = float(filters.get("min_affinity", 0.0))

        raw_id = candidate_data.get("candidate_id", "0")
        cand_id = f"cand_{raw_id}" if not str(raw_id).startswith("cand_") else str(raw_id)
        candidate_narrative = build_candidate_narrative(candidate_data)
        candidate_skills = candidate_data.get("skills", [])

        # Ensure jobs are indexed if passed
        if jobs:
            self.sync_jobs(jobs)

        # Fallback if ChromaDB is unavailable
        if self.jobs_collection is None:
            return self._fallback_match(
                candidate_data, jobs or [], filters, candidate_narrative, candidate_skills
            )

        try:
            total_jobs_in_col = self.jobs_collection.count()
            if total_jobs_in_col == 0 and jobs:
                self.sync_jobs(jobs)
                total_jobs_in_col = self.jobs_collection.count()

            if total_jobs_in_col == 0:
                return {
                    "success": True,
                    "candidate_id": cand_id,
                    "total_matched": 0,
                    "matches": [],
                    "engine": "chromadb",
                    "query_parameters": filters
                }

            # Build where clause
            where_conditions = []
            if domain_filter and domain_filter.lower() != "all":
                where_conditions.append({"domain": {"$eq": domain_filter.lower()}})
            if location_type_filter and location_type_filter.lower() != "all":
                where_conditions.append({"location_type": {"$eq": location_type_filter.lower()}})

            where_clause = None
            if len(where_conditions) == 1:
                where_clause = where_conditions[0]
            elif len(where_conditions) > 1:
                where_clause = {"$and": where_conditions}

            # Query ChromaDB
            n_results = min(max(limit * 3, 10), total_jobs_in_col)
            query_kwargs = {
                "query_texts": [candidate_narrative],
                "n_results": n_results,
                "include": ["metadatas", "distances", "documents"]
            }
            if where_clause:
                query_kwargs["where"] = where_clause

            results = self.jobs_collection.query(**query_kwargs)

            matched_items = []
            if results and results.get("ids") and len(results["ids"]) > 0:
                ids = results["ids"][0]
                distances = results.get("distances", [[]])[0]
                metadatas = results.get("metadatas", [[]])[0]
                documents = results.get("documents", [[]])[0]

                for idx, item_id in enumerate(ids):
                    dist = float(distances[idx]) if idx < len(distances) else 0.5
                    meta = metadatas[idx] if idx < len(metadatas) else {}
                    doc = documents[idx] if idx < len(documents) else ""

                    # Affinity percentage: (1.0 - distance) * 100
                    affinity_pct = round(max(0.0, min(100.0, (1.0 - dist) * 100.0)), 1)

                    if affinity_pct < min_affinity:
                        continue

                    # Extract skill overlap
                    req_text = meta.get("requirements", "") or doc
                    matching_skills, missing_skills = extract_skills_delta(
                        candidate_skills, req_text, doc
                    )

                    job_numeric_id = meta.get("job_id") or item_id.replace("job_", "")
                    try:
                        job_numeric_id = int(job_numeric_id)
                    except (ValueError, TypeError):
                        pass

                    matched_items.append({
                        "job_id": job_numeric_id,
                        "title": meta.get("title", "Internship Opportunity"),
                        "company": meta.get("company", "Cynaris Client"),
                        "domain": meta.get("domain", "backend"),
                        "location_type": meta.get("location_type", "remote"),
                        "affinity_percentage": affinity_pct,
                        "vector_distance": round(dist, 4),
                        "matching_skills": matching_skills,
                        "missing_skills": missing_skills,
                        "salary_range": meta.get("salary_range", ""),
                        "posted_at": meta.get("posted_at", ""),
                    })

            # Sort descending by affinity
            matched_items.sort(key=lambda x: x["affinity_percentage"], reverse=True)
            top_matches = matched_items[:limit]

            return {
                "success": True,
                "candidate_id": cand_id,
                "total_matched": len(top_matches),
                "matches": top_matches,
                "engine": "chromadb",
                "query_parameters": {
                    "domain": domain_filter,
                    "location_type": location_type_filter,
                    "min_affinity": min_affinity,
                    "limit": limit
                }
            }

        except Exception as e:
            sys.stderr.write(f"ChromaDB query exception, falling back: {e}\n{traceback.format_exc()}\n")
            return self._fallback_match(
                candidate_data, jobs or [], filters, candidate_narrative, candidate_skills
            )

    def _fallback_match(
        self,
        candidate_data: Dict[str, Any],
        jobs: List[Dict[str, Any]],
        filters: Dict[str, Any],
        candidate_narrative: str,
        candidate_skills: List[str]
    ) -> Dict[str, Any]:
        """
        Deterministic Jaccard / lexical token similarity fallback
        when ChromaDB service or ONNX model is degraded.
        """
        limit = int(filters.get("limit", 5))
        domain_filter = filters.get("domain")
        location_type_filter = filters.get("location_type")
        min_affinity = float(filters.get("min_affinity", 0.0))

        raw_id = candidate_data.get("candidate_id", "0")
        cand_id = f"cand_{raw_id}" if not str(raw_id).startswith("cand_") else str(raw_id)

        tokens_cand = set(re.findall(r"\w+", candidate_narrative.lower()))

        scored_jobs = []
        for job in jobs:
            title = str(job.get("title", ""))
            requirements = str(job.get("requirements", ""))
            domain = str(job.get("domain") or infer_domain(title, requirements)).lower()
            location_type = str(job.get("location_type") or infer_location_type(job.get("location", ""))).lower()

            # Apply domain filter
            if domain_filter and domain_filter.lower() != "all" and domain != domain_filter.lower():
                continue

            # Apply location_type filter
            if location_type_filter and location_type_filter.lower() != "all" and location_type != location_type_filter.lower():
                continue

            job_doc = build_job_document(job).lower()
            tokens_job = set(re.findall(r"\w+", job_doc))

            # Jaccard index
            intersection = tokens_cand.intersection(tokens_job)
            union = tokens_cand.union(tokens_job)
            jaccard = len(intersection) / len(union) if union else 0.0

            # Scale to realistic affinity percentage: 60% base + 40% * jaccard
            affinity_pct = round(min(98.5, max(45.0, 55.0 + (jaccard * 100.0))), 1)
            dist = round(max(0.01, 1.0 - (affinity_pct / 100.0)), 4)

            if affinity_pct < min_affinity:
                continue

            matching_skills, missing_skills = extract_skills_delta(
                candidate_skills, requirements, job_doc
            )

            job_id = job.get("id") or job.get("job_id")
            try:
                job_id = int(job_id)
            except (ValueError, TypeError):
                pass

            scored_jobs.append({
                "job_id": job_id,
                "title": title,
                "company": job.get("company", "Cynaris Client"),
                "domain": domain,
                "location_type": location_type,
                "affinity_percentage": affinity_pct,
                "vector_distance": dist,
                "matching_skills": matching_skills,
                "missing_skills": missing_skills,
                "salary_range": job.get("salary_range", ""),
                "posted_at": str(job.get("posted_at") or job.get("created_at") or ""),
            })

        scored_jobs.sort(key=lambda x: x["affinity_percentage"], reverse=True)
        top_matches = scored_jobs[:limit]

        return {
            "success": True,
            "candidate_id": cand_id,
            "total_matched": len(top_matches),
            "matches": top_matches,
            "engine": "chromadb_fallback",
            "query_parameters": {
                "domain": domain_filter,
                "location_type": location_type_filter,
                "min_affinity": min_affinity,
                "limit": limit
            }
        }


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No payload file provided."}))
        sys.exit(1)

    payload_file = sys.argv[1]
    if not os.path.isfile(payload_file):
        print(json.dumps({"success": False, "error": f"Payload file {payload_file} not found."}))
        sys.exit(1)

    with open(payload_file, "r", encoding="utf-8") as f:
        payload = json.load(f)

    action = payload.get("action", "match")
    chroma_path = payload.get("chroma_path") or os.environ.get("CHROMA_PATH")

    engine = ChromaDbMatchingEngine(chroma_path=chroma_path)

    if action == "sync_jobs":
        result = engine.sync_jobs(payload.get("jobs", []))
    elif action == "sync_profile":
        result = engine.sync_profile(payload.get("candidate", {}))
    elif action == "match":
        result = engine.match(
            candidate_data=payload.get("candidate", {}),
            jobs=payload.get("jobs"),
            filters=payload.get("filters", {})
        )
    else:
        result = {"success": False, "error": f"Unknown action: {action}"}

    print(json.dumps(result))


if __name__ == "__main__":
    main()

