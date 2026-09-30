# SQL Injection Remediation

## Direct SQL injection

The root cause is constructing SQL from attacker-controlled input.

Use parameterized database access, Laravel query builder, Eloquent, or prepared statements. Do not rely on escaping as the primary defense.

## Second-order SQL injection

A value stored during one workflow can become dangerous when another component later interprets it as SQL.

Represent report filters as structured data, for example:

```text
field = status
operator = equals
value = DELIVERED
```

Allowlist permitted fields, operators, and expected value types. Keep SQL structure controlled by application code while user-controlled values remain parameters.

## Defense in depth

- least-privilege database accounts
- safe error handling
- authorization checks
- audit logging
- regression tests
- monitoring

## Verification

Replay the same security test cases against the secure implementation. Confirm that injected syntax is treated as data, unrelated records are not exposed, and legitimate reporting still works.
