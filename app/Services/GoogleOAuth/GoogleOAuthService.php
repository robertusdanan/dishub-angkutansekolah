<?php

namespace App\Services\GoogleOAuth;

use Illuminate\Support\Facades\Http;

/**
 * Port dari core/google_oauth_helper.php (google_oauth_exchange_and_fetch_user()).
 *
 * Beda dengan kode lama: di sana pakai die() kalau gagal di tengah proses.
 * Di Laravel kita lempar GoogleOAuthException supaya controller pemanggil
 * bisa menangani (redirect + flash error) alih-alih mematikan proses mentah.
 */
class GoogleOAuthService
{
    /**
     * @return array{email:string,name:?string,picture:?string}
     *
     * @throws GoogleOAuthException
     */
    public function exchangeCodeAndFetchUser(string $code, string $clientId, string $clientSecret, string $redirectUri): array
    {
        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        $tokenData = $tokenResponse->json();

        if (empty($tokenData['access_token'])) {
            throw new GoogleOAuthException('Tidak bisa mendapatkan Access Token dari Google.');
        }

        $userResponse = Http::withToken($tokenData['access_token'])
            ->get('https://www.googleapis.com/oauth2/v2/userinfo');

        $userData = $userResponse->json();

        if (empty($userData['email'])) {
            throw new GoogleOAuthException('Tidak bisa mendapatkan data email user.');
        }

        return [
            'email' => $userData['email'],
            'name' => $userData['name'] ?? '',
            'picture' => $userData['picture'] ?? '',
        ];
    }
}
