ALTER TABLE api_services ADD COLUMN health_check_method TEXT;
ALTER TABLE api_services ADD COLUMN health_check_path TEXT;
ALTER TABLE api_services ADD COLUMN last_health_status_code INTEGER;
ALTER TABLE api_services ADD COLUMN last_health_response_time_ms INTEGER;
ALTER TABLE api_services ADD COLUMN last_health_checked_at TEXT;

UPDATE api_services
SET health_check_method = 'GET',
    health_check_path = '/users/1'
WHERE slug = 'citizen-registry'
  AND health_check_path IS NULL;