CREATE TABLE IF NOT EXISTS api_endpoints (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    api_service_id   INTEGER NOT NULL,
    method           TEXT,
    path             TEXT NOT NULL,
    description_th   TEXT,
    description_en   TEXT,
    auth_required    INTEGER NOT NULL DEFAULT 0,
    request_schema   TEXT,
    response_schema  TEXT,
    example_request  TEXT,
    example_response TEXT,
    is_active        INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (api_service_id) REFERENCES api_services(id) ON DELETE CASCADE
);