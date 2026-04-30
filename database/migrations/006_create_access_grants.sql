CREATE TABLE IF NOT EXISTS access_grants (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id        INTEGER NOT NULL,
    api_service_id INTEGER NOT NULL,
    granted_at     TEXT NOT NULL DEFAULT (datetime('now')),
    expires_at     TEXT,
    granted_by     INTEGER,
    UNIQUE(user_id, api_service_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (api_service_id) REFERENCES api_services(id),
    FOREIGN KEY (granted_by) REFERENCES users(id)
);