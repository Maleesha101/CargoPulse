# AGENT.md — CargoPulse Development Rules

CargoPulse — The Phantom Shipment is an intentionally vulnerable SQL injection training lab.

## Core chain

1. Customer shipment search
2. In-band SQL injection
3. Application/database information discovery
4. Reporting workflow discovery
5. Stored report filter
6. Background report worker
7. Second-order SQL injection
8. Restricted training data

## Technology

- Laravel / PHP 8.3+
- PostgreSQL
- Redis
- Nginx
- Docker Compose

## Rules

Intentional vulnerabilities must be isolated, deterministic, documented, tested, and paired with secure remediation.

Do not accidentally introduce unrelated vulnerabilities. Do not fix intentional vulnerable paths unless working on the secure implementation.

Never add real credentials, real personal data, reverse shells, host command execution, persistence, unrestricted callbacks, host filesystem access, or destructive actions.

Keep the attack chain inside the local application and its database/queue services.

## Development

- Use small reviewable commits.
- Update documentation when behavior changes.
- Run relevant tests after meaningful changes.
- Never commit `.env` or secrets.
- Keep vulnerable and secure implementations clearly distinguishable.
- Avoid obvious endpoints such as `/get-flag` or `/vulnerable-sqli`.
- Do not expose the final flag through source code, static assets, environment variables, or debug routes.

## Git workflow

Recommended branches:

- `feat/app-foundation`
- `feat/customer-portal`
- `feat/sqli-chain`
- `feat/report-worker`
- `feat/security-tests`
- `fix/sqli-remediation`

Use meaningful conventional commits. Before completing a phase, inspect the diff, run tests, verify documented behavior, check for secrets, and commit.

## Definition of done

A learner must be able to start the lab locally, investigate the intended SQLi chain, retrieve the training flag, and verify that the secure implementation blocks the chain.
