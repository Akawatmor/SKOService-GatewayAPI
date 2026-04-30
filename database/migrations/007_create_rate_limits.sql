CREATE TABLE IF NOT EXISTS rate_limits (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    api_service_id      INTEGER,
    role_id             INTEGER,
    requests_per_minute INTEGER NOT NULL DEFAULT 60,
    requests_per_day    INTEGER NOT NULL DEFAULT 1000,
    FOREIGN KEY (api_service_id) REFERENCES api_services(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);