-- Run this in phpMyAdmin to add admin columns to existing requests table
USE aum_task_system;

ALTER TABLE requests
    ADD COLUMN IF NOT EXISTS assigned_to  VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS admin_notes  TEXT        DEFAULT NULL;
