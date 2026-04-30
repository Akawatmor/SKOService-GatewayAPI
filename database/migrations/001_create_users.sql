CREATE TABLE IF NOT EXISTS users (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    username         TEXT NOT NULL UNIQUE,
    email            TEXT NOT NULL UNIQUE,
    password_hash    TEXT NOT NULL,
    role_id          INTEGER NOT NULL DEFAULT 2,
    api_key          TEXT UNIQUE,
    api_key_prefix   TEXT,
    refresh_token    TEXT,
    is_active        INTEGER NOT NULL DEFAULT 1,
    created_at       TEXT NOT NULL DEFAULT (datetime('now')),
    last_login_at    TEXT,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);