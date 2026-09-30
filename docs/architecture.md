# CargoPulse Architecture

CargoPulse models a logistics platform with a customer-facing shipment portal and restricted internal operations console.

## Planned architecture

```text
Browser
   |
   v
 Nginx
   |
   v
Laravel App ---- PostgreSQL
   |
   v
 Redis ---- Report Worker
```

## Application surfaces

### Customer Portal
- authentication
- shipment tracking
- shipment search
- shipment history
- customer reports

### Operations Console
- shipment investigation
- report builder
- report queue
- compliance workflow
- audit logs

Operations functionality represents a privilege boundary.

## Planned tables

- users
- shipments
- shipment_events
- warehouse_notes
- report_jobs
- audit_logs
- internal_credentials
- security_challenges

All data is fictional.

## Trust boundaries

1. Browser to web application.
2. Customer portal to operations functionality.
3. Laravel to PostgreSQL.
4. Laravel to Redis/report worker.
5. Stored report data to later report execution.

The fifth boundary is central to the second-order SQL injection lesson.

## Secure design

The secure implementation will use parameterized queries, Laravel query facilities, and allowlisted structured report filters.
