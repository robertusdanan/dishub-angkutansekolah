-- REKOMENDASI KEAMANAN: kolom baru untuk fitur 2FA (TOTP) superadmin.
-- Jalankan SEKALI lewat Supabase SQL Editor SEBELUM memakai fitur 2FA.
ALTER TABLE admin_accounts
  ADD COLUMN IF NOT EXISTS two_factor_secret text,
  ADD COLUMN IF NOT EXISTS two_factor_enabled_at timestamptz,
  ADD COLUMN IF NOT EXISTS two_factor_recovery_codes jsonb;

COMMENT ON COLUMN admin_accounts.two_factor_secret IS 'Secret TOTP base32 (rahasia)';
COMMENT ON COLUMN admin_accounts.two_factor_enabled_at IS 'NULL = 2FA belum aktif';
COMMENT ON COLUMN admin_accounts.two_factor_recovery_codes IS 'Array kode pemulihan (hashed bcrypt)';
