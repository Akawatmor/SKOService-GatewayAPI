CREATE TABLE IF NOT EXISTS request_logs (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    api_service_id   INTEGER,
    endpoint_id      INTEGER,
    user_id          INTEGER,
    api_key_prefix   TEXT,
    method           TEXT,
    path             TEXT,
    ip_address       TEXT,
    status_code      INTEGER,
    response_time_ms INTEGER,
    created_at       TEXT NOT NULL DEFAULT (datetime('now'))
);