<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Lomba;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DosenRecommendationService
{
    /**
     * Dapatkan rekomendasi Dosen Pembimbing berbasis AI untuk lomba yang diikuti tim.
     *
     * @param  Collection<int, Dosen>  $dosenList
     * @return array{
     *     map: array<string, array{skor: int, alasan: string, is_top: bool}>,
     *     top: array<int, array{nidn: string, nama: string, bidang_keahlian: string, sisa_kuota: int, skor: int, alasan: string}>,
     *     source: string
     * }
     */
    public function recommend(Lomba $lomba, Collection $dosenList): array
    {
        if ($dosenList->isEmpty()) {
            return [
                'map' => [],
                'top' => [],
                'source' => 'none',
            ];
        }

        $cacheKey = "ai_dosen_rec_v1_{$lomba->id_lomba}";

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($lomba, $dosenList) {
            $apiResult = $this->callGeminiApi($lomba, $dosenList);

            if (! empty($apiResult)) {
                return $this->formatRecommendations($apiResult, $dosenList, 'gemini_ai');
            }

            return $this->formatRecommendations($this->heuristicFallback($lomba, $dosenList), $dosenList, 'heuristic_rule');
        });
    }

    /**
     * Hapus cache rekomendasi AI secara terarah untuk seluruh lomba
     * tanpa melakukan Cache::flush() global.
     */
    public static function clearAllRecommendationsCache(): void
    {
        try {
            Lomba::pluck('id_lomba')->each(function ($id) {
                Cache::forget("ai_dosen_rec_v1_{$id}");
            });
        } catch (\Throwable $e) {
            Log::warning('Gagal membersihkan cache rekomendasi dosen: '.$e->getMessage());
        }
    }

    /**
     * Panggil Google Gemini API untuk menganalisis kecocokan bidang lomba dengan dosen.
     *
     * @param  Collection<int, Dosen>  $dosenList
     * @return array<int, array{nidn: string, skor: int, alasan: string}>
     */
    protected function callGeminiApi(Lomba $lomba, Collection $dosenList): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-flash-lite-latest');

        if (empty($apiKey)) {
            return [];
        }

        $candidates = $dosenList->map(function (Dosen $d) {
            return [
                'nidn' => (string) $d->nidn,
                'nama' => $d->nama,
                'keahlian' => $d->bidang_keahlian,
                'sisa_kuota' => $d->sisa_kuota,
            ];
        })->values()->toArray();

        $prompt = "Kamu adalah sistem rekomendasi cerdas pembimbing lomba Jurusan Teknologi Informasi Politeknik Negeri Malang (JTI Polinema).\n"
            ."Tugasmu menganalisis tingkat kecocokan kompetensi setiap dosen calon pembimbing dengan lomba yang akan diikuti mahasiswa.\n\n"
            ."DATA LOMBA:\n"
            ."- Judul: {$lomba->nama_lomba}\n"
            ."- Kategori: {$lomba->kategori}\n"
            ."- Deskripsi: {$lomba->deskripsi}\n\n"
            ."DAFTAR DOSEN CALON PEMBIMBING:\n"
            .json_encode($candidates, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n\n"
            ."PETUNJUK:\n"
            ."1. Beri skor kecocokan 40 - 99 untuk setiap dosen berdasarkan relevansi bidang keahlian dengan kategori/lingkup lomba.\n"
            ."2. Buat alasan singkat (1-2 kalimat) yang persuasif dan spesifik menjelaskan mengapa keahlian dosen tersebut mendukung keberhasilan karya di lomba ini.\n"
            ."3. Kembalikan HANYA JSON array murni tanpa markdown triple-backtick, berformat:\n"
            .'[{"nidn":"...","skor":95,"alasan":"..."}]';

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout(12)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.2,
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('Gemini API call failed for Dosen recommendation', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            $rawText = $response->json('candidates.0.content.parts.0.text');
            if (empty($rawText)) {
                return [];
            }

            // Bersihkan markdown wrap jika ada
            $cleanJson = trim($rawText);
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

            $decoded = json_decode($cleanJson, true);

            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            Log::error('Exception calling Gemini API for Dosen recommendation: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Fallback pencocokan berbasis kata kunci dan relevansi teks jika Gemini API berhalangan.
     *
     * @param  Collection<int, Dosen>  $dosenList
     * @return array<int, array{nidn: string, skor: int, alasan: string}>
     */
    protected function heuristicFallback(Lomba $lomba, Collection $dosenList): array
    {
        $context = strtolower($lomba->nama_lomba.' '.$lomba->kategori.' '.$lomba->deskripsi);

        $results = [];
        foreach ($dosenList as $dosen) {
            $keahlian = strtolower($dosen->bidang_keahlian ?? '');
            $words = array_filter(preg_split('/[\s,\/&]+/', $keahlian));

            $matches = 0;
            $matchedWords = [];
            foreach ($words as $word) {
                if (strlen($word) >= 3 && str_contains($context, $word)) {
                    $matches++;
                    $matchedWords[] = $word;
                }
            }

            $baseScore = 65;
            $bonus = min(30, $matches * 10);
            $skor = min(95, $baseScore + $bonus);

            if (! empty($matchedWords)) {
                $alasan = "Bidang keahlian {$dosen->bidang_keahlian} selaras dengan topik kompetisi {$lomba->kategori}.";
            } else {
                $alasan = "Keahlian di bidang {$dosen->bidang_keahlian} dapat memberikan wawasan teknis pendukung untuk karya tim.";
            }

            $results[] = [
                'nidn' => (string) $dosen->nidn,
                'skor' => $skor,
                'alasan' => $alasan,
            ];
        }

        return $results;
    }

    /**
     * Format dan urutkan rekomendasi untuk kebutuhan tampilan blade.
     *
     * @param  array<int, mixed>  $rawItems
     * @param  Collection<int, Dosen>  $dosenList
     * @return array{
     *     map: array<string, array{skor: int, alasan: string, is_top: bool}>,
     *     top: array<int, array{nidn: string, nama: string, bidang_keahlian: string, sisa_kuota: int, skor: int, alasan: string}>,
     *     source: string
     * }
     */
    protected function formatRecommendations(array $rawItems, Collection $dosenList, string $source): array
    {
        $dosenMap = $dosenList->keyBy(function (Dosen $d) {
            return (string) $d->nidn;
        });

        // Urutkan berdasarkan skor tertinggi
        usort($rawItems, function ($a, $b) {
            return ($b['skor'] ?? 0) <=> ($a['skor'] ?? 0);
        });

        $map = [];
        $top = [];
        $rank = 0;

        foreach ($rawItems as $item) {
            $nidn = (string) ($item['nidn'] ?? '');
            if (! isset($dosenMap[$nidn])) {
                continue;
            }

            $dosen = $dosenMap[$nidn];
            $skor = (int) ($item['skor'] ?? 70);
            $alasan = (string) ($item['alasan'] ?? 'Relevan dengan kategori lomba tim.');
            $isTop = ($rank < 3) && ($dosen->sisa_kuota > 0);

            $map[$nidn] = [
                'skor' => $skor,
                'alasan' => $alasan,
                'is_top' => $isTop,
            ];

            if ($isTop) {
                $top[] = [
                    'nidn' => $nidn,
                    'nama' => $dosen->nama,
                    'bidang_keahlian' => $dosen->bidang_keahlian,
                    'sisa_kuota' => $dosen->sisa_kuota,
                    'skor' => $skor,
                    'alasan' => $alasan,
                ];
            }

            $rank++;
        }

        return [
            'map' => $map,
            'top' => $top,
            'source' => $source,
        ];
    }
}
