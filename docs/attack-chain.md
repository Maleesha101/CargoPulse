# Attack Chain — CargoPulse Training Lab

This document describes the conceptual attack chain for the CargoPulse SQL injection training lab.

## Overview

```
External Input
      ↓
Shipment Search
      ↓
SQL Query (In-band SQLi)
      ↓
Database Information
      ↓
Report Workflow Discovery
      ↓
Stored Filter
      ↓
Background Worker
      ↓
Second SQL Query (Second-Order SQLi)
      ↓
Restricted Data
      ↓
Challenge Flag
```

## Stage 1 — External Input

**Attacker objective**: Identify input points that interact with database queries.

**Observable behavior**: The customer portal provides a shipment lookup feature at `/portal/shipments/search?tracking=`. Entering a tracking number returns shipment data.

**Security weakness**: User input is passed directly to database query construction.

**Required knowledge**: Understanding that tracking number input flows to SQL.

**Resulting information**: The shipment lookup endpoint is the entry point for the attack.

## Stage 2 — Shipment Search

**Attacker objective**: Confirm that input reaches the database query.

**Observable behavior**: Normal tracking numbers return shipment records. Invalid tracking numbers return "No shipment found." without errors.

**Security weakness**: The application constructs SQL by concatenating user input: `SELECT * FROM shipments WHERE tracking_number LIKE '%{user_input}%'`

**Required knowledge**: Understanding string interpolation in SQL context.

**Resulting information**: The query uses `LIKE` with string concatenation, making it susceptible to injection.

## Stage 3 — SQL Query (In-band SQLi)

**Attacker objective**: Confirm SQL injection by observing behavioral differences.

**Observable behavior**: Injecting `' OR 1=1--` returns all shipments. Injecting `' AND 1=2--` returns no shipments. UNION-based injection returns data from other tables.

**Security weakness**: User input is directly concatenated into the SQL query without parameterization.

**Required knowledge**: SQL injection techniques including UNION queries and boolean conditions.

**Resulting information**: The query structure is understood (7 columns: id, tracking_number, customer_name, origin, destination, shipped_at, is_restricted).

## Stage 4 — Database Information

**Attacker objective**: Enumerate the database schema and identify interesting tables.

**Observable behavior**: UNION injection with `information_schema.columns` reveals table names and column names.

**Security weakness**: The application returns query results directly in the response, enabling in-band data extraction.

**Required knowledge**: Database schema enumeration techniques.

**Resulting information**: Tables identified include `users`, `shipments`, `shipment_events`, `warehouse_notes`, `report_jobs`, `internal_credentials`, and `security_challenges`.

## Stage 5 — Report Workflow Discovery

**Attacker objective**: Find the second-order SQL injection path.

**Observable behavior**: Through database enumeration, the attacker discovers the `report_jobs` table and the `CP-VOID-7719` shipment record. The special shipment's metadata contains references to a legacy enrichment process involving the report workflow.

**Security weakness**: The `report_jobs.filter_expression` column stores user-provided filter text that is later used in SQL construction.

**Required knowledge**: Understanding data flow between application components (data stored now, used later).

**Resulting information**: The report system stores filter expressions in `report_jobs.filter_expression`. The operations console generates reports at `/ops/reports/generate`.

## Stage 6 — Stored Filter

**Attacker objective**: Store a malicious filter expression in the report queue.

**Observable behavior**: An operations user can create a report with an arbitrary filter expression. The expression is stored in `report_jobs.filter_expression` without validation.

**Security weakness**: No validation or sanitization is applied to filter expressions before storage. The application assumes stored data is safe.

**Required knowledge**: Understanding that stored data can be malicious.

**Resulting information**: The filter expression `1=1 UNION SELECT ... FROM security_challenges--` can be stored as a "valid" filter.

## Stage 7 — Background Worker

**Attacker objective**: Trigger the stored filter processing.

**Observable behavior**: When a report is generated, the stored filter expression is concatenated directly into a SQL query:

```sql
SELECT s.*, COUNT(se.id) as event_count
FROM shipments s
LEFT JOIN shipment_events se ON s.id = se.shipment_id
WHERE {stored_filter}
GROUP BY s.id
ORDER BY s.created_at DESC
```

**Security weakness**: The stored filter is used in SQL construction without parameterization, even though it was stored "safely."

**Required knowledge**: Understanding that "stored safely" does not mean "safe to execute as SQL."

**Resulting information**: The second-order SQL injection is executed when the report is processed.

## Stage 8 — Second SQL Query (Second-Order SQLi)

**Attacker objective**: Exploit the stored injection to extract restricted data.

**Observable behavior**: The UNION injection in the stored filter causes the report to return data from restricted tables (`security_challenges`, `internal_credentials`).

**Security weakness**: Second-order SQL injection — data that was stored without harm is later interpreted as SQL code in a privileged context.

**Required knowledge**: Understanding how to craft second-order injection payloads that exploit the query structure of the report worker.

**Resulting information**: Internal credentials and the challenge flag become accessible through the report results.

## Stage 9 — Restricted Data

**Attacker objective**: Access the security challenge flag.

**Observable behavior**: The report results now include rows from the `security_challenges` table alongside shipment data.

**Security weakness**: The second-order SQL injection provides read access to all tables in the database.

**Required knowledge**: Identifying which tables contain the target data and how to extract it via UNION injection.

**Resulting information**: The final flag `CP{second_order_sqli_chain_complete}` is revealed.

## Stage 10 — Challenge Flag

**Attacker objective**: Retrieve the complete training flag.

**Observable behavior**: The flag is displayed in the report results table.

**Security weakness**: The combination of in-band SQLi (discovery) and second-order SQLi (exploitation) allows full database access, exposing the challenge flag.

**Required knowledge**: Completing the full attack chain from initial input injection to second-order exploitation.

**Resulting information**: The flag `CP{second_order_sqli_chain_complete}` is obtained.

## Key Concepts Demonstrated

1. **Chaining vulnerabilities**: The first vulnerability enables discovery of the second.
2. **Second-order SQL injection**: Data safe in storage becomes dangerous in execution context.
3. **Trust boundaries**: Customer-facing endpoints can reveal internal system structure.
4. **Data flow analysis**: Following attacker-controlled data through multiple application components.
5. **Parameterized queries**: The remediation that prevents both attack vectors.

## Remediation

See [remediation.md](remediation.md) for secure implementation guidance.