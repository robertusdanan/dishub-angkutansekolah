<?php

namespace App\Services\Admin;

/**
 * REKOMENDASI KEAMANAN - implementasi TOTP (RFC 6238) murni PHP, kompatibel
 * Google Authenticator/Authy. Ditulis sendiri (bukan composer package)
 * supaya tidak ada risiko resolve dependency baru.
 */
class TotpService
{
    protected const DIGITS = 6;
    protected const PERIOD = 30;
    protected const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    public function provisioningUri(string $secret, string $accountLabel, string $issuer = 'Dishub Tulungagung Admin'): string
    {
        $label = rawurlencode($issuer.':'.$accountLabel);
        return 'otpauth://totp/'.$label.'?'.http_build_query([
            'secret' => $secret, 'issuer' => $issuer, 'algorithm' => 'SHA1',
            'digits' => self::DIGITS, 'period' => self::PERIOD,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = trim($code);
        if (!preg_match('/^\d{6}$/', $code)) return false;
        $timestamp = (int) floor(time() / self::PERIOD);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->generateCode($secret, $timestamp + $i), $code)) return true;
        }
        return false;
    }

    protected function generateCode(string $secret, int $timestamp): string
    {
        $key = $this->base32Decode($secret);
        $time = pack('N*', 0).pack('N*', $timestamp);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0xf;
        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);
        return str_pad((string) ($binary % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    protected function base32Encode(string $data): string
    {
        $binaryString = '';
        foreach (str_split($data) as $char) {
            $binaryString .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $result = '';
        foreach (str_split($binaryString, 5) as $chunk) {
            if (strlen($chunk) < 5) $chunk = str_pad($chunk, 5, '0');
            $result .= self::BASE32_ALPHABET[bindec($chunk)];
        }
        return $result;
    }

    protected function base32Decode(string $secret): string
    {
        $secret = strtoupper((string) preg_replace('/[^A-Z2-7]/i', '', $secret));
        $binaryString = '';
        foreach (str_split($secret) as $char) {
            $pos = strpos(self::BASE32_ALPHABET, $char);
            if ($pos === false) continue;
            $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($binaryString, 8) as $chunk) {
            if (strlen($chunk) === 8) $bytes .= chr(bindec($chunk));
        }
        return $bytes;
    }

    /** @return string[] */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(6)), 0, 10));
        }
        return $codes;
    }
}
