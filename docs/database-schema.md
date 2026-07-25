# Database schema diagram

This diagram reflects the current `database/database.sqlite` schema. `PK` means primary key, `FK` means foreign key, and `UK` means a column participating in a unique key. A `?` suffix marks nullable columns.

## Business tables

```mermaid
erDiagram
    projects {
        integer id PK
        varchar name
        varchar slug UK
        text location "nullable"
        numeric gross_area_marla
        numeric saleable_area_marla
        numeric reserved_area_marla
        numeric sold_area_marla
        boolean status
        datetime created_at "nullable"
        datetime updated_at "nullable"
        datetime deleted_at "nullable"
    }
    blocks {
        integer id PK
        integer project_id FK
        varchar name UK
        numeric total_area_marla
        numeric saleable_area_marla
        boolean status
        datetime created_at "nullable"
        datetime updated_at "nullable"
        datetime deleted_at "nullable"
    }
    plots {
        integer id PK
        integer project_id FK
        integer block_id FK
        varchar plot_number UK
        numeric size_marla
        varchar category
        numeric base_price
        numeric premium_amount
        numeric total_price
        varchar status
        integer version
        datetime created_at "nullable"
        datetime updated_at "nullable"
        datetime deleted_at "nullable"
    }
    plot_packages {
        integer id PK
        integer project_id FK
        varchar name UK
        numeric size_marla
        numeric booking_amount
        integer months
        numeric monthly_amount
        numeric month_12_balloon
        numeric month_24_balloon
        numeric month_36_balloon
        boolean status
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }
    users {
        integer id PK
        varchar name
        varchar email UK
        datetime email_verified_at "nullable"
        varchar password
        varchar remember_token "nullable"
        datetime created_at "nullable"
        datetime updated_at "nullable"
        varchar role
        varchar phone "nullable"
        boolean status
        varchar referral_code UK "nullable"
        varchar theme
        varchar father_name "nullable"
        varchar cnic UK "nullable"
        text address "nullable"
        integer referral_agent_id FK "nullable"
    }
    bookings {
        integer id PK
        varchar booking_number UK
        integer project_id FK
        integer plot_id FK "nullable"
        integer customer_id FK
        integer package_id "logical reference"
        integer agent_id FK "nullable"
        date booking_date
        numeric total_price
        numeric booking_amount
        numeric financed_amount
        varchar status
        integer version
        datetime created_at "nullable"
        datetime updated_at "nullable"
        datetime deleted_at "nullable"
        text management_notes "nullable"
    }
    installment_schedules {
        integer id PK
        integer booking_id FK
        integer installment_number UK
        date due_date
        numeric regular_amount
        numeric balloon_amount
        numeric total_due
        numeric paid_amount
        varchar status
        datetime created_at "nullable"
        datetime updated_at "nullable"
        datetime reminder_sent_at "nullable"
    }
    payments {
        integer id PK
        varchar receipt_number UK
        integer booking_id FK
        integer customer_id FK
        numeric amount
        varchar payment_method
        varchar transaction_reference "nullable"
        datetime payment_date
        varchar status
        integer verified_by FK "nullable"
        datetime verified_at "nullable"
        text verification_notes "nullable"
        datetime created_at "nullable"
        datetime updated_at "nullable"
        datetime deleted_at "nullable"
        integer installment_schedule_id FK "nullable"
        varchar proof_path "nullable"
        varchar proof_original_name "nullable"
    }
    referrals {
        integer id PK
        integer user_id FK,UK
        integer sponsor_id FK "nullable"
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }
    commission_rules {
        integer id PK
        integer level UK
        numeric percentage
        boolean status
        datetime created_at "nullable"
        datetime updated_at "nullable"
        integer package_id UK "logical reference, nullable"
    }
    commissions {
        integer id PK
        integer payment_id FK,UK
        integer booking_id FK
        integer beneficiary_id FK
        integer level UK
        numeric percentage
        numeric amount
        varchar status
        datetime created_at "nullable"
        datetime updated_at "nullable"
        integer commission_payout_id FK "nullable"
    }
    commission_payouts {
        integer id PK
        varchar payout_number UK
        integer agent_id FK
        numeric amount
        varchar payment_method
        varchar transaction_reference "nullable"
        text notes "nullable"
        integer paid_by FK "nullable"
        datetime paid_at
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }
    plot_allotments {
        integer id PK
        integer booking_id FK,UK
        integer plot_id FK,UK
        date allotment_date
        varchar allotment_number UK
        text notes "nullable"
        integer allotted_by FK "nullable"
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }

    projects ||--o{ blocks : contains
    projects ||--o{ plots : contains
    blocks ||--o{ plots : contains
    projects ||--o{ plot_packages : offers
    projects ||--o{ bookings : receives
    plots o|--o{ bookings : requested_for
    plot_packages ||..o{ bookings : package_id
    users ||--o{ bookings : customer
    users o|--o{ bookings : agent
    bookings ||--o{ installment_schedules : schedules
    bookings ||--o{ payments : receives
    users ||--o{ payments : customer
    users o|--o{ payments : verifies
    installment_schedules o|--o{ payments : allocated_to
    users ||--o| referrals : has
    users o|--o{ referrals : sponsors
    users o|--o{ users : referral_agent
    plot_packages o|..o{ commission_rules : package_id
    payments ||--o{ commissions : generates
    bookings ||--o{ commissions : awards
    users ||--o{ commissions : beneficiary
    commission_payouts o|--o{ commissions : groups
    users ||--o{ commission_payouts : agent
    users o|--o{ commission_payouts : paid_by
    bookings ||--o| plot_allotments : allotment
    plots ||--o| plot_allotments : assigned
    users o|--o{ plot_allotments : allotted_by
```

Dashed relationships are logical references present as columns but not enforced by SQLite foreign-key constraints.

## Authentication, permissions, logging, and framework tables

```mermaid
erDiagram
    permissions {
        integer id PK
        varchar name UK
        varchar guard_name UK
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }
    roles {
        integer id PK
        varchar name UK
        varchar guard_name UK
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }
    model_has_permissions {
        integer permission_id PK,FK
        varchar model_type PK
        integer model_id PK
    }
    model_has_roles {
        integer role_id PK,FK
        varchar model_type PK
        integer model_id PK
    }
    role_has_permissions {
        integer permission_id PK,FK
        integer role_id PK,FK
    }
    activity_log {
        integer id PK
        varchar log_name "nullable"
        text description
        varchar subject_type "nullable"
        integer subject_id "nullable"
        varchar causer_type "nullable"
        integer causer_id "nullable"
        text properties "nullable"
        datetime created_at "nullable"
        datetime updated_at "nullable"
        varchar event "nullable"
        varchar batch_uuid "nullable"
    }
    notifications {
        varchar id PK
        varchar type
        varchar notifiable_type
        integer notifiable_id
        text data
        datetime read_at "nullable"
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }
    password_reset_tokens {
        varchar email PK
        varchar token
        datetime created_at "nullable"
    }
    sessions {
        varchar id PK
        integer user_id "nullable"
        varchar ip_address "nullable"
        text user_agent "nullable"
        text payload
        integer last_activity
    }
    cache {
        varchar key PK
        text value
        integer expiration
    }
    cache_locks {
        varchar key PK
        varchar owner
        integer expiration
    }
    jobs {
        integer id PK
        varchar queue
        text payload
        integer attempts
        integer reserved_at "nullable"
        integer available_at
        integer created_at
    }
    job_batches {
        varchar id PK
        varchar name
        integer total_jobs
        integer pending_jobs
        integer failed_jobs
        text failed_job_ids
        text options "nullable"
        integer cancelled_at "nullable"
        integer created_at
        integer finished_at "nullable"
    }
    failed_jobs {
        integer id PK
        varchar uuid UK
        varchar connection
        varchar queue
        text payload
        text exception
        datetime failed_at
    }
    migrations {
        integer id PK
        varchar migration
        integer batch
    }

    permissions ||--o{ model_has_permissions : assigned
    roles ||--o{ model_has_roles : assigned
    permissions ||--o{ role_has_permissions : included
    roles ||--o{ role_has_permissions : includes
```

Polymorphic columns (`model_type`/`model_id`, `subject_type`/`subject_id`, `causer_type`/`causer_id`, and `notifiable_type`/`notifiable_id`) intentionally have no fixed foreign-key line. The `sessions.user_id` column also has no database-enforced foreign key.
