# CargoPulse Architecture Documentation

## Overview

CargoPulse is a logistics shipment management platform with two primary application surfaces:
- **Customer Portal** (`/portal`) - Customer-facing interface for tracking shipments
- **Operations Console** (`/ops`) - Internal employee interface for investigation and reporting

## Application Components

```
┌─────────────────────────────────────────────────────────────────┐
│                        Browser                                  │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Nginx (Port 80)                         │
│                    Reverse Proxy / Static Files                 │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Laravel Application (PHP 8.3)                │
│  ┌──────────────────────────┐  ┌─────────────────────────────┐  │
│  │    Customer Portal       │  │    Operations Console       │  │
│  │    /portal/*             │  │    /ops/*                   │  │
│  │                          │  │                             │  │
│  │  - Dashboard             │  │  - Dashboard                │  │
│  │  - Track Shipment        │  │  - Shipment Investigation   │  │
│  │  - Shipment History      │  │  - Report Builder           │  │
│  │  - Shipment Search       │  │  - Report Queue             │  │
│  │  - Reports               │  │  - Compliance               │  │
│  └──────────────────────────┘  └─────────────────────────────┘  │
└──────────────────────────┬──────────────────────────────────────┘
                           │
          ┌────────────────┼────────────────┐
          ▼                ▼                ▼
   ┌────────────┐  ┌────────────┐   ┌────────────┐
   │ PostgreSQL │  │    Redis   │   │  Report    │
   │  (Primary) │  │ (Session/  │   │  Worker    │
   │            │  │  Queue)    │   │ (Background)│
   └────────────┘  └────────────┘   └────────────┘
```

## Database Schema

### Core Tables

| Table | Purpose |
|-------|---------|
| `users` | Authentication and authorization (roles: customer, support, operations, compliance, admin) |
| `shipments` | Shipment tracking data with tracking numbers |
| `shipment_events` | Event history for each shipment (ARRIVED_WAREHOUSE, CUSTOMS_REVIEW, etc.) |
| `warehouse_notes` | Internal notes from operations staff |
| `report_jobs` | Stored report filters for background processing |
| `audit_logs` | Security-relevant action logging |
| `internal_credentials` | Service account credentials (fictional, training-only) |
| `security_challenges` | Training challenge flags |

### Key Relationships

```
users 1──< report_jobs >──1 shipments >──< shipment_events
                    │
                    └──< warehouse_notes
```

## Authentication & Authorization

### Roles

| Role | Portal Access | Operations Access |
|------|---------------|-------------------|
| customer | ✅ `/portal/*` | ❌ |
| support | ✅ `/portal/*` | ❌ |
| operations | ✅ `/portal/*` | ✅ `/ops/*` |
| compliance | ✅ `/portal/*` | ✅ `/ops/*` |
| admin | ✅ `/portal/*` | ✅ `/ops/*` |

### Session Management

- Laravel session driver: Redis
- Session lifetime: 120 minutes
- Role-based middleware protects routes

## Data Flow

### Customer Shipment Search (Vulnerable Path)
1. Customer enters tracking number in `/portal/shipments/search`
2. Input concatenated directly into SQL: `tracking_number LIKE '%{$query}%'`
3. Results returned to customer

### Report Workflow (Second-Order Vulnerability)
1. Operations user creates report at `/ops/reports/create`
2. Filter expression stored in `report_jobs.filter_expression`
3. Background worker (or manual generation) retrieves filter
4. Filter concatenated directly into SQL query
5. Results displayed to operations user

## Trust Boundaries

### Boundary 1: Customer ↔ Operations
- Customer accounts cannot access `/ops/*` routes
- Operations accounts can access `/portal/*` but with different permissions
- Middleware enforces role-based access control

### Boundary 2: Application ↔ Database
- Application constructs SQL dynamically
- User input flows into SQL without parameterization (intentional for training)
- Stored data later used in SQL context (second-order vulnerability)

### Boundary 3: Report Creation ↔ Report Processing
- Creation time: Filter validated but not executed
- Processing time: Filter executed in privileged context
- Time-of-check vs time-of-use discrepancy enables second-order attack

## Network Isolation (Docker)

```
frontend network: nginx ──► Laravel
backend network:  Laravel ──► PostgreSQL, Redis
                  Report Worker ──► PostgreSQL, Redis
```

- PostgreSQL and Redis NOT exposed to host
- Only port 8080 (nginx) exposed to localhost

## Vulnerability Locations

### SQL-01: Shipment Search (In-Band)
- **File**: `app/Http/Controllers/ShipmentController.php`
- **Method**: `search()`
- **Line**: `DB::select("SELECT * FROM shipments WHERE tracking_number LIKE '%{$query}%'")`

### SQL-02: Report Worker (Second-Order)
- **File**: `app/Http/Controllers/Ops/ReportController.php`
- **Method**: `generate()`
- **Line**: `$query = "SELECT ... WHERE " . $storedFilter . "..."`

## Secure Implementation

### Secure Shipment Search
- **File**: `app/Security/SecureShipmentSearch.php`
- Uses parameterized queries: `DB::select("SELECT * FROM shipments WHERE tracking_number = ?", [$tracking])`

### Secure Report Controller
- **File**: `app/Security/SecureReportController.php`
- Validates filter expressions against allowlist
- Parses filters into parameterized conditions
- Rejects dangerous SQL patterns

## Deployment

```bash
docker compose up --build
```

Access at: http://localhost:8080