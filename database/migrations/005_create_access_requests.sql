CREATE TABLE IF NOT EXISTS access_requests (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id        INTEGER NOT NULL,
    api_service_id INTEGER NOT NULL,
    reason         TEXT,
    status         TEXT NOT NULL DEFAULT 'pending',
    requested_at   TEXT NOT NULL DEFAULT (datetime('now')),
    reviewed_by    INTEGER,
    reviewed_at    TEXT,
    reviewer_note  TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (api_service_id) REFERENCES api_services(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);