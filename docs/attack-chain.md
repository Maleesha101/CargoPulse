# CargoPulse Attack Chain

The lab is designed so the learner must follow attacker-controlled data through the application.

```text
Shipment Search
      |
      v
  SQL injection
      |
      v
Application/database discovery
      |
      v
Report workflow
      |
      v
Stored filter
      |
      v
Background worker
      |
      v
Second-order SQL injection
      |
      v
Restricted training data
```

## Stages

### 1. Reconnaissance
Map routes, authentication boundaries, parameters, cookies, and normal responses.

### 2. Shipment search
Investigate whether the lookup parameter influences database query behavior.

### 3. Data discovery
Use the vulnerable behavior to identify useful application/reporting data.

### 4. Workflow analysis
Trace report-filter data from HTTP input into persistent storage.

### 5. Second-order analysis
Determine how a stored filter is later consumed by the background worker.

### 6. Impact validation
Demonstrate access to the intended restricted training data without leaving the application/database boundary.

## Design principle

The first SQL injection is an entry point, not the final objective.

## Out of scope

- operating-system compromise
- reverse shells
- real credentials
- internet callbacks
- host filesystem access
