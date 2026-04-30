INSERT INTO roles (id, name, permissions) VALUES
    (1, 'guest', '["catalog.read"]'),
    (2, 'developer', '["catalog.read","access.request","console.use"]'),
    (3, 'admin', '["*"]');

INSERT INTO users (id, username, email, password_hash, role_id, api_key, api_key_prefix, is_active, created_at, last_login_at)
VALUES
    (1, 'admin', 'admin@gatewayapi.local', '$2y$12$ws5ynVFd1Ey9tCBA8zNtLOYRoOp0nG.453KVcMI0.I44RqPZ/8WUm', 3, '8c4dfa1987c7372c6f244cf53470f6f25207f46e116cd15d4d0f66ec91171057', 'sk_live_seed', 1, datetime('now'), datetime('now')),
    (2, 'developer', 'dev@gatewayapi.local', '$2y$12$uwNvANT0uEX42KGidpi64..oCmvxt9PYg9KS9HAeOQMvDK4fpiLje', 2, '035bd3a5638e586d4427f6ac7cf9e7c4452efdde2f464d8dc15fd8e6f897f577', 'sk_live_demo', 1, datetime('now'), datetime('now'));

INSERT INTO api_services (id, name, slug, description_th, description_en, mode, api_type, standard, status, base_url, schema_path, version, is_public, tags, created_by, created_at)
VALUES
    (1, 'Citizen Registry API', 'citizen-registry', 'REST API สำหรับค้นหาข้อมูลทะเบียนพื้นฐาน', 'REST API for public registry lookups.', 'proxy', 'REST', 'OAS', 'active', 'https://jsonplaceholder.typicode.com', 'citizen-registry.yaml', '1.0', 1, '["rest","public","registry"]', 1, datetime('now')),
    (2, 'Internal Analytics GraphQL', 'internal-analytics', 'GraphQL สำหรับข้อมูลเชิงลึกภายในองค์กร', 'Private analytics GraphQL gateway for internal dashboards.', 'proxy', 'GraphQL', 'GraphQL_SDL', 'experimental', 'https://countries.trevorblades.com', 'internal-analytics.graphql', '1.1', 0, '["graphql","private","analytics"]', 1, datetime('now')),
    (3, 'Open Data Snapshot', 'open-data-snapshot', 'ชุดข้อมูลแบบดาวน์โหลดเป็นไฟล์ SQLite', 'Downloadable SQLite snapshots for offline analysis.', 'catalog', 'File', 'none', 'active', NULL, NULL, '1.0', 1, '["file","snapshot","sqlite"]', 1, datetime('now'));

INSERT INTO api_endpoints (api_service_id, method, path, description_th, description_en, auth_required, request_schema, response_schema, example_request, example_response, is_active)
VALUES
    (1, 'GET', '/users', 'ดึงรายการผู้ใช้', 'Fetch all users', 0, '{}', '{"type":"array"}', '', '[{"id":1}]', 1),
    (1, 'GET', '/users/{id}', 'ดึงข้อมูลผู้ใช้รายบุคคล', 'Fetch a single user', 0, '{}', '{"type":"object"}', '', '{"id":1}', 1),
    (2, 'POST', '/', 'ส่ง GraphQL query', 'Execute GraphQL query', 1, '{"query":"{ countries { code name } }"}', '{"data":{}}', '{"query":"{ countries { code name } }"}', '{"data":{"countries":[]}}', 1),
    (3, 'GET', '/snapshot/latest', 'ดาวน์โหลด snapshot ล่าสุด', 'Download latest snapshot', 1, '{}', '{"type":"file"}', '', '', 1);

INSERT INTO access_grants (user_id, api_service_id, granted_by)
VALUES
    (2, 2, 1);

INSERT INTO access_requests (user_id, api_service_id, reason, status)
VALUES
    (2, 2, 'Need private analytics access for dashboard integration', 'approved');

INSERT INTO rate_limits (api_service_id, role_id, requests_per_minute, requests_per_day)
VALUES
    (NULL, 1, 10, 100),
    (NULL, 2, 60, 1000),
    (NULL, 3, 1000, 100000),
    (2, 2, 30, 500);