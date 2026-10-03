# Remediation Guide — CargoPulse Training Lab

This document explains how to fix the SQL injection vulnerabilities in the CargoPulse training lab, transforming it from an intentionally vulnerable application to a secure implementation.

## Overview

The lab contains two SQL injection vulnerabilities:
1. **SQL-01**: In-band SQL injection in shipment search (`ShipmentController::search`)
2. **SQL-02**: Second-order SQL injection in report processing (`ReportController::generate`)

Both vulnerabilities stem from concatenating user input directly into SQL queries instead of using parameterized queries or proper input validation.

## Fixing SQL-01 — Shipment Search (In-band SQL Injection)

### Vulnerable Code
```php
// INTENTIONALLY VULNERABLE — SQLi training lab
$query = $request->input('tracking');
$results = DB::select("SELECT * FROM shipments WHERE tracking_number LIKE '%{$query}%'");
```

### Root Cause
User input is directly interpolated into the SQL query string, allowing attackers to break out of the LIKE clause and inject arbitrary SQL.

### Secure Solution
Use Laravel's query builder or parameterized queries with prepared statements.

#### Option 1: Laravel Query Builder (Recommended)
```php
use App\Models\Shipment;

public function search(Request $request)
{
    $query = $request->input('tracking');
    
    $results = Shipment::where('tracking_number', 'LIKE', "%{$query}%")
        ->get();
        
    // ... logging and return view
}
```

#### Option 2: Parameterized Query with DB::select
```php
public function search(Request $request)
{
    $query = $request->input('tracking');
    
    // SECURE: Parameterized query - input never touches SQL syntax
    $results = DB::select("SELECT * FROM shipments WHERE tracking_number LIKE ?", ["%{$query}%"]);
    
    // ... logging and return view
}
```

### Why This Fixes the Vulnerability
- The database driver separates SQL code from data
- Input is treated strictly as a string value, never as executable SQL
- Escaping is handled automatically by the PDO driver
- No matter what special characters are in `$query`, they cannot alter the query structure

### Additional Security Considerations
1. **Input Validation**: While not strictly necessary for SQL injection prevention with parameterized queries, validate input for business logic:
   ```php
   // Validate tracking number format if needed
   if (!preg_match('/^CP-[A-Z0-9]{2}-[0-9]{4}$/', $query)) {
       // Handle invalid format
   }
   ```
2. **Output Encoding**: Always encode output when displaying user data to prevent XSS (though not relevant to SQLi).

## Fixing SQL-02 — Report Filter Processing (Second-Order SQL Injection)

### Vulnerable Code
```php
// Stored filter from database
$storedFilter = $reportJob->getFilterExpression();

// INTENTIONALLY VULNERABLE — SQLi training lab
$query = "SELECT s.*, COUNT(se.id) as event_count
         FROM shipments s
         LEFT JOIN shipment_events se ON s.id = se.shipment_id
         WHERE " . $storedFilter . "
         GROUP BY s.id
         ORDER BY s.created_at DESC";
```

### Root Cause
The application assumes that data stored safely in the database remains safe when later used in SQL construction. However, the stored filter expression is concatenated directly into SQL without parameterization during report generation.

### Secure Solution
Two approaches:
1. **Reject dynamic SQL entirely** (safest)
2. **Use strict allowlists and structured query building** (more flexible)

#### Option 1: Reject Dynamic SQL (Recommended for High Security)
```php
// In ReportController::storeFilter()
public function storeFilter(Request $request)
{
    // ... validation
    
    // Store only predefined, safe filter types
    $allowedFilters = [
        'restricted_shipments' => 'is_restricted = 1',
        'customer_shipments' => "customer_name = '{$request->input('customer_name')}'",
        'destination_based' => "destination = '{$request->input('destination')}'",
        // ... other predefined filters
    ];
    
    $filterKey = $request->input('filter_type');
    if (!array_key_exists($filterKey, $allowedFilters)) {
        return back()->with('error', 'Invalid filter type selected');
    }
    
    $safeFilter = $allowedFilters[$filterKey];
    
    // Store the safe filter
    $reportJob = ReportJob::create([
        'user_id' => $user->id,
        'report_type' => $request->report_type,
        'filter_expression' => $safeFilter,
        'status' => 'pending',
    ]);
}
```

#### Option 2: Allowlist-Based Structured Queries (More Flexible)
```php
// SecureReportController.php
class SecureReportController extends Controller
{
    protected $validFilterFields = [
        'status',
        'report_type', 
        'customer_name',
        'origin',
        'destination',
        'tracking_number',
        'is_restricted'
    ];
    
    protected $validOperators = ['=', 'LIKE', '>', '<', '>=', '<=', 'IN'];
    
    public function generate(Request $request)
    {
        // ... authentication and job retrieval
        
        // SECURE: Parse and validate filter expression
        $conditions = $this->parseFilterExpression($reportJob->filter_expression);
        
        // Build parameterized query
        $query = "SELECT s.*, COUNT(se.id) as event_count
                 FROM shipments s
                 LEFT JOIN shipment_events se ON s.id = se.shipment_id";
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions['sql']);
        }
        
        $query .= " GROUP BY s.id ORDER BY s.created_at DESC";
        
        $results = DB::select($query, $conditions['params']);
        
        // ... save results and return view
    }
    
    protected function parseFilterExpression($expression)
    {
        $conditions = [];
        $params = [];
        
        // Match patterns like: field operator value
        // More robust parsing would handle complex expressions
        $pattern = '/(\w+)\s*(=|LIKE|>|<|>=|<=|IN)\s*([^\s,]+)/i';
        
        if (preg_match_all($pattern, $expression, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $field = $match[1];
                $operator = strtoupper($match[2]);
                $value = $match[3];
                
                // Validate against allowlists
                if (!in_array($field, $this->validFilterFields)) {
                    throw new \InvalidArgumentException("Invalid field: {$field}");
                }
                
                if (!in_array($operator, $this->validOperators)) {
                    throw new \InvalidArgumentException("Invalid operator: {$operator}");
                }
                
                // Add parameterized condition
                $conditions[] = "s.{$field} {$operator} ?";
                $params[] = $value;
            }
        }
        
        return ['sql' => $conditions, 'params' => $params];
    }
}
```

### Why This Fixes the Vulnerability
- **Option 1**: Eliminates dynamic SQL entirely by only allowing predefined safe filter expressions
- **Option 2**: Uses strict allowlists to validate which fields and operators are permitted, then builds the query using parameterized statements
- Both approaches ensure that stored data, even if malicious, cannot alter the SQL query structure

### Critical Security Principle
> **"Stored safely" does not mean "safe forever."**  
> Data that is safe to store can become dangerous when later interpreted in a different context (like SQL, HTML, JSON, shell commands, etc.).

## Defense in Depth

While parameterized queries are the primary defense, implement these additional layers:

### 1. Principle of Least Privilege
- Database user should have only necessary permissions (SELECT, INSERT, UPDATE on needed tables)
- Avoid granting DROP, ALTER, CREATE to application users
- Consider separate users for different privileges if needed

### 2. Input Validation
Validate input for business rules, even if not strictly needed for SQLi prevention:
```php
// Validate tracking number format
if (!preg_match('/^CP-[A-Z]{2}-[0-9]{4}$/', $tracking)) {
    throw new ValidationException('Invalid tracking number format');
}

// Validate report filter expressions for reasonableness
if (strlen($filter_expression) > 255) {
    throw new ValidationException('Filter expression too long');
}
```

### 3. Output Encoding
When displaying database data in HTML views:
```blade
{{-- Blade automatically escapes by default --}}
<td>{{ $shipment->customer_name }}</td>

--{!! $unsafe_html !!}--{!! --}  -- Never use unless data is trusted and properly sanitized
```

### 4. Logging and Monitoring
- Log all SQL errors (without exposing details to users)
- Monitor for suspicious query patterns
- Log failed access attempts to protected endpoints

### 5. Defense Against Second-Order Specific Risks
- **Input Validation at Storage Time**: Apply the same validation rules when storing as when using
- **Context-Aware Output Encoding**: Different contexts (SQL, HTML, JSON, shell) require different escaping
- **Use Type-Safe APIs**: ORM query builders, JSON serializers, etc.
- **Implement Usage Auditing**: Track how stored data is later used

## Secure Implementation Files

The lab includes secure implementations in the `app/Security/` directory:

### Secure Shipment Search
- **File**: `app/Security/SecureShipmentSearch.php`
- **Usage**: Replaces `ShipmentController::search()` logic
- **Method**: Uses parameterized queries with `DB::select("... = ?", [$param])`

### Secure Report Controller  
- **File**: `app/Security/SecureReportController.php`
- **Usage**: Replaces `OpsReportController` logic
- **Methods**: 
  - `validateFilterExpression()`: Checks for dangerous SQL patterns
  - `parseFilterExpression()`: Converts filter to parameterized conditions
  - `generate()`: Builds safe parameterized query

## Verification of Fixes

After applying the secure implementations:

### Test SQL-01 Fix
```bash
# Should NOT return all shipments
curl -s "http://localhost:8080/portal/shipments/search?tracking=' OR '1'='1" | grep -c "Shipment"

# Should return 0 results for non-existent tracking
curl -s "http://localhost:8080/portal/shipments/search?tracking='xyz'" | grep -c "No shipment found"
```

### Test SQL-02 Fix
```bash
# Attempt to store malicious filter
# Should be rejected with validation error

# Even if somehow stored, should NOT execute as SQL when generating report
# Report generation should either fail safely or produce no results
```

## Configuration for Secure vs Vulnerable Modes

The lab can be run in two modes:

### Vulnerable Mode (Default)
- Uses controllers in `app/Http/Controllers/`
- Intentional vulnerabilities present
- For training and exploitation practice

### Secure Mode
- Use controllers in `app/Security/` instead
- All SQLi vectors blocked
- For demonstrating fixes and regression testing

To switch modes, update the route bindings in `routes/web.php` to point to the secure controllers instead of the vulnerable ones.

## Testing the Fixes

Run the security regression tests:
```bash
php artisan test --filter=SqlInjectionTest
```

All tests should pass, confirming:
1. Normal functionality still works
2. SQL injection attempts are blocked in secure implementation
3. Second-order SQLi cannot extract restricted data
4. Authorization boundaries are maintained
5. The flag remains inaccessible through normal channels

## Conclusion

By applying parameterized queries and strict input validation, both SQL injection vulnerabilities in the CargoPulse lab are eliminated while preserving all legitimate functionality. The remediation demonstrates core secure coding principles that prevent entire classes of injection vulnerabilities.