<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around the Google Cloud Text-to-Speech REST API.
 *
 * Uses a plain API key (config('services.google_tts.key'), env
 * GOOGLE_TTS_API_KEY) rather than a full service-account credential file —
 * simplest possible setup for a single server-side batch job. Get a key at:
 * https://console.cloud.google.com/apis/credentials (after enabling the
 * "Cloud Text-to-Speech API" on a project).
 */
class GoogleTextToSpeechService
{
    private const ENDPOINT = 'https://texttospeech.googleapis.com/v1/text:synthesize';

    public function __construct(
        private readonly ?string $apiKey = null,
    ) {
    }

    /**
     * Synthesize $text to MP3 bytes. Returns the raw binary audio content,
     * ready to be written straight to a .mp3 file.
     *
     * @param string $voiceName e.g. 'ja-JP-Wavenet-B' (male) or
     *                          'ja-JP-Wavenet-C' (female) — see
     *                          https://cloud.google.com/text-to-speech/docs/voices
     */
    public function synthesize(
        string $text,
        string $voiceName = 'ja-JP-Wavenet-B',
        string $languageCode = 'ja-JP',
        float $speakingRate = 0.92,
    ): string {
        $key = $this->apiKey ?? config('services.google_tts.key');

        if (! $key) {
            throw new RuntimeException(
                'GOOGLE_TTS_API_KEY is not set. Add it to .env — see README for how to get one.'
            );
        }

        $response = Http::timeout(30)->post(self::ENDPOINT.'?key='.$key, [
            'input' => ['text' => $text],
            'voice' => [
                'languageCode' => $languageCode,
                'name' => $voiceName,
            ],
            'audioConfig' => [
                'audioEncoding' => 'MP3',
                'speakingRate' => $speakingRate,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Google TTS request failed: '.$response->status().' '.$response->body()
            );
        }

        $audioContentBase64 = $response->json('audioContent');

        if (! $audioContentBase64) {
            throw new RuntimeException('Google TTS response had no audioContent: '.$response->body());
        }

        return base64_decode($audioContentBase64);
    }

    /**
     * Same as synthesize(), but the input is SSML (e.g. to add a <break/>
     * pause after a sentence). Used by the JLPT listening audio, where the
     * narrator leaves a short silence between the answer options.
     */
    public function synthesizeSsml(
        string $ssml,
        string $voiceName = 'ja-JP-Wavenet-B',
        string $languageCode = 'ja-JP',
        float $speakingRate = 0.92,
    ): string {
        $key = $this->apiKey ?? config('services.google_tts.key');

        if (! $key) {
            throw new RuntimeException(
                'GOOGLE_TTS_API_KEY is not set. Add it to .env — see docs/chokai-tts.md for how to get one.'
            );
        }

        $response = Http::timeout(30)->post(self::ENDPOINT.'?key='.$key, [
            'input' => ['ssml' => $ssml],
            'voice' => [
                'languageCode' => $languageCode,
                'name' => $voiceName,
            ],
            'audioConfig' => [
                'audioEncoding' => 'MP3',
                'speakingRate' => $speakingRate,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Google TTS request failed: '.$response->status().' '.$response->body()
            );
        }

        $audioContentBase64 = $response->json('audioContent');

        if (! $audioContentBase64) {
            throw new RuntimeException('Google TTS response had no audioContent: '.$response->body());
        }

        return base64_decode($audioContentBase64);
    }

    /**
     * Synthesize a multi-speaker dialogue into ONE MP3.
     *
     * Google TTS can only use one voice per request, so each turn is
     * synthesized separately with the voice mapped to its speaker, then the
     * MP3 byte streams are concatenated (MP3 frames can be joined as-is).
     *
     * @param array<int, array{speaker: string, text: string}> $turns
     *        speaker: 'male' | 'female' (also accepts 'm'/'f', 'M'/'F')
     * @param array{male?: string, female?: string} $voices voice name per speaker
     */
    public function synthesizeDialogue(
        array $turns,
        array $voices = [],
        string $languageCode = 'ja-JP',
        float $speakingRate = 0.92,
    ): string {
        $voices = $voices + [
            'male' => 'ja-JP-Wavenet-B',
            'female' => 'ja-JP-Wavenet-C',
        ];

        $audio = '';

        foreach ($turns as $i => $turn) {
            $text = trim($turn['text'] ?? '');

            if ($text === '') {
                continue;
            }

            $speaker = strtolower($turn['speaker'] ?? 'male');
            $gender = in_array($speaker, ['f', 'female', 'w', 'wanita'], true) ? 'female' : 'male';

            $audio .= $this->synthesize($text, $voices[$gender], $languageCode, $speakingRate);
        }

        if ($audio === '') {
            throw new RuntimeException('Dialogue has no non-empty turns.');
        }

        return $audio;
    }
}
