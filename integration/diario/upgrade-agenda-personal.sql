-- Preserve existing reminders. Quick notes created by the agenda use Recordar=0.
ALTER TABLE Rhu_DiarioPersonal ADD COLUMN IF NOT EXISTS Recordar TINYINT(1) NOT NULL DEFAULT 1;
