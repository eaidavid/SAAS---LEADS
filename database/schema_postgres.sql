CREATE TABLE IF NOT EXISTS users (
  id BIGSERIAL PRIMARY KEY,
  company_id BIGINT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(40) NOT NULL DEFAULT 'seller',
  avatar VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS companies (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  cnpj VARCHAR(20) NULL,
  logo VARCHAR(255) NULL,
  colors JSONB NULL,
  address VARCHAR(255) NULL,
  plan VARCHAR(60) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS projects (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  niche VARCHAR(120) NULL,
  description TEXT NULL,
  archived_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS imports (
  id BIGSERIAL PRIMARY KEY,
  project_id BIGINT NOT NULL,
  name VARCHAR(180) NOT NULL,
  source_filename VARCHAR(255) NULL,
  total_rows INT NOT NULL DEFAULT 0,
  imported_rows INT NOT NULL DEFAULT 0,
  imported_at TIMESTAMP NULL,
  archived_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS leads (
  id BIGSERIAL PRIMARY KEY,
  company_id BIGINT NULL,
  project_id BIGINT NULL,
  import_id BIGINT NULL,
  place_id VARCHAR(180) NULL,
  name VARCHAR(180) NOT NULL,
  phone VARCHAR(40) NULL,
  mobile VARCHAR(40) NULL,
  email VARCHAR(180) NULL,
  website VARCHAR(255) NULL,
  address VARCHAR(255) NULL,
  city VARCHAR(120) NULL,
  state VARCHAR(80) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  category VARCHAR(120) NULL,
  google_maps_url VARCHAR(255) NULL,
  comments TEXT NULL,
  rating DECIMAL(3,2) NULL,
  reviews_count INT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'new',
  score INT NOT NULL DEFAULT 0,
  assigned_to BIGINT NULL,
  tags JSONB NULL,
  notes TEXT NULL,
  dedupe_key VARCHAR(64) NULL,
  imported_at TIMESTAMP NULL,
  archived_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS interactions (
  id BIGSERIAL PRIMARY KEY,
  lead_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  type VARCHAR(30) NOT NULL,
  message TEXT NULL,
  date TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS services (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  category VARCHAR(120) NULL,
  description TEXT NULL,
  benefits TEXT NULL,
  deliverables TEXT NULL,
  price_base DECIMAL(12,2) NULL,
  price_small DECIMAL(12,2) NULL,
  price_medium DECIMAL(12,2) NULL,
  price_large DECIMAL(12,2) NULL,
  deadline_days INT NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS proposals (
  id BIGSERIAL PRIMARY KEY,
  lead_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  title VARCHAR(180) NOT NULL,
  intro_message TEXT NULL,
  discount DECIMAL(12,2) NULL,
  total_value DECIMAL(12,2) NOT NULL DEFAULT 0,
  validity_days INT NOT NULL DEFAULT 15,
  status VARCHAR(40) NOT NULL DEFAULT 'draft',
  payment_terms TEXT NULL,
  viewed_at TIMESTAMP NULL,
  approved_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS proposal_items (
  id BIGSERIAL PRIMARY KEY,
  proposal_id BIGINT NOT NULL,
  service_id BIGINT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS contracts (
  id BIGSERIAL PRIMARY KEY,
  proposal_id BIGINT NOT NULL,
  lead_id BIGINT NOT NULL,
  template_id BIGINT NULL,
  content TEXT NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'draft',
  signed_by_client_at TIMESTAMP NULL,
  signed_by_company_at TIMESTAMP NULL,
  signature_client TEXT NULL,
  signature_company TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS templates (
  id BIGSERIAL PRIMARY KEY,
  type VARCHAR(40) NOT NULL,
  name VARCHAR(120) NOT NULL,
  content TEXT NOT NULL,
  variables JSONB NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL,
  type VARCHAR(40) NOT NULL,
  message TEXT NOT NULL,
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGSERIAL PRIMARY KEY,
  entity_type VARCHAR(40) NOT NULL,
  entity_id BIGINT NOT NULL,
  action VARCHAR(40) NOT NULL,
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS uniq_place_id ON leads (place_id);
CREATE UNIQUE INDEX IF NOT EXISTS uniq_project_dedupe ON leads (project_id, dedupe_key);
CREATE INDEX IF NOT EXISTS idx_imports_project ON imports (project_id);
CREATE INDEX IF NOT EXISTS idx_leads_company ON leads (company_id);
CREATE INDEX IF NOT EXISTS idx_leads_project ON leads (project_id);
CREATE INDEX IF NOT EXISTS idx_leads_import ON leads (import_id);
CREATE INDEX IF NOT EXISTS idx_leads_status ON leads (status);
CREATE INDEX IF NOT EXISTS idx_interactions_lead ON interactions (lead_id);
CREATE INDEX IF NOT EXISTS idx_proposals_lead ON proposals (lead_id);
CREATE INDEX IF NOT EXISTS idx_proposal_items ON proposal_items (proposal_id);
CREATE INDEX IF NOT EXISTS idx_audit_entity ON audit_logs (entity_type, entity_id);
