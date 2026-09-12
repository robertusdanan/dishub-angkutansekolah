<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Port dari pages/absenqrcode/php/proxy.php - proxy CORS generik ke
 * Supabase REST/Storage pakai anon key (RLS Supabase yang jadi pagar
 * keamanan sesungguhnya, bukan proxy ini). BEDA dari
 * ReferenceCacheProxyController: yang itu whitelist tabel + caching,
 * yang ini full pass-through tanpa cache, tanpa whitelist tabel - hanya
 * dibatasi prefix path /rest/v1/ atau /storage/v1/, sama seperti kode lama.
 */
class SupabaseGenericProxyController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 204)->withHeaders($this->corsHeaders());
        }

        $sbPath = (string) $request->query('path', '');
        if ($sbPath === '') {
            return response()->json(['error' => 'Parameter path wajib diisi'], 400)->withHeaders($this->corsHeaders());
        }
        if (!str_starts_with($sbPath, '/rest/v1/') && !str_starts_with($sbPath, '/storage/v1/')) {
            return response()->json(['error' => 'Hanya path /rest/v1/ atau /storage/v1/ yang diizinkan'], 403)->withHeaders($this->corsHeaders());
        }

        $query = $request->query();
        unset($query['path']);
        $qs = http_build_query($query);

        $sbBase = rtrim((string) config('services.supabase.url'), '/');
        $sbAnon = (string) config('services.supabase.anon_key');
        $url = $sbBase.$sbPath.($qs ? '?'.$qs : '');

        $method = $request->method();
        $body = in_array($method, ['POST', 'PATCH', 'PUT'], true) ? $request->getContent() : null;

        $authHeader = $request->header('Authorization') ?? $request->header('X-Authorization') ?? ('Bearer '.$sbAnon);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' => $sbAnon,
                'Authorization' => $authHeader,
            ])->timeout(15)->send($method, $url, $body !== null ? ['body' => $body] : []);

            return response($response->body(), $response->status())
                ->header('Content-Type', 'application/json')
                ->withHeaders($this->corsHeaders());
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Proxy gagal konek ke Supabase', 'detail' => $e->getMessage()], 502)
                ->withHeaders($this->corsHeaders());
        }
    }

    protected function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
    }
}
