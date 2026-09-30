# CargoPulse Testing Guide

## Tools

- Browser
- Burp Suite
- curl
- jq
- PostgreSQL client for defensive verification only

## Phase 1 — Reconnaissance

Map routes, authentication flows, parameters, cookies, request methods, response codes, and response lengths.

## Phase 2 — Input discovery

Investigate shipment lookup, search/filter parameters, report creation, report filters, and JSON inputs.

## Phase 3 — SQLi detection

Compare controlled requests and observe status codes, response differences, response length, errors, timing, and behavior under logically different conditions.

A lack of database errors does not prove that a parameter is safe.

## Phase 4 — Data discovery

Once SQL injection is established, investigate database technology, query structure, relevant application tables, report workflow data, and stored filter values.

## Phase 5 — Second-order investigation

Trace:

```text
HTTP request -> storage -> report_jobs -> worker -> SQL query -> result
```

The key question is whether data stored earlier becomes interpreted as SQL later.

## Phase 6 — Impact validation

Confirm that the intended restricted training data can be reached without leaving the application/database boundary.

## Phase 7 — Remediation verification

Replay the same tests against the secure implementation. Malicious syntax must be treated as data and report filters must accept only supported fields/operators.

Use Burp Repeater for controlled request comparison and HTTP history for application-flow mapping.
