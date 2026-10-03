# AGENT.md — CargoPulse Development Rules

## Project Purpose
CargoPulse — The Phantom Shipment is an intentionally vulnerable SQL injection training lab designed for authorized application-security education.

## Core Chain
1. Customer shipment search (Customer Portal)
2. In-band SQL injection (tracking number parameter)
3. Application/database information discovery
4. Reporting workflow discovery (via CP-VOID-7719 shipment)
5. Stored report filter
6. Background report worker
7. Second-order SQL injection (stored filter concatenated into SQL)
8. Restricted training data (security_challenges table flag)

## Technology Stack
- Laravel / PHP 8.3+
- PostgreSQL
- Redis
- Nginx
- Docker Compose

## Critical Rules

### Intentional Vulnerabilities
- Must remain **isolated, deterministic, documented, tested, and paired** with secure remediation
- **Do NOT fix** intentional vulnerable paths unless explicitly working on the secure implementation (`app/Security/`)
- Vulnerable code must be clearly commented: `// INTENTIONALLY VULNERABLE — SQLi training lab`
- Vulnerabilities must be clearly separated from secure code (different namespaces/files)

### Security Boundaries
**NEVER introduce:**
- Real credentials or real personal data
- Reverse shells or host command execution
- Persistence mechanisms
- Unrestricted network callbacks to external systems
- Host filesystem access
- Destructive actions (data deletion, corruption)

**Keep the attack chain strictly inside:**
Browser → CargoPulse Application ↔ PostgreSQL/Redis ↔ CargoPulse Application

### Authentication
- Training accounts use obvious passwords documented in setup
- Authentication establishes realistic privilege boundaries
- **Do NOT** make authentication bypass the primary vulnerability learning objective

## Development Process

### Git Workflow (Recommended)
1. `feat/app-foundation` — Laravel/Docker foundation
2. `feat/customer-portal` — Customer-facing features
3. `feat/sqli-chain` — Vulnerable shipment search + initial data
4. `feat/report-worker` — Report workflow + second-order SQLi
5. `feat/security-tests` — Automated regression tests
6. `fix/sqli-remediation` — Secure implementations in `app/Security/`

### Commit Practices
- Use **small, reviewable commits** with meaningful conventional messages
- **Update documentation** when behavior changes
- **Run relevant tests** after meaningful changes
- **Never commit** `.env` files or secrets
- Keep vulnerable and secure implementations **clearly distinguishable** in code

### Documentation Requirements
1. Update `docs/` whenever application behavior changes
2. Keep `docs/architecture.md` current with component changes
3. Maintain `docs/attack-chain.md` accuracy
4. Ensure `docs/vulnerability-map.md` matches implemented vulns
5. Keep `docs/testing-guide.md` aligned with actual endpoints
6. Update `docs/remediation.md` when secure implementations change
7. Preserve `docs/solutions/attack-walkthrough.md` as SPOILER-marked solution

## Definition of Done

A feature is complete only when:
1. Code compiles and passes Laravel validation
2. Automated tests pass (for relevant components)
3. Manual verification confirms documented behavior
4. Documentation is updated to match implementation
5. No secrets or real data have been committed
6. Vulnerable/secure implementations remain properly separated
7. The attack chain remains exploitable in vulnerable version
8. The attack chain is blocked in secure version
9. Git history shows incremental, meaningful progress

## Agent Instructions

### When Working on Vulnerable Code
- Only modify explicitly marked vulnerable sections
- Never "accidentally fix" vulnerabilities during feature work
- If you must touch vulnerable areas for legitimate reasons, document why and ensure vulnerability remains
- Use the exact vulnerability patterns specified in the design

### When Working on Secure Code
- Implement in `app/Security/` namespace
- Maintain same interfaces as vulnerable counterparts
- Preserve all legitimate functionality
- Clearly document the security approach used
- Ensure secure version blocks original attack vectors

### Testing Validation
1. Verify Docker builds successfully (`docker compose up --build`)
2. Confirm application is reachable at http://localhost:8080
3. Test login works for all training roles
4. Confirm SQL injection is exploitable in vulnerable version
5. Confirm second-order chain works end-to-end
6. Test that secure version blocks attacks while maintaining function
7. Run all security regression tests
8. Validate documentation matches current state

## Emergency Procedures

If you accidentally fix a vulnerability:
1. Immediately identify what was changed
2. Revert the fix or reintroduce the vulnerability exactly as specified
3. Document the incident in commit message
4. Notify any reviewing humans that the vulnerability has been restored
5. Re-run tests to confirm exploitability is restored

## Final Review Checklist

Before considering work complete:
- [ ] No real credentials in codebase
- [ ] No real personal data in seeders
- [ ] All secrets/fake data clearly marked as training-only
- [ ] Vulnerable code explicitly commented as intentional
- [ ] Secure implementations in `app/Security/`
- [ ] Attack chain works in vulnerable version
- [ ] Attack chain blocked in secure version
- [ ] Documentation matches actual implementation
- [ ] Docker builds and runs successfully
- [ ] Tests pass for both versions (where applicable)