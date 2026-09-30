# CargoPulse — The Phantom Shipment

CargoPulse is an intentionally vulnerable logistics-management application designed for authorized application-security training.

The lab focuses on chaining SQL injection weaknesses across a realistic business workflow rather than demonstrating a single isolated login bypass.

## Planned attack chain

```
Customer Shipment Search
        |
        v
   In-band SQLi
        |
        v
Database/application-data discovery
        |
        v
Report workflow discovery
        |
        v
Stored report filter
        |
        v
Background report worker
        |
        v
Second-order SQLi
        |
        v
Restricted training data
```

## Learning objectives

- Identify SQL injection through black-box application testing.
- Understand in-band SQL injection and database-driven responses.
- Trace attacker-controlled data through application workflows.
- Identify second-order SQL injection.
- Understand trust boundaries between customer and operations functionality.
- Validate remediation using parameterized queries and allowlisted filters.
- Practice a complete Burp Suite investigation workflow.

## Planned stack

- Laravel / PHP
- PostgreSQL
- Redis
- Nginx
- Docker Compose
- Burp Suite

## Repository status

The repository currently contains the project and security-lab foundation documentation. Application implementation will be added incrementally in subsequent branches and pull requests.

## Safety

This project is intentionally vulnerable. Run it only in an isolated, authorized local training environment. All planned data and credentials are fictional.

## Development approach

The lab will be developed in phases:

1. Foundation documentation
2. Laravel/PostgreSQL/Docker foundation
3. Customer portal
4. Intentional SQL injection path
5. Operations reporting workflow
6. Second-order SQL injection
7. Testing and challenge validation
8. Secure remediation
9. Final lab documentation

See `AGENT.md` for development rules.
