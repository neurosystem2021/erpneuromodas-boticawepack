-- DESTRUCTIVE: isolated test rollback only. Never auto-run on live support data.
DROP TABLE support_request_nonces;
DROP TABLE support_installation_keys;
DROP TABLE support_audit_logs;
DROP TABLE support_ticket_status_history;
DROP TABLE support_tickets;
DROP TABLE support_categories;
DROP TABLE support_memberships;
DROP TABLE support_principals;
DROP TABLE support_tenants;
DROP TABLE support_installations;