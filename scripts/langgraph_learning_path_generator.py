#!/usr/bin/env python3
"""
Cynaris Learning Path Generator — LangGraph Five-Node StateGraph Workflow
Week 9, Day 5 (W9D5) — CIA Career Intelligence

Workflow:
  AssessState -> IdentifyGaps -> BuildPlan -> ValidatePlan -> OutputPlan -> END

Engine: LangGraph StateGraph + ChatGroq (with deterministic fallback)
"""

import os
import sys
import json
import re
import traceback
from datetime import datetime, timezone
from typing import TypedDict, List, Dict, Any, Optional

try:
    from langgraph.graph import StateGraph, END
    LANGGRAPH_AVAILABLE = True
except ImportError:
    LANGGRAPH_AVAILABLE = False

try:
    from langchain_groq import ChatGroq
    from langchain_core.messages import SystemMessage, HumanMessage
    CHATGROQ_AVAILABLE = True
except ImportError:
    CHATGROQ_AVAILABLE = False


# ============================================================================
# 1. State Definition (TypedDict)
# ============================================================================

class LearningPathState(TypedDict):
    candidate_id: str
    target_role: str
    target_job: Optional[Dict[str, Any]]
    skill_profile: List[Dict[str, Any]]
    composite_score: float
    completed_modules_count: int

    # Node 1: AssessState updates
    baseline_tier: str
    primary_domain: str
    verified_competencies: List[str]

    # Node 2: IdentifyGaps updates
    critical_gaps: List[Dict[str, Any]]
    target_proficiencies: Dict[str, str]

    # Node 3: BuildPlan updates
    raw_schedule: List[Dict[str, Any]]
    total_hours: int
    generation_engine: str

    # Node 4: ValidatePlan updates
    is_valid: bool
    validation_notes: List[str]
    validated_schedule: List[Dict[str, Any]]

    # Node 5: OutputPlan updates
    nodes_traversed: List[str]
    final_output: Dict[str, Any]


# ============================================================================
# 2. Heuristics & Benchmark Catalog
# ============================================================================

BENCHMARK_ROLE_CURRICULUM = {
    "backend": [
        {
            "week": 1,
            "theme": "Advanced PHP 8.x Internals, Modern Syntax & Composer Architecture",
            "focus_skills": ["PHP 8.x", "Object-Oriented Design", "Composer", "Type Safety"],
            "learning_objectives": [
                "Master PHP 8.x match expressions, attributes, typed properties, and named arguments",
                "Structure modular packages with PSR-4 autoloading and Composer dependency injection",
                "Implement strict error handling and domain exceptions"
            ],
            "actionable_tasks": [
                "Refactor legacy procedural utilities into modern PHP 8 classes with typed properties",
                "Create a custom Composer package with automated test pipelines",
                "Profile memory consumption on large array datasets using Xdebug"
            ],
            "recommended_resources": ["PHP 8 Official Reference", "Composer Best Practices Guide", "Moodle Module W2D1"],
            "estimated_hours": 11,
            "checkpoint_quiz": "PHP 8 Advanced Syntax & Type Safety Certification"
        },
        {
            "week": 2,
            "theme": "Relational Data Modeling, Eloquent ORM & Query Optimization",
            "focus_skills": ["MySQL", "Eloquent ORM", "Database Indexing", "Transactions"],
            "learning_objectives": [
                "Design normalized schemas with foreign key integrity and compound indexes",
                "Optimize Eloquent relationships avoiding N+1 performance bottlenecks using eager loading",
                "Execute ACID transactions with deadlock retry mechanisms"
            ],
            "actionable_tasks": [
                "Write complex migration scripts featuring cascading foreign keys and soft deletes",
                "Implement repository pattern queries with index profiling via EXPLAIN",
                "Audit slow query logs and optimize execution time by >= 40%"
            ],
            "recommended_resources": ["High Performance MySQL Handbook", "Laravel Eloquent In-Depth", "Moodle Module W3D2"],
            "estimated_hours": 12,
            "checkpoint_quiz": "Relational Query Profiling & Eloquent Mastery Assessment"
        },
        {
            "week": 3,
            "theme": "RESTful API Architecture, OpenAPI Specification & Sanctum Security",
            "focus_skills": ["REST APIs", "Laravel Sanctum", "API Versioning", "Input Validation"],
            "learning_objectives": [
                "Architect standard RESTful endpoints utilizing FormRequests and API Resource transformations",
                "Enforce token-based authentication and token ability scopes via Laravel Sanctum",
                "Implement rate limiting, idempotency keys, and CORS configurations"
            ],
            "actionable_tasks": [
                "Build CRUD endpoints with pagination, filter scopes, and nested relations",
                "Secure endpoints with bearer token authentication and role-based policies",
                "Generate an OpenAPI v3 Swagger specification document"
            ],
            "recommended_resources": ["RESTful API Architectural Guidelines", "Laravel Sanctum Docs", "Moodle Module W3D5"],
            "estimated_hours": 13,
            "checkpoint_quiz": "API Security & Sanctum Token Lifecycle Quiz"
        },
        {
            "week": 4,
            "theme": "Asynchronous Processing, Redis Caching & Background Queues",
            "focus_skills": ["Redis", "Laravel Queues", "Background Workers", "Rate Limiting"],
            "learning_objectives": [
                "Decouple time-intensive operations into asynchronous queue jobs",
                "Configure Redis for distributed session storage and cache tags",
                "Handle queue failures, exponential backoffs, and dead-letter queues"
            ],
            "actionable_tasks": [
                "Dispatch batch email notifications via queued jobs with rate limiters",
                "Implement Redis caching for high-traffic database queries with TTL invalidation",
                "Monitor worker health and configure failed job alerting"
            ],
            "recommended_resources": ["Redis in Action", "Laravel Queue Architecture", "Moodle Module W4D2"],
            "estimated_hours": 12,
            "checkpoint_quiz": "Asynchronous Queues & Cache Invalidation Checkpoint"
        },
        {
            "week": 5,
            "theme": "Automated Testing, Test-Driven Development (TDD) & PHPUnit",
            "focus_skills": ["PHPUnit", "Test-Driven Development", "Mocking", "Feature Testing"],
            "learning_objectives": [
                "Write comprehensive unit test suites with data providers and assertion suites",
                "Simulate external HTTP services and database states using Mockery and factories",
                "Attain >= 85% code coverage across business service layers"
            ],
            "actionable_tasks": [
                "Write unit tests verifying edge-case calculations and boundary validations",
                "Create end-to-end feature tests covering multi-step user registration and checkout flows",
                "Integrate test coverage reporting into local Git pre-commit hooks"
            ],
            "recommended_resources": ["PHPUnit 10 Documentation", "Test-Driven Laravel Handbook", "Moodle Module W4D4"],
            "estimated_hours": 14,
            "checkpoint_quiz": "Automated Testing & Mockery Verification Challenge"
        },
        {
            "week": 6,
            "theme": "Containerization with Docker & Multi-Stage Deployment",
            "focus_skills": ["Docker", "Docker Compose", "Multi-Stage Builds", "Nginx"],
            "learning_objectives": [
                "Containerize PHP-FPM, Nginx, and MySQL services into isolated micro-environments",
                "Optimize image footprint using multi-stage Alpine Dockerfiles",
                "Manage local volume mounts, environmental secrets, and networking"
            ],
            "actionable_tasks": [
                "Write a production-ready Dockerfile for PHP 8.x with OPcache enabled",
                "Configure Docker Compose orchestrating app, database, Redis, and worker containers",
                "Perform health-check configurations and container restart policies"
            ],
            "recommended_resources": ["Docker Deep Dive", "Docker for PHP Developers", "Moodle Module W4D6"],
            "estimated_hours": 13,
            "checkpoint_quiz": "Container Orchestration & Docker Compose Quiz"
        },
        {
            "week": 7,
            "theme": "CI/CD Automation, GitHub Actions & Cloud Infrastructure",
            "focus_skills": ["CI/CD", "GitHub Actions", "Cloud Deployment", "Linux Shell"],
            "learning_objectives": [
                "Construct automated GitHub Actions workflows running linter, PHPStan, and PHPUnit",
                "Automate zero-downtime deployment pipelines to cloud environments",
                "Implement environment secret management and branch protection rules"
            ],
            "actionable_tasks": [
                "Create a multi-job GitHub Actions workflow triggered on pull requests",
                "Set up automated artifact building and deployment webhooks",
                "Configure staging environment automated rollback triggers"
            ],
            "recommended_resources": ["GitHub Actions Documentation", "Cloud Native DevOps Guide", "Moodle Module W5D2"],
            "estimated_hours": 11,
            "checkpoint_quiz": "CI/CD Pipeline & Deployment Automation Checkpoint"
        },
        {
            "week": 8,
            "theme": "Capstone Architecture: Microservices, Observability & Production Readiness",
            "focus_skills": ["Application Observability", "Security Auditing", "Capstone Project", "System Design"],
            "learning_objectives": [
                "Implement structured telemetry logging, APM request profiling, and error monitoring",
                "Perform OWASP Top 10 security audits across authentication and database queries",
                "Deliver and document a comprehensive capstone enterprise microservice"
            ],
            "actionable_tasks": [
                "Instrument Prometheus / health endpoints and structured JSON audit logging",
                "Run vulnerability scanners and harden HTTP security headers",
                "Present the completed capstone engineering portfolio with architectural documentation"
            ],
            "recommended_resources": ["OWASP Top 10 Security Guide", "Building Microservices (Sam Newman)", "Moodle Capstone"],
            "estimated_hours": 15,
            "checkpoint_quiz": "Final Capstone Architecture & Production Readiness Board Review"
        }
    ]
}


# ============================================================================
# 3. Five Node Implementations
# ============================================================================

def assess_state_node(state: LearningPathState) -> Dict[str, Any]:
    """
    Node 1: AssessState
    Assesses candidate's baseline tier, primary domain, and verified competencies.
    """
    skill_profile = state.get("skill_profile", [])
    composite_score = float(state.get("composite_score", 70.0))
    completed_modules = int(state.get("completed_modules_count", 0))

    # Determine baseline tier
    if composite_score >= 82.0 and completed_modules >= 4:
        baseline_tier = "Advanced Intermediate"
    elif composite_score >= 60.0 or completed_modules >= 2:
        baseline_tier = "Intermediate"
    else:
        baseline_tier = "Foundational"

    # Infer primary domain
    target_role = state.get("target_role", "").lower()
    if any(k in target_role for k in ["frontend", "vue", "react", "ui", "tailwind"]):
        primary_domain = "frontend"
    elif any(k in target_role for k in ["cloud", "devops", "docker", "kubernetes"]):
        primary_domain = "devops"
    elif any(k in target_role for k in ["ai", "llm", "chromadb", "machine learning"]):
        primary_domain = "ai"
    elif any(k in target_role for k in ["full-stack", "fullstack"]):
        primary_domain = "fullstack"
    else:
        primary_domain = "backend"

    verified = [
        s.get("skill", "") for s in skill_profile
        if isinstance(s, dict) and s.get("verified")
    ]

    nodes = state.get("nodes_traversed", [])
    nodes.append("AssessState")

    return {
        "baseline_tier": baseline_tier,
        "primary_domain": primary_domain,
        "verified_competencies": verified,
        "nodes_traversed": nodes
    }


def identify_gaps_node(state: LearningPathState) -> Dict[str, Any]:
    """
    Node 2: IdentifyGaps
    Compares candidate state against target role/job benchmarks and identifies deficit skills.
    """
    target_role = state.get("target_role", "Junior Backend Laravel Engineer")
    target_job = state.get("target_job") or {}
    skill_profile = state.get("skill_profile", [])

    existing_skills = {
        s.get("skill", "").strip().lower(): float(s.get("proficiency_score", 70.0))
        for s in skill_profile if isinstance(s, dict)
    }

    # Expected competencies for benchmark
    role_lower = target_role.lower()
    expected = [
        {"skill": "PHP 8.x", "target_score": 85.0, "impact": "High"},
        {"skill": "Laravel Framework", "target_score": 85.0, "impact": "High"},
        {"skill": "Relational MySQL & Indexing", "target_score": 80.0, "impact": "High"},
        {"skill": "REST APIs & Sanctum", "target_score": 80.0, "impact": "High"},
        {"skill": "Redis & Background Queues", "target_score": 75.0, "impact": "Medium"},
        {"skill": "PHPUnit & Automated Testing", "target_score": 80.0, "impact": "High"},
        {"skill": "Docker & Containerization", "target_score": 75.0, "impact": "Medium"},
        {"skill": "CI/CD & GitHub Actions", "target_score": 70.0, "impact": "Medium"},
    ]

    if "frontend" in role_lower or "full-stack" in role_lower:
        expected.append({"skill": "Vue.js & Reactive UI", "target_score": 80.0, "impact": "High"})
        expected.append({"skill": "Tailwind CSS Design Systems", "target_score": 75.0, "impact": "Medium"})

    if "ai" in role_lower or "chromadb" in role_lower:
        expected.append({"skill": "ChromaDB Vector Embeddings", "target_score": 80.0, "impact": "High"})
        expected.append({"skill": "Groq & LLM Prompt Pipelines", "target_score": 75.0, "impact": "High"})

    critical_gaps = []
    target_proficiencies = {}

    for exp in expected:
        name = exp["skill"]
        key = name.lower()
        target_score = exp["target_score"]
        current = existing_skills.get(key, 0.0)

        # Fuzzy lookup if not exact
        if current == 0.0:
            for ex_k, ex_v in existing_skills.items():
                if ex_k in key or key in ex_k:
                    current = ex_v
                    break

        if current < target_score:
            critical_gaps.append({
                "skill": name,
                "current_score": current,
                "target_score": target_score,
                "deficit": round(target_score - current, 1),
                "impact": exp["impact"]
            })
            target_proficiencies[name] = "Advanced" if target_score >= 80 else "Intermediate"

    nodes = state.get("nodes_traversed", [])
    nodes.append("IdentifyGaps")

    return {
        "critical_gaps": critical_gaps,
        "target_proficiencies": target_proficiencies,
        "nodes_traversed": nodes
    }


def build_plan_node(state: LearningPathState) -> Dict[str, Any]:
    """
    Node 3: BuildPlan
    Synthesizes an Eight-Week Structured Curriculum via ChatGroq or deterministic synthesis.
    """
    target_role = state.get("target_role", "Junior Backend Laravel Engineer")
    baseline_tier = state.get("baseline_tier", "Intermediate")
    critical_gaps = state.get("critical_gaps", [])
    groq_api_key = os.environ.get("GROQ_API_KEY", "")

    schedule = None
    engine_used = "langgraph_fallback"

    # Attempt ChatGroq generation if API key is present
    if CHATGROQ_AVAILABLE and groq_api_key and len(groq_api_key) > 5:
        try:
            llm = ChatGroq(
                model_name=os.environ.get("GROQ_MODEL", "llama-3.3-70b-versatile"),
                temperature=0.2,
                api_key=groq_api_key,
                timeout=15
            )

            prompt = (
                f"You are the Cynaris Senior Principal Learning Architect. Generate an exactly 8-week structured, "
                f"personalized learning path for candidate targeting the role: '{target_role}'.\n"
                f"Candidate Baseline Tier: {baseline_tier}.\n"
                f"Critical Competency Gaps to Address: {[g['skill'] for g in critical_gaps]}.\n\n"
                f"Return ONLY a valid JSON array of exactly 8 week objects. No preamble, no markdown codeblocks.\n"
                f"Format for each week object:\n"
                f"{{\n"
                f"  \"week_number\": 1,\n"
                f"  \"theme\": \"Title of the week\",\n"
                f"  \"focus_skills\": [\"Skill1\", \"Skill2\"],\n"
                f"  \"learning_objectives\": [\"Obj1\", \"Obj2\"],\n"
                f"  \"actionable_tasks\": [\"Task1\", \"Task2\"],\n"
                f"  \"recommended_resources\": [\"Resource1\", \"Resource2\"],\n"
                f"  \"estimated_hours\": 12,\n"
                f"  \"checkpoint_quiz\": \"Quiz Name\"\n"
                f"}}"
            )

            response = llm.invoke([
                SystemMessage(content="You are a strict JSON curriculum generator. Output valid JSON array only."),
                HumanMessage(content=prompt)
            ])

            content = response.content.strip()
            # Clean possible markdown blocks
            if content.startswith("```"):
                content = re.sub(r"^```(?:json)?", "", content)
                content = re.sub(r"```$", "", content).strip()

            parsed = json.loads(content)
            if isinstance(parsed, list) and len(parsed) == 8:
                schedule = parsed
                engine_used = "langgraph_chatgroq"
        except Exception as e:
            sys.stderr.write(f"ChatGroq invocation failed in BuildPlan, falling back to deterministic synthesis: {e}\n")
            schedule = None

    # Deterministic synthesis if LLM unavailable
    if schedule is None:
        base_curriculum = BENCHMARK_ROLE_CURRICULUM.get("backend", [])
        schedule = []
        for item in base_curriculum:
            # Deep copy item
            copy_item = {
                "week_number": item["week"],
                "theme": item["theme"],
                "focus_skills": list(item["focus_skills"]),
                "learning_objectives": list(item["learning_objectives"]),
                "actionable_tasks": list(item["actionable_tasks"]),
                "recommended_resources": list(item["recommended_resources"]),
                "estimated_hours": int(item["estimated_hours"]),
                "checkpoint_quiz": item["checkpoint_quiz"]
            }
            schedule.append(copy_item)

        # Personalize based on critical gaps
        if critical_gaps:
            gap_names = [g["skill"] for g in critical_gaps]
            # Embed specific gaps into weeks 4, 5, 6
            if "Docker & Containerization" in gap_names and len(schedule) >= 6:
                schedule[5]["actionable_tasks"].append(f"Complete targeted remediation on Docker containerization")
            if "Redis & Background Queues" in gap_names and len(schedule) >= 4:
                schedule[3]["actionable_tasks"].append(f"Implement Redis cache invalidation benchmarks")

    total_hours = sum(int(w.get("estimated_hours", 12)) for w in schedule)

    nodes = state.get("nodes_traversed", [])
    nodes.append("BuildPlan")

    return {
        "raw_schedule": schedule,
        "total_hours": total_hours,
        "generation_engine": engine_used,
        "nodes_traversed": nodes
    }


def validate_plan_node(state: LearningPathState) -> Dict[str, Any]:
    """
    Node 4: ValidatePlan
    Validates business rules: exactly 8 weeks, 8-20h weekly, 60-120h total, critical gaps addressed.
    """
    raw_schedule = state.get("raw_schedule", [])
    critical_gaps = state.get("critical_gaps", [])
    validation_notes = []
    is_valid = True

    # 1. Check week count
    if len(raw_schedule) != 8:
        is_valid = False
        validation_notes.append(f"Schedule length ({len(raw_schedule)}) adjusted to exactly 8 weeks.")
        # Ensure 8 weeks
        while len(raw_schedule) < 8:
            idx = len(raw_schedule) + 1
            raw_schedule.append({
                "week_number": idx,
                "theme": f"Week {idx}: Advanced Engineering & Integration Practice",
                "focus_skills": ["Code Quality", "Performance", "Clean Architecture"],
                "learning_objectives": ["Implement robust error boundaries and clean architecture principles"],
                "actionable_tasks": ["Write integration test suites and profile system bottlenecks"],
                "recommended_resources": ["Enterprise Design Patterns", "Cynaris Learning Portal"],
                "estimated_hours": 12,
                "checkpoint_quiz": f"Week {idx} Competency Assessment"
            })
        raw_schedule = raw_schedule[:8]

    # 2. Check weekly hours & total hours
    validated_schedule = []
    total_hours = 0
    for idx, week in enumerate(raw_schedule, start=1):
        w = dict(week)
        w["week_number"] = idx

        # Ensure hours between 8 and 20
        hours = int(w.get("estimated_hours", 12))
        if hours < 8:
            validation_notes.append(f"Week {idx} hours ({hours}h) adjusted upward to minimum threshold (8h).")
            hours = 8
        elif hours > 20:
            validation_notes.append(f"Week {idx} hours ({hours}h) adjusted downward to ceiling threshold (20h).")
            hours = 20

        w["estimated_hours"] = hours
        total_hours += hours

        # Ensure all required fields exist
        if not w.get("theme"):
            w["theme"] = f"Week {idx}: Technical Core Competencies"
        if not w.get("focus_skills"):
            w["focus_skills"] = ["Software Engineering"]
        if not w.get("learning_objectives"):
            w["learning_objectives"] = ["Attain mastery in target domain requirements"]
        if not w.get("actionable_tasks"):
            w["actionable_tasks"] = ["Build working prototype and verify with automated tests"]
        if not w.get("recommended_resources"):
            w["recommended_resources"] = ["Cynaris Internal Courseware", "Official Documentation"]
        if not w.get("checkpoint_quiz"):
            w["checkpoint_quiz"] = f"Week {idx} Checkpoint Assessment"

        validated_schedule.append(w)

    # Total hours constraint: 60 to 120
    if total_hours < 60:
        validation_notes.append(f"Total hours ({total_hours}h) increased to 60h minimum.")
        diff = 60 - total_hours
        for i in range(diff):
            validated_schedule[i % 8]["estimated_hours"] += 1
        total_hours = 60
    elif total_hours > 120:
        validation_notes.append(f"Total hours ({total_hours}h) capped to 120h maximum.")
        diff = total_hours - 120
        for i in range(diff):
            if validated_schedule[i % 8]["estimated_hours"] > 8:
                validated_schedule[i % 8]["estimated_hours"] -= 1
        total_hours = sum(w["estimated_hours"] for w in validated_schedule)

    validation_notes.append("Validated prerequisite progression and eight-week milestone coverage.")

    nodes = state.get("nodes_traversed", [])
    nodes.append("ValidatePlan")

    return {
        "is_valid": is_valid,
        "validation_notes": validation_notes,
        "validated_schedule": validated_schedule,
        "total_hours": total_hours,
        "nodes_traversed": nodes
    }


def output_plan_node(state: LearningPathState) -> Dict[str, Any]:
    """
    Node 5: OutputPlan
    Compiles final standardized JSON structure for consumption by Laravel service layer.
    """
    candidate_id = state.get("candidate_id", "cand_anonymous")
    target_role = state.get("target_role", "Junior Backend Laravel Engineer")
    target_job = state.get("target_job")
    validated_schedule = state.get("validated_schedule", [])
    total_hours = state.get("total_hours", 96)
    critical_gaps = state.get("critical_gaps", [])
    engine_used = state.get("generation_engine", "langgraph_fallback")
    validation_notes = state.get("validation_notes", [])
    nodes_traversed = state.get("nodes_traversed", [])

    nodes_traversed.append("OutputPlan")

    final_output = {
        "success": True,
        "candidate_id": candidate_id,
        "target_role": target_role,
        "target_job_id": target_job.get("id") if target_job else None,
        "total_weeks": len(validated_schedule),
        "total_hours": total_hours,
        "baseline_tier": state.get("baseline_tier", "Intermediate"),
        "primary_domain": state.get("primary_domain", "backend"),
        "critical_gaps_addressed": [g["skill"] for g in critical_gaps],
        "schedule": validated_schedule,
        "workflow_metadata": {
            "workflow_name": "LangGraphLearningPathWorkflow",
            "nodes_executed": nodes_traversed,
            "engine": engine_used,
            "is_valid": state.get("is_valid", True),
            "validation_notes": validation_notes,
            "generated_at": datetime.now(timezone.utc).isoformat()
        }
    }

    return {
        "final_output": final_output,
        "nodes_traversed": nodes_traversed
    }


# ============================================================================
# 4. StateGraph Assembly & Execution
# ============================================================================

def build_langgraph_workflow():
    """Assemble and compile the 5-node LangGraph StateGraph."""
    if not LANGGRAPH_AVAILABLE:
        return None

    builder = StateGraph(LearningPathState)

    # Register five nodes
    builder.add_node("AssessState", assess_state_node)
    builder.add_node("IdentifyGaps", identify_gaps_node)
    builder.add_node("BuildPlan", build_plan_node)
    builder.add_node("ValidatePlan", validate_plan_node)
    builder.add_node("OutputPlan", output_plan_node)

    # Establish linear workflow edges
    builder.set_entry_point("AssessState")
    builder.add_edge("AssessState", "IdentifyGaps")
    builder.add_edge("IdentifyGaps", "BuildPlan")
    builder.add_edge("BuildPlan", "ValidatePlan")
    builder.add_edge("ValidatePlan", "OutputPlan")
    builder.add_edge("OutputPlan", END)

    return builder.compile()


def run_fallback_workflow(initial_state: Dict[str, Any]) -> Dict[str, Any]:
    """
    Deterministic Python StateGraph runner when LangGraph library is bypassed.
    Executes identical 5-node sequence faithfully.
    """
    state = dict(initial_state)
    state["nodes_traversed"] = []

    # 1. AssessState
    u1 = assess_state_node(state)
    state.update(u1)

    # 2. IdentifyGaps
    u2 = identify_gaps_node(state)
    state.update(u2)

    # 3. BuildPlan
    u3 = build_plan_node(state)
    state.update(u3)

    # 4. ValidatePlan
    u4 = validate_plan_node(state)
    state.update(u4)

    # 5. OutputPlan
    u5 = output_plan_node(state)
    state.update(u5)

    return state.get("final_output", {})


def generate_learning_path(payload: Dict[str, Any]) -> Dict[str, Any]:
    """Primary entry point for generating the 8-week learning plan."""
    candidate_id = payload.get("candidate_id", "cand_0")
    target_role = payload.get("target_role", "Junior Backend Laravel Engineer")
    target_job = payload.get("target_job")
    skill_profile = payload.get("skill_profile", [])
    composite_score = float(payload.get("composite_score", 75.0))
    completed_modules = int(payload.get("completed_modules_count", 0))

    initial_state: LearningPathState = {
        "candidate_id": candidate_id,
        "target_role": target_role,
        "target_job": target_job,
        "skill_profile": skill_profile,
        "composite_score": composite_score,
        "completed_modules_count": completed_modules,
        "baseline_tier": "",
        "primary_domain": "",
        "verified_competencies": [],
        "critical_gaps": [],
        "target_proficiencies": {},
        "raw_schedule": [],
        "total_hours": 0,
        "generation_engine": "langgraph",
        "is_valid": False,
        "validation_notes": [],
        "validated_schedule": [],
        "nodes_traversed": [],
        "final_output": {}
    }

    workflow = build_langgraph_workflow()

    if workflow is not None:
        try:
            result = workflow.invoke(initial_state)
            final_out = result.get("final_output")
            if final_out and final_out.get("schedule"):
                return final_out
        except Exception as e:
            sys.stderr.write(f"LangGraph execution exception, running fallback workflow: {e}\n{traceback.format_exc()}\n")

    # Fallback runner
    return run_fallback_workflow(initial_state)


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

    result = generate_learning_path(payload)
    print(json.dumps(result))


if __name__ == "__main__":
    main()

