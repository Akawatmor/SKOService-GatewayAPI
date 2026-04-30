CREATE TABLE IF NOT EXISTS file_snapshots (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    api_service_id INTEGER NOT NULL,
    filename       TEXT NOT NULL,
    mime_type      TEXT NOT NULL DEFAULT 'application/octet-stream',
    storage_path   TEXT NOT NULL,
    size_bytes     INTEGER,
    generated_at   TEXT NOT NULL DEFAULT (datetime('now')),
    generated_by   INTEGER,
    FOREIGN KEY (api_service_id) REFERENCES api_services(id),
    FOREIGN KEY (generated_by) REFERENCES users(id)
);