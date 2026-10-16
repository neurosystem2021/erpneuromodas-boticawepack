ALTER TABLE support_notifications ADD INDEX recipient_unread(recipient_id,read_at,id);
ALTER TABLE support_tickets ADD INDEX team_queue(tenant_id,technician_id,status,id);
