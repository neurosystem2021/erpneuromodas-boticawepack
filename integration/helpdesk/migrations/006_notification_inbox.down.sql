ALTER TABLE support_tickets DROP INDEX team_queue;
ALTER TABLE support_notifications ADD INDEX recipient_id(recipient_id);
ALTER TABLE support_notifications DROP INDEX recipient_unread;
