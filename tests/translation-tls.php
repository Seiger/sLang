<?php

declare(strict_types=1);

namespace Seiger\sLang;

use RuntimeException;

// The transport doubles can run even on a PHP CLI without the cURL extension.
foreach ([
    'CURLOPT_RETURNTRANSFER', 'CURLOPT_ENCODING', 'CURLOPT_SSL_VERIFYPEER', 'CURLOPT_SSL_VERIFYHOST',
    'CURLOPT_FOLLOWLOCATION', 'CURLOPT_CONNECTTIMEOUT', 'CURLOPT_TIMEOUT', 'CURLOPT_USERAGENT',
    'CURLOPT_POST', 'CURLOPT_POSTFIELDS', 'CURLINFO_RESPONSE_CODE',
] as $index => $constant) {
    if (!defined($constant)) {
        define($constant, $index + 1);
    }
}

final class TranslationCurlStub
{
    public static array $responses = [];
    public static array $requests = [];

    public static function reset(array $responses = []): void
    {
        self::$responses = $responses;
        self::$requests = [];
    }
}

function curl_init(string $url): object
{
    $response = array_shift(TranslationCurlStub::$responses);
    if ($response === null) {
        throw new RuntimeException('Unexpected Google translation request.');
    }

    return (object) ['url' => $url, 'response' => $response, 'options' => []];
}

function curl_setopt_array(object $handle, array $options): bool
{
    $handle->options = $options;

    return true;
}

function curl_exec(object $handle): string|false
{
    TranslationCurlStub::$requests[] = $handle;

    return $handle->response['body'];
}

function curl_getinfo(object $handle, int $option): int
{
    slang_assert($option === CURLINFO_RESPONSE_CODE, 'Inspect the HTTP response status.');

    return $handle->response['status'];
}

final class TranslationTlsFixture extends sLang
{
    public function __construct()
    {
    }
}

function slang_assert_translation_tls(object $request, string $host): void
{
    slang_assert(parse_url($request->url, PHP_URL_SCHEME) === 'https' && parse_url($request->url, PHP_URL_HOST) === $host, 'Use the expected HTTPS endpoint.');
    slang_assert(($request->options[CURLOPT_SSL_VERIFYPEER] ?? null) === true, 'Verify the server certificate chain.');
    slang_assert(($request->options[CURLOPT_SSL_VERIFYHOST] ?? null) === 2, 'Verify the certificate hostname.');
    slang_assert(($request->options[CURLOPT_FOLLOWLOCATION] ?? null) === false, 'Keep the request on the configured endpoint.');
    slang_assert(($request->options[CURLOPT_CONNECTTIMEOUT] ?? null) === 5 && ($request->options[CURLOPT_TIMEOUT] ?? null) === 8, 'Preserve the translation timeouts.');
}

slang_group('translation-tls', function (): void {
    slang_test('the primary translation GET verifies the certificate and hostname', function (): void {
        TranslationCurlStub::reset([['status' => 200, 'body' => '[[["first. "],["second."]]]']]);
        $translator = new TranslationTlsFixture();

        slang_assert($translator->getAutomaticTranslate('джерело', 'uk', 'en') === 'first. second.', 'Join the primary translation sentences.');
        slang_assert(count(TranslationCurlStub::$requests) === 1, 'A successful primary request must not need a fallback.');
        $request = TranslationCurlStub::$requests[0];
        slang_assert_translation_tls($request, 'translate.googleapis.com');
        slang_assert(empty($request->options[CURLOPT_POST]), 'The primary endpoint still uses GET.');
    });

    slang_test('the legacy fallback verifies TLS and keeps its POST form after a transport failure', function (): void {
        TranslationCurlStub::reset([
            ['status' => 0, 'body' => false],
            ['status' => 200, 'body' => '{"sentences":[{"trans":"translated"}]}'],
        ]);
        $text = 'джерело & текст';
        $translator = new TranslationTlsFixture();

        slang_assert($translator->getAutomaticTranslate($text, 'uk', 'en') === 'translated', 'A successful verified fallback can supply a translation.');
        slang_assert(count(TranslationCurlStub::$requests) === 2, 'The transport failure should reach the fallback once.');
        [$primary, $fallback] = TranslationCurlStub::$requests;
        slang_assert_translation_tls($primary, 'translate.googleapis.com');
        slang_assert_translation_tls($fallback, 'translate.google.com');
        slang_assert(($fallback->options[CURLOPT_POST] ?? null) === true, 'Use POST for the legacy fallback.');
        parse_str($fallback->options[CURLOPT_POSTFIELDS], $form);
        slang_assert($form === ['sl' => 'uk', 'tl' => 'en', 'q' => $text], 'Preserve the legacy POST fields.');
    });

    slang_test('translation failures yield no text and matching languages make no requests', function (): void {
        $translator = new TranslationTlsFixture();
        TranslationCurlStub::reset([
            ['status' => 503, 'body' => '[[["service unavailable"]]]'],
            ['status' => 200, 'body' => '{"sentences":[{"trans":["not text"]}]}'],
        ]);
        slang_assert($translator->getAutomaticTranslate('джерело', 'uk', 'en') === '', 'Reject non-success HTTP responses and malformed translations.');

        TranslationCurlStub::reset([
            ['status' => 200, 'body' => 'invalid JSON'],
            ['status' => 0, 'body' => false],
        ]);
        slang_assert($translator->getAutomaticTranslate('джерело', 'uk', 'en') === '', 'Reject invalid JSON and unsuccessful transport responses.');

        TranslationCurlStub::reset();
        slang_assert($translator->getAutomaticTranslate('джерело', 'uk', 'uk') === 'джерело'
            && TranslationCurlStub::$requests === [], 'The original text should be returned without a request.');
    });
});
