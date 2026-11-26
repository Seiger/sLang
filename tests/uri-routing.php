<?php

declare(strict_types=1);

use Seiger\sLang\sLang;

define('EVO_BASE_PATH', slang_path(''));
define('EVO_SITE_URL', 'https://example.test/');

require slang_path('src/sLang.php');

function __(string $key): string
{
    return $key === 'sLang::global.slang' ? 'Multilingual' : $key;
}

function csrf_token(): string
{
    slang_assert(!empty($_SESSION['mgrValidated']), 'Read the module token only in a validated manager session.');
    return $_SESSION['_token'];
}

final class SlangRoutingStringStub
{
    public static function lower(string $value): string
    {
        return strtolower($value);
    }
}

class_alias(SlangRoutingStringStub::class, 'Illuminate\Support\Str');

final class SlangRoutingFixture extends sLang
{
    public array $resolvedUris = [];

    public function __construct(private bool $showDefault = false)
    {
    }

    protected function getConfigValue($name): string
    {
        return match ($name) {
            's_lang_default' => 'uk',
            's_lang_default_show' => $this->showDefault ? '1' : '0',
            's_lang_config' => 'uk,en,fr',
            's_lang_front' => 'uk,en',
            's_lang_url_map' => '{"en":"english-eu","fr":"french-preview"}',
            default => '',
        };
    }

    public function resolveLocalizedIdentifier(string $requestUri): ?int
    {
        $this->resolvedUris[] = $requestUri;

        return null;
    }
}

final class SlangConfigSettingStub
{
    public static string $value = '';

    public static function query(): self
    {
        return new self();
    }

    public function where(string $field, string $value): self
    {
        slang_assert($field === 'setting_name' && $value === 's_lang_tvs', 'Expected the language TVs setting.');
        return $this;
    }

    public function first(): object
    {
        return (object) ['setting_value' => self::$value];
    }
}

class_alias(SlangConfigSettingStub::class, 'EvolutionCMS\Models\SystemSetting');

function slang_with_routing_fixture(Closure $test, bool $showDefault = false): void
{
    slang_docformsave_boot();
    $globals = [$_SERVER, $_REQUEST, $_GET];
    $evo = evo();
    $manager = $evo->managerId;
    $config = $evo->config;
    $locale = $evo->locale;
    $evo->managerId = 0;
    $evo->config = ['base_url' => '/', 'site_start' => 1];
    $routing = new SlangRoutingFixture($showDefault);
    SlangDocFormSaveLangStub::$routing = $routing;
    SlangDocFormSaveStub::$langConfig = $routing->langConfig();
    $_REQUEST = $_GET = [];

    try {
        $test($routing, $evo);
    } finally {
        [$_SERVER, $_REQUEST, $_GET] = $globals;
        SlangDocFormSaveLangStub::$routing = null;
        SlangDocFormSaveStub::$langConfig = ['uk', 'en'];
        $evo->managerId = $manager;
        $evo->config = $config;
        $evo->locale = $locale;
    }
}

function slang_expect_route_response(string $message, int $code): void
{
    try {
        SlangDocFormSaveStub::fire('evolution.OnPageNotFound', []);
    } catch (RuntimeException $signal) {
        slang_assert($signal->getMessage() === $message && $signal->getCode() === $code, 'Unexpected route response.');
        return;
    }

    throw new RuntimeException('Expected a route response.');
}

slang_group('uri-routing', function (): void {
    slang_test('frontend TV settings are cached until invalidated; manager reads stay fresh', function (): void {
        slang_with_routing_fixture(function ($routing, $evo): void {
            $settings = (new ReflectionClass(sLang::class))->newInstanceWithoutConstructor();
            $frontend = $evo->frontend;
            $value = SlangConfigSettingStub::$value;

            try {
                $evo->frontend = true;
                SlangConfigSettingStub::$value = '1';
                slang_assert($settings->langTvs() === ['1'], 'Read the initial frontend setting.');

                SlangConfigSettingStub::$value = '2';
                slang_assert($settings->langTvs() === ['1'], 'Reuse the setting within the frontend request.');

                $settings->forgetLanguageSetting('s_lang_tvs');
                slang_assert($settings->langTvs() === ['2'], 'Read the saved value after invalidation.');

                $evo->frontend = false;
                SlangConfigSettingStub::$value = '3';
                slang_assert($settings->langTvs() === ['3'], 'Read the manager setting without the frontend cache.');
                SlangConfigSettingStub::$value = '4';
                slang_assert($settings->langTvs() === ['4'], 'Keep subsequent manager reads fresh.');
            } finally {
                $evo->frontend = $frontend;
                SlangConfigSettingStub::$value = $value;
            }
        });
    });

    slang_test('module URL includes the encoded CSRF token only for a validated manager', function (): void {
        $routing = new SlangRoutingFixture();
        $session = $_SESSION ?? null;
        $moduleId = md5('Multilingual');

        try {
            $_SESSION = ['_token' => 'a+b/c=d&e'];
            slang_assert($routing->moduleUrl() === 'index.php?a=112&id=' . $moduleId, 'A guest URL must omit the token.');

            $_SESSION['mgrValidated'] = true;
            slang_assert(
                $routing->moduleUrl() === 'index.php?a=112&_token=a%2Bb%2Fc%3Dd%26e&id=' . $moduleId,
                'A manager URL must include the session token and preserve the module ID.'
            );
        } finally {
            $_SESSION = $session;
        }
    });

    slang_test('language prefixes match one literal beginning segment', function (): void {
        $routing = new SlangRoutingFixture();
        foreach ([
            ['/en/guide/en', 'en', '/guide/en'],
            ['/en.eu/guide//en.eu?ref=/en.eu#notes', 'en.eu', '/guide//en.eu?ref=/en.eu#notes'],
            ['/en-eu?ref=/guide#notes', 'en-eu', '/?ref=/guide#notes'],
            ['/en-eu#notes', 'en-eu', '/#notes'],
            ['/en-eu', 'en-eu', '/'],
            ['/enx/guide', 'en', '/enx/guide'],
            ['/guide/en', 'en', '/guide/en'],
            ['/enxeu/guide', 'en.eu', '/enxeu/guide'],
            ['/en/guide?q=en', 'en/guide', '/en/guide?q=en'],
        ] as [$uri, $segment, $expected]) {
            slang_assert($routing->stripLeadingUriSegment($uri, $segment) === $expected, 'Unexpected result for a literal locale prefix.');
        }
        $longSegment = str_repeat('a', 40000);
        slang_assert($routing->stripLeadingUriSegment('/' . $longSegment . '/guide', $longSegment) === '/guide', 'Long literal segments should not depend on a regex pattern limit.');
        slang_assert($routing->stripLanguageSegmentFromUri('/english-eu/docs//english-eu?ref=/docs#notes', 'en') === '/docs/english-eu?ref=/docs#notes', 'The existing locale helper should preserve its slash normalization.');
    });

    slang_test('fallback routing strips the resolved segment and preserves independent URI parts', function (): void {
        slang_with_routing_fixture(function (SlangRoutingFixture $routing, SlangDocFormSaveEvoStub $evo): void {
            $uri = '/english.eu/guide/english.eu?next=/english.eu/page#notes';
            $_SERVER['REQUEST_URI'] = $uri;
            SlangDocFormSaveStub::fire('evolution.OnPageNotFound', []);
            slang_assert($evo->locale === 'en', 'The literal segment must use the configured locale mapping.');
            slang_assert($_SERVER['REQUEST_URI'] === '/guide/english.eu?next=/english.eu/page#notes', 'Only the leading segment may be removed.');
            slang_assert($routing->resolvedUris === [$uri], 'The localized alias resolver must keep receiving the original URI.');
        });
    });

    slang_test('locale root routing uses the path and respects default language and manager access', function (): void {
        slang_with_routing_fixture(function (SlangRoutingFixture $routing, SlangDocFormSaveEvoStub $evo): void {
            $_SERVER['REQUEST_URI'] = '/english-eu?next=/guide#notes';
            SlangDocFormSaveStub::fire('evolution.OnLoadSettings', []);
            slang_assert($evo->locale === 'en', 'The root locale must be read from the path without the query or fragment.');
            slang_expect_route_response('Forward', 1);
            slang_assert($_SERVER['REQUEST_URI'] === '/?next=/guide#notes', 'The locale home page must keep its query and fragment.');

            $_SERVER['REQUEST_URI'] = '/uk?next=/guide';
            slang_expect_route_response('Not found', 404);
            slang_assert($_SERVER['REQUEST_URI'] === '/uk?next=/guide', 'The hidden default prefix must not be stripped.');

            $_SERVER['REQUEST_URI'] = '/french-preview/guide';
            $_REQUEST['q'] = $_GET['q'] = 'french-preview/guide';
            SlangDocFormSaveStub::fire('evolution.OnPageNotFound', []);
            slang_assert($evo->locale === 'uk' && $_SERVER['REQUEST_URI'] === '/french-preview/guide'
                && $_REQUEST['q'] === 'french-preview/guide', 'A guest must not get the manager-only locale rewrite.');
            $evo->managerId = 12;
            SlangDocFormSaveStub::fire('evolution.OnPageNotFound', []);
            slang_assert($evo->locale === 'fr' && $_SERVER['REQUEST_URI'] === '/guide'
                && $_REQUEST['q'] === 'guide' && $_GET['q'] === 'guide', 'A manager can use the configured preview locale.');
        });

        slang_with_routing_fixture(function (): void {
            $_SERVER['REQUEST_URI'] = '/uk?next=/guide';
            slang_expect_route_response('Forward', 1);
            slang_assert($_SERVER['REQUEST_URI'] === '/?next=/guide', 'An enabled default prefix must follow the same stripping rules.');
        }, showDefault: true);
    });
});
