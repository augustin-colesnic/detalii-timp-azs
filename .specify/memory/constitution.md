<!--
Sync Impact Report

- Version change: template -> 1.0.0
- Modified principles:
	- [PRINCIPLE_1_NAME] -> I. User-Value Delivery
	- [PRINCIPLE_2_NAME] -> II. Test-First (TDD) (NON-NEGOTIABLE)
	- [PRINCIPLE_3_NAME] -> III. Small, Releasable Units
	- [PRINCIPLE_4_NAME] -> IV. Observability & Structured Logging
	- [PRINCIPLE_5_NAME] -> V. Semantic Versioning & Breaking Changes
- Added sections: "Security & Performance Constraints", "Development Workflow & Review"
- Removed sections: none
- Templates requiring updates:
	- .specify/templates/plan-template.md ✅ updated
	- .specify/templates/spec-template.md ✅ updated
	- .specify/templates/tasks-template.md ✅ updated
	- .specify/templates/checklist-template.md ✅ updated
	- .specify/templates/agent-file-template.md ✅ updated
- Follow-up TODOs:
	- RATIFICATION_DATE: TODO(RATIFICATION_DATE): original adoption date unknown; please provide.
	- Confirm if project prefers MAJOR version numbering convention different from semantic versioning.
-->

# desktop-timp Constitution

## Core Principles

### I. User-Value Delivery
All work MUST map to at least one explicit user scenario with measurable acceptance criteria. Every change (PR) MUST include a corresponding user story or bug description and a defined acceptance test that can be executed by the reviewer.
Rationale: Ensures engineering effort delivers identifiable customer value and enables objective verification.

### II. Test-First (TDD) (NON-NEGOTIABLE)
Tests MUST be written before production code for any new feature or behavior change. Unit tests, contract tests, or integration tests MUST fail prior to implementation and pass after implementation. All CI runs MUST pass before merging.
Rationale: Guarantees regression safety and drives clear requirements-driven implementation.

### III. Small, Releasable Units
Changes MUST be delivered as minimal, reviewable PRs that are independently deployable. A single PR SHOULD not change more than one user story or add multiple unrelated features. PRs MUST include a concise summary, changelog entry, and testing instructions.
Rationale: Limits review scope, reduces risk, and enables rapid rollback and iteration.

### IV. Observability & Structured Logging
All services and scripts MUST emit structured logs (JSON or equivalent) for key operations and errors. New features that affect runtime behavior MUST add metrics (counts, latencies, and error rates) and clear, actionable log messages. Logging MUST avoid leaking secrets.
Rationale: Makes production issues diagnosable and supports SLOs and incident response.

### V. Semantic Versioning & Breaking Changes
Releases MUST follow Semantic Versioning (MAJOR.MINOR.PATCH). Breaking changes MUST increment MAJOR and be accompanied by a migration guide and a deprecation timeline. Minor releases SHOULD be backwards compatible and include explicit test coverage for new behaviors.
Rationale: Provides predictable compatibility guarantees for consumers and clear upgrade paths.

## Security & Performance Constraints

- Security: Code MUST not introduce secrets in source. All external inputs MUST be validated and escaped. Security-sensitive changes MUST include threat-model notes and at least one security-focused test or review comment.
- Performance: New features MUST include performance goals when they impact latency or throughput (e.g., target p95 latency). Any change that increases resource usage by >20% MUST be justified and reviewed.

## Development Workflow & Review

- Branching: Use short-lived feature branches named `feat/<id>-summary` or `fix/<id>-summary`.
- Reviews: Every PR MUST have at least one approving reviewer who is not the author. High-risk or security changes MUST have two approvers, including at least one senior reviewer.
- CI Gates: PRs MUST pass automated tests, linting, and a constitution compliance check (see below) before merging.
- Constitution Compliance Check (Gates): The automated check MUST verify:
	- Presence of acceptance tests or a test plan
	- Version impact (MAJOR/MINOR/PATCH) declared in PR description
	- Logging/metrics added for runtime-impacting changes

## Governance

- Amendment procedure: Propose amendments via a repository PR against `.specify/memory/constitution.md`. Each amendment PR MUST include a rationale, a migration plan (if applicable), and explicit version-bump rationale.
- Approval: Amendments require two approvals from maintainers or one maintainer + one owner. For MAJOR governance changes (removals or redefinitions of principles) the PR MUST include a migration/compatibility plan and obtain consensus from all owners.
- Versioning policy: Bump semantics:
	- MAJOR when removing or re-defining existing, non-backwards-compatible principles or governance rules.
	- MINOR when adding a new principle or materially expanding guidance.
	- PATCH for clarifications, wording fixes, or non-semantic refinements.
- Compliance reviews: At least once per 12 months a maintainer MUST open a review issue to audit compliance against the constitution.

**Version**: 1.0.0 | **Ratified**: TODO(RATIFICATION_DATE) | **Last Amended**: 2025-11-28
