# CargoPulse — The Phantom Shipment

**An intentionally vulnerable logistics-management application for SQL injection training.**

CargoPulse is a realistic training environment designed to demonstrate how multiple SQL injection weaknesses can be chained together during an application-security assessment. It simulates a logistics company with a customer-facing portal and an internal operations console.

---

## Lab Rules — READ FIRST

> **⚠️ Authorized local training environment only.**
>
> - This application is **intentionally vulnerable** and must **NOT** be deployed publicly.
> - All data, credentials, and secrets in this lab are **fictional** and isolated.
> - Run the lab only in an isolated local Docker environment.
> - Do not use these techniques against systems you do not own or have written authorization to test.
> - The final flag is stored securely in the database and is **not** exposed in source code, static assets, environment variables, or debug routes.

---

## Scenario

CargoPulse is a third-party logistics provider handling shipments for pharmaceutical, electronics, and industrial customers. The company operates two application surfaces:

| Surface | Route | Purpose |
|---------|-------|---------|
| **Customer Portal** | `/portal` | Customers track shipments, view delivery events, and request reports |
| **Operations Console** | `/ops` | Employees investigate shipments, manage compliance reports, and process warehouse notes |

The operations system is designed to be inaccessible to ordinary customer accounts — but a trained security tester may discover its existence and structure through data leakage...

---

## Learning Objectives

By completing this lab you will learn to:

1. Discover SQL injection through application behavior testing
2. Identify injectable parameters systematically
3. Distinguish normal behavior from SQL-driven behavior
4. Exploit an in-band SQL injection vulnerability
5. Enumerate database schema information
6. Identify sensitive data exposed through SQL injection
7. Understand second-order SQL injection
8. Follow an attack chain across multiple application components
9. Identify trust boundaries between customer and operations systems
10. Investigate with Burp Suite and other AppSec tools
11. Fix vulnerabilities using parameterized queries
12. Verify fixes prevent the original attack chain

---

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Browser                                  │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Nginx (Port 8080)                           │
│                    Reverse Proxy                                │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│              Laravel Application (PHP 8.3, Redis cache)         │
│  ┌────────────────────┐  ┌─────────────────────────────────┐   │
│  │  Customer Portal   │  │  Operations Console             │   │
│  │  /portal/*         │  │  /ops/*                         │   │
│  │  (Vulnerable SQL)  │  │  (Second-order SQLi)            │   │
│  └────────────────────┘  └─────────────────────────────────┘   │
└──────────────────────────┬──────────────────────────────────────┘
                           │
          ┌────────────────┼────────────────┐
          ▼                ▼                ▼
   ┌────────────┐  ┌────────────┐   ┌────────────┐
   │ PostgreSQL │  │    Redis   │   │  Report    │
   │  Database  │  │  Session   │   │  Worker    │
   └────────────┘  └────────────┘   └────────────┘
```

## Planned Attack Chain

```
Customer Shipment Search
         |
         v
    In-band SQLi
         |
         v
Database / application-data discovery
         |
         v
Report workflow discovery (via CP-VOID-7719 shipment)
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

---

## Requirements

- Docker Engine (for building and running the lab)
- Docker Compose
- A modern web browser (or curl for headless testing)
- Burp Suite (recommended, for the investigation phases)

---

## Installation

```bash
git clone <repository-url>
cd cargopulse-sqli-lab
docker compose up --build
```

The application will be available at: **http://localhost:8080**

---

## Access & Credentials

All credentials are **training-only, fictional**.

| Email | Password | Role | Access |
|-------|----------|------|--------|
| customer@cargopulse.test | LabPass123! | customer | /portal/* only |
| support@cargopulse.test | LabPass123! | support | /portal/* only |
| operations@cargopulse.test | LabPass123! | operations | /portal/* + /ops/* |
| compliance@cargopulse.test | LabPass123! | compliance | /portal/* + /ops/* |
| admin@cargopulse.test | LabPass123! | admin | All routes |

**Reset the lab (drop all data):**
```bash
docker compose down -v
docker compose up --build
```

---

## Recommended Workflow

### Phase 1: Reconnaissance
1. Log in as a customer user and explore the Customer Portal.
2. Map all endpoints and identify input points (try the Shipment Lookup form).
3. Attempt to access `/ops/*` as a customer — note the access denial behavior.

### Phase 2: Discover the Injection
1. Test the shipment search with normal tracking numbers.
2. Compare responses for valid vs invalid tracking numbers.
3. Try boolean conditions (`' OR '1'='1`, `' AND '1'='2`).
4. Confirm SQL injection via behavioral differences.

### Phase 3: Enumeration
1. Determine the column count using `ORDER BY` / UNION tests.
2. Enumerate table and column names via `information_schema`.
3. Look for interesting tables (users, report_jobs, internal_credentials, security_challenges).

### Phase 4: Report Workflow Discovery
1. Find the `CP-VOID-7719` shipment (restricted status, special metadata).
2. Query the `report_jobs` table to understand the report filter mechanism.
3. Identify how report filters are stored and later processed.

### Phase 5: Second-Order Exploitation
1. Log in as an operations user.
2. Create a report with a malicious filter expression in the Report Builder.
3. Generate the report to trigger the second-order SQL injection.
4. Observe the report results reveal restricted data — including the security challenge flag.

### Phase 6: Remediation
1. Read `docs/remediation.md` to learn how to fix both vulnerabilities.
2. Review the secure implementations in `app/Security/`.
3. Run the automated regression tests to verify the fix.

---

## Learning Modes

### Guided Mode (for beginners)

Follow these progressive hints:

1. **Hint 1:** Look closely at how tracking numbers are handled in the shipment lookup.
2. **Hint 2:** Compare responses when the search condition changes logically (try `' OR '1'='1` vs `' AND '1'='2`).
3. **Hint 3:** Think about whether database query results can be incorporated into the normal response (UNION-based injection).
4. **Hint 4:** Look for data related to the reporting workflow — the restricted `CP-VOID-7719` shipment is a clue.
5. **Hint 5:** Ask what happens to report filters after they are stored — are they ever executed?

### Expert Mode

Investigate completely independently:
- Do not read the guided hints.
- Do not read `docs/solutions/attack-walkthrough.md` until you have completed the lab.
- Document your findings, payloads, and reasoning in your own notes.

---

## Documentation

| Document | Description |
|----------|-------------|
| `docs/architecture.md` | Full system architecture, components, and trust boundaries |
| `docs/attack-chain.md` | Detailed conceptual attack chain by stage |
| `docs/vulnerability-map.md` | Vulnerability inventory with locations and impacts |
| `docs/testing-guide.md` | Burp Suite investigation workflow |
| `docs/remediation.md` | Secure implementation guidance |
| `docs/solutions/attack-walkthrough.md` | **SPOILER** — complete solution walkthrough |

---

## Verification

A fresh user should be able to:

1. Clone the repository and start the lab with Docker Compose.
2. Log in with the provided training account.
3. Discover the shipment search functionality.
4. Identify the SQL injection through behavioral testing.
5. Investigate the database through the vulnerable functionality.
6. Discover the report workflow via database enumeration.
7. Understand the stored-data flow (report filter mechanism).
8. Demonstrate the second-order SQL injection.
9. Reach the intended restricted training data.
10. Retrieve the final flag.
11. Read the remediation guide.
12. Verify the secure implementation blocks the attack chain.

---

## Repository Structure

```
cargopulse-sqli-lab/
├── app/                    # Application source
│   ├── Console/Commands/   # Report worker command
│   ├── Http/Controllers/   # Controllers (vulnerable implementations)
│   ├── Models/             # Eloquent models
│   └── Security/           # Secure remediation implementations
├── database/
│   ├── migrations/         # Database schema migrations
│   └── seeders/            # Training data seeders
├── docs/                   # Security training documentation
│   └── solutions/          # SPOILER: complete walkthrough
├── resources/views/        # Blade templates
├── tests/                  # Security regression tests
└── docker/                 # Container configuration
```

---

## License

This is an intentionally vulnerable training application for educational purposes. All data, credentials, and secrets are fictional. Do not deploy this application publicly.