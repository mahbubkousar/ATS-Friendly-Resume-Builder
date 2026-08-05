# Database migrations

`resumesync_db_structure.sql` is the canonical schema for new installations.
These migrations are only for databases created before the corresponding
schema change.

Apply them in numeric order:

1. `001_create_ai_rate_limits.sql`
2. `002_create_auth_rate_limits.sql`
3. `003_add_ai_processing_consent.sql`

Back up the database first, then record each applied migration in deployment
automation. All migrations are additive and safe to run once.
