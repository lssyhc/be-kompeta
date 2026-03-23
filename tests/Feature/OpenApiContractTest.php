<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class OpenApiContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<string, array<string, array<int, string>>>|null
     */
    private static ?array $docsOperations = null;

    /**
     * @var array<string, array<string, array{statuses: array<int, string>, requires_auth: bool}>>|null
     */
    private static ?array $docsResolvedOperations = null;

    public function test_every_api_route_has_matching_openapi_path_and_method(): void
    {
        $docs = $this->docsOperations();

        foreach ($this->apiRoutes() as $route) {
            $path = '/'.$route['path'];

            $this->assertArrayHasKey(
                $path,
                $docs,
                "OpenAPI path tidak ditemukan untuk route: {$route['method']} {$path}"
            );

            $this->assertArrayHasKey(
                strtolower($route['method']),
                $docs[$path],
                "OpenAPI method tidak ditemukan untuk route: {$route['method']} {$path}"
            );
        }
    }

    public function test_every_sanctum_route_documents_401_response(): void
    {
        $docs = $this->docsOperations();

        foreach ($this->apiRoutes() as $route) {
            if (! $route['auth']) {
                continue;
            }

            $path = '/'.$route['path'];
            $method = strtolower($route['method']);
            $statuses = $docs[$path][$method] ?? [];

            $this->assertContains(
                '401',
                $statuses,
                "Endpoint auth wajib mendokumentasikan 401: {$route['method']} {$path}"
            );
        }
    }

    public function test_every_openapi_operation_maps_to_real_route(): void
    {
        $routeOps = $this->routeOperationMap();
        $docs = $this->docsResolvedOperations();

        foreach ($docs as $path => $methods) {
            $this->assertArrayHasKey(
                ltrim($path, '/'),
                $routeOps,
                "OpenAPI path tidak punya route aktual: {$path}"
            );

            foreach (array_keys($methods) as $method) {
                $this->assertArrayHasKey(
                    strtolower($method),
                    $routeOps[ltrim($path, '/')],
                    'OpenAPI method tidak punya route aktual: '.strtoupper($method)." {$path}"
                );
            }
        }
    }

    public function test_security_requirement_in_openapi_matches_route_middleware(): void
    {
        $routeOps = $this->routeOperationMap();
        $docs = $this->docsResolvedOperations();

        foreach ($docs as $path => $methods) {
            $routePath = ltrim($path, '/');

            foreach ($methods as $method => $meta) {
                $this->assertSame(
                    $routeOps[$routePath][strtolower($method)],
                    $meta['requires_auth'],
                    'Mismatch auth requirement untuk '.strtoupper($method)." {$path}"
                );
            }
        }
    }

    public function test_every_openapi_operation_has_valid_responses(): void
    {
        $docs = $this->docsResolvedOperations();

        foreach ($docs as $path => $methods) {
            foreach ($methods as $method => $meta) {
                $statuses = $meta['statuses'];

                $this->assertNotEmpty(
                    $statuses,
                    'Operasi harus punya minimal satu response: '.strtoupper($method)." {$path}"
                );

                foreach ($statuses as $status) {
                    $this->assertMatchesRegularExpression(
                        '/^[1-5][0-9][0-9]$/',
                        $status,
                        'Status response tidak valid untuk '.strtoupper($method)." {$path}: {$status}"
                    );
                }
            }
        }
    }

    public function test_runtime_probes_match_documented_statuses_and_json_envelope(): void
    {
        $docs = $this->docsOperations();

        $probes = [
            ['method' => 'POST', 'uri' => '/api/auth/register', 'docPath' => '/auth/register', 'payload' => [], 'expect' => 422, 'json' => true],
            ['method' => 'POST', 'uri' => '/api/auth/login', 'docPath' => '/auth/login', 'payload' => [], 'expect' => 422, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/docs/openapi', 'docPath' => '/docs/openapi', 'payload' => [], 'expect' => 200, 'json' => false],

            ['method' => 'GET', 'uri' => '/api/public/explore/jobs', 'docPath' => '/public/explore/jobs', 'payload' => [], 'expect' => 200, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/public/explore/jobs/not-found-slug', 'docPath' => '/public/explore/jobs/{slug}', 'payload' => [], 'expect' => 404, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/public/explore/filters', 'docPath' => '/public/explore/filters', 'payload' => [], 'expect' => 200, 'json' => true],

            ['method' => 'GET', 'uri' => '/api/public/blog/content-types', 'docPath' => '/public/blog/content-types', 'payload' => [], 'expect' => 200, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/public/blog/articles', 'docPath' => '/public/blog/articles', 'payload' => [], 'expect' => 200, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/public/blog/articles/most-read', 'docPath' => '/public/blog/articles/most-read', 'payload' => [], 'expect' => 200, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/public/blog/articles/not-found-slug', 'docPath' => '/public/blog/articles/{slug}', 'payload' => [], 'expect' => 404, 'json' => true],

            ['method' => 'GET', 'uri' => '/api/public/for-you/jobs', 'docPath' => '/public/for-you/jobs', 'payload' => [], 'expect' => 200, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/public/for-you/jobs/not-found-slug', 'docPath' => '/public/for-you/jobs/{slug}', 'payload' => [], 'expect' => 404, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/public/for-you/sort-options', 'docPath' => '/public/for-you/sort-options', 'payload' => [], 'expect' => 200, 'json' => true],

            ['method' => 'GET', 'uri' => '/api/public/mitra', 'docPath' => '/public/mitra', 'payload' => [], 'expect' => 200, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/public/mitra/999999', 'docPath' => '/public/mitra/{id}', 'payload' => [], 'expect' => 404, 'json' => true],

            ['method' => 'GET', 'uri' => '/api/public/schools', 'docPath' => '/public/schools', 'payload' => [], 'expect' => 200, 'json' => true],
            ['method' => 'GET', 'uri' => '/api/public/schools/999999', 'docPath' => '/public/schools/{id}', 'payload' => [], 'expect' => 404, 'json' => true],
        ];

        foreach ($probes as $probe) {
            $response = $this->json($probe['method'], $probe['uri'], $probe['payload']);

            $response->assertStatus($probe['expect']);

            $docMethod = strtolower($probe['method']);
            $statuses = $docs[$probe['docPath']][$docMethod] ?? [];

            $this->assertContains(
                (string) $response->getStatusCode(),
                $statuses,
                "Status runtime tidak terdokumentasi: {$probe['method']} {$probe['docPath']} => {$response->getStatusCode()}"
            );

            if ($probe['json']) {
                $response->assertHeader('content-type');
                $response->assertJsonStructure([
                    'success',
                    'message',
                    'data',
                    'errors',
                    'meta',
                ]);
            } else {
                $this->assertStringContainsString('yaml', (string) $response->headers->get('content-type'));
            }
        }
    }

    public function test_runtime_protected_endpoints_return_401_when_unauthenticated_and_are_documented(): void
    {
        $docs = $this->docsOperations();

        $protectedProbes = [
            ['method' => 'POST', 'uri' => '/api/auth/logout', 'docPath' => '/auth/logout'],
            ['method' => 'GET', 'uri' => '/api/auth/me', 'docPath' => '/auth/me'],
            ['method' => 'GET', 'uri' => '/api/user', 'docPath' => '/user'],

            ['method' => 'GET', 'uri' => '/api/profile', 'docPath' => '/profile'],
            ['method' => 'PUT', 'uri' => '/api/profile', 'docPath' => '/profile'],

            ['method' => 'GET', 'uri' => '/api/school/students', 'docPath' => '/school/students'],
            ['method' => 'POST', 'uri' => '/api/school/students', 'docPath' => '/school/students'],
            ['method' => 'GET', 'uri' => '/api/school/students/search', 'docPath' => '/school/students/search'],
            ['method' => 'GET', 'uri' => '/api/school/students/1', 'docPath' => '/school/students/{id}'],
            ['method' => 'PUT', 'uri' => '/api/school/students/1', 'docPath' => '/school/students/{id}'],
            ['method' => 'DELETE', 'uri' => '/api/school/students/1', 'docPath' => '/school/students/{id}'],

            ['method' => 'POST', 'uri' => '/api/student/portfolio-items', 'docPath' => '/student/portfolio-items'],
            ['method' => 'GET', 'uri' => '/api/student/application-reminder', 'docPath' => '/student/application-reminder'],
            ['method' => 'GET', 'uri' => '/api/student/job-applications', 'docPath' => '/student/job-applications'],
            ['method' => 'POST', 'uri' => '/api/student/job-applications', 'docPath' => '/student/job-applications'],
            ['method' => 'GET', 'uri' => '/api/student/job-applications/1', 'docPath' => '/student/job-applications/{id}'],
            ['method' => 'PUT', 'uri' => '/api/student/job-applications/1', 'docPath' => '/student/job-applications/{id}'],
            ['method' => 'POST', 'uri' => '/api/student/job-applications/1/submit', 'docPath' => '/student/job-applications/{id}/submit'],

            ['method' => 'GET', 'uri' => '/api/partnership-proposals', 'docPath' => '/partnership-proposals'],
            ['method' => 'POST', 'uri' => '/api/partnership-proposals', 'docPath' => '/partnership-proposals'],
            ['method' => 'GET', 'uri' => '/api/partnership-proposals/1', 'docPath' => '/partnership-proposals/{id}'],
            ['method' => 'PUT', 'uri' => '/api/partnership-proposals/1', 'docPath' => '/partnership-proposals/{id}'],
            ['method' => 'POST', 'uri' => '/api/partnership-proposals/1/submit', 'docPath' => '/partnership-proposals/{id}/submit'],

            ['method' => 'GET', 'uri' => '/api/files/me/operational_license', 'docPath' => '/files/me/{type}'],

            ['method' => 'POST', 'uri' => '/api/admin/blog/articles', 'docPath' => '/admin/blog/articles'],
            ['method' => 'GET', 'uri' => '/api/admin/registrations', 'docPath' => '/admin/registrations'],
            ['method' => 'GET', 'uri' => '/api/admin/registrations/1', 'docPath' => '/admin/registrations/{id}'],
            ['method' => 'PATCH', 'uri' => '/api/admin/registrations/1/approve', 'docPath' => '/admin/registrations/{id}/approve'],
            ['method' => 'PATCH', 'uri' => '/api/admin/registrations/1/reject', 'docPath' => '/admin/registrations/{id}/reject'],
        ];

        foreach ($protectedProbes as $probe) {
            $response = $this->requestJson($probe['method'], $probe['uri'], []);
            $response->assertStatus(401);
            $response->assertJsonStructure([
                'success',
                'message',
                'data',
                'errors',
                'meta',
            ]);

            $docMethod = strtolower($probe['method']);
            $statuses = $docs[$probe['docPath']][$docMethod] ?? [];

            $this->assertContains(
                '401',
                $statuses,
                "Status 401 belum terdokumentasi: {$probe['method']} {$probe['docPath']}"
            );
        }
    }

    /**
     * @return array<int, array{method: string, path: string, auth: bool}>
     */
    private function apiRoutes(): array
    {
        Artisan::call('route:list', [
            '--path' => 'api',
            '--json' => true,
        ]);

        /** @var array<int, array{method: string, uri: string, middleware: array<int, string>|string}> $routes */
        $routes = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
        $normalized = [];

        foreach ($routes as $route) {
            $uri = (string) $route['uri'];

            if (! str_starts_with($uri, 'api/')) {
                continue;
            }

            $middlewares = is_array($route['middleware'])
                ? $route['middleware']
                : explode(',', (string) $route['middleware']);

            $hasSanctum = collect($middlewares)
                ->contains(fn ($m) => is_string($m) && str_contains($m, 'Authenticate:sanctum'));

            $methods = array_filter(
                explode('|', (string) $route['method']),
                fn (string $method): bool => $method !== 'HEAD'
            );

            foreach ($methods as $method) {
                $normalized[] = [
                    'method' => strtoupper($method),
                    'path' => substr($uri, 4),
                    'auth' => $hasSanctum,
                ];
            }
        }

        return $normalized;
    }

    /**
     * @return array<string, array<string, array<int, string>>>
     */
    private function docsOperations(): array
    {
        if (is_array(self::$docsOperations)) {
            return self::$docsOperations;
        }

        $openApiPath = public_path('docs/swagger/openapi.yaml');
        $lines = file($openApiPath, FILE_IGNORE_NEW_LINES);

        if (! is_array($lines)) {
            $this->fail('Gagal membaca openapi.yaml');
        }

        $pathRefs = [];
        $inPaths = false;
        $currentPath = null;

        foreach ($lines as $line) {
            if (preg_match('/^paths:\\s*$/', $line) === 1) {
                $inPaths = true;

                continue;
            }

            if ($inPaths && preg_match('/^components:\\s*$/', $line) === 1) {
                break;
            }

            if (! $inPaths) {
                continue;
            }

            if (preg_match('/^  (\\/[^:]+):\\s*$/', $line, $matches) === 1) {
                $currentPath = $matches[1];

                continue;
            }

            if ($currentPath !== null && preg_match('/^    \\$ref:\\s+"([^"]+)"\\s*$/', $line, $matches) === 1) {
                $pathRefs[$currentPath] = $matches[1];
            }
        }

        $pathFileCache = [];
        $operations = [];

        foreach ($pathRefs as $openApiPathKey => $ref) {
            if (preg_match('/^\\.\\/paths\\/([^#]+)#\\/paths\\/(.+)$/', $ref, $matches) !== 1) {
                $this->fail("Format ref OpenAPI tidak dikenali: {$ref}");
            }

            $fileName = $matches[1];
            $pointerPath = str_replace(['~1', '~0'], ['/', '~'], $matches[2]);

            if (! str_starts_with($pointerPath, '/')) {
                $pointerPath = '/'.$pointerPath;
            }
            $filePath = public_path('docs/swagger/paths/'.$fileName);

            if (! isset($pathFileCache[$filePath])) {
                $pathFileCache[$filePath] = $this->parsePathFile($filePath);
            }

            $parsed = $pathFileCache[$filePath];

            if (! isset($parsed[$pointerPath])) {
                $this->fail("Path {$pointerPath} tidak ditemukan di file ref {$fileName}");
            }

            $operations[$openApiPathKey] = $parsed[$pointerPath];
        }

        self::$docsOperations = $operations;

        return $operations;
    }

    /**
     * @return array<string, array<string, array<int, string>>>
     */
    private function parsePathFile(string $filePath): array
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        if (! is_array($lines)) {
            $this->fail("Gagal membaca file path swagger: {$filePath}");
        }

        $result = [];
        $currentPath = null;
        $currentMethod = null;

        foreach ($lines as $line) {
            if (preg_match('/^  (\\/[^:]+):\\s*$/', $line, $matches) === 1) {
                $currentPath = $matches[1];
                $currentMethod = null;

                if (! isset($result[$currentPath])) {
                    $result[$currentPath] = [];
                }

                continue;
            }

            if ($currentPath !== null && preg_match('/^    (get|post|put|patch|delete):\\s*$/', $line, $matches) === 1) {
                $currentMethod = $matches[1];

                if (! isset($result[$currentPath][$currentMethod])) {
                    $result[$currentPath][$currentMethod] = [];
                }

                continue;
            }

            if ($currentPath !== null && $currentMethod !== null && preg_match('/^        "(\\d{3})":\\s*$/', $line, $matches) === 1) {
                $result[$currentPath][$currentMethod][] = $matches[1];
            }
        }

        return $result;
    }

    private function requestJson(string $method, string $uri, array $payload)
    {
        return match (strtoupper($method)) {
            'GET' => $this->getJson($uri),
            'POST' => $this->postJson($uri, $payload),
            'PUT' => $this->putJson($uri, $payload),
            'PATCH' => $this->patchJson($uri, $payload),
            'DELETE' => $this->deleteJson($uri, $payload),
            default => throw new \InvalidArgumentException("Unsupported method: {$method}"),
        };
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function routeOperationMap(): array
    {
        $map = [];

        foreach ($this->apiRoutes() as $route) {
            $path = $route['path'];
            $method = strtolower($route['method']);
            $map[$path][$method] = $route['auth'];
        }

        return $map;
    }

    /**
     * @return array<string, array<string, array{statuses: array<int, string>, requires_auth: bool}>>
     */
    private function docsResolvedOperations(): array
    {
        if (is_array(self::$docsResolvedOperations)) {
            return self::$docsResolvedOperations;
        }

        $openApi = Yaml::parseFile(public_path('docs/swagger/openapi.yaml'));

        if (! is_array($openApi)) {
            $this->fail('Gagal parse openapi.yaml');
        }

        $globalSecurity = $openApi['security'] ?? null;
        $globalRequiresAuth = is_array($globalSecurity) && count($globalSecurity) > 0;
        $paths = $openApi['paths'] ?? [];

        if (! is_array($paths)) {
            $this->fail('Bagian paths di openapi.yaml tidak valid.');
        }

        /** @var array<string, array<string, mixed>> $pathFileCache */
        $pathFileCache = [];
        $resolved = [];

        foreach ($paths as $path => $node) {
            if (! is_string($path) || ! is_array($node)) {
                continue;
            }

            $operationsNode = isset($node['$ref']) && is_string($node['$ref'])
                ? $this->resolvePathRef($node['$ref'], $pathFileCache)
                : $node;

            foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
                $operation = $operationsNode[$method] ?? null;

                if (! is_array($operation)) {
                    continue;
                }

                $responses = $operation['responses'] ?? [];
                $statuses = is_array($responses)
                    ? array_values(array_filter(array_map('strval', array_keys($responses))))
                    : [];

                $requiresAuth = $globalRequiresAuth;

                if (array_key_exists('security', $operation) && is_array($operation['security'])) {
                    $requiresAuth = $this->securityRequiresAuth($operation['security']);
                }

                $resolved[$path][$method] = [
                    'statuses' => $statuses,
                    'requires_auth' => $requiresAuth,
                ];
            }
        }

        self::$docsResolvedOperations = $resolved;

        return $resolved;
    }

    /**
     * @param  array<string, array<string, mixed>>  $pathFileCache
     * @return array<string, mixed>
     */
    private function resolvePathRef(string $ref, array &$pathFileCache): array
    {
        if (preg_match('/^\.\/paths\/([^#]+)#\/(.+)$/', $ref, $matches) !== 1) {
            $this->fail("Format ref OpenAPI tidak dikenali: {$ref}");
        }

        $fileName = $matches[1];
        $pointer = $matches[2];
        $filePath = public_path('docs/swagger/paths/'.$fileName);

        if (! isset($pathFileCache[$filePath])) {
            $parsed = Yaml::parseFile($filePath);

            if (! is_array($parsed)) {
                $this->fail("Gagal parse file path OpenAPI: {$fileName}");
            }

            $pathFileCache[$filePath] = $parsed;
        }

        $node = $pathFileCache[$filePath];

        foreach (explode('/', $pointer) as $segment) {
            $segment = str_replace(['~1', '~0'], ['/', '~'], $segment);

            if (! is_array($node) || ! array_key_exists($segment, $node)) {
                $this->fail("JSON pointer tidak valid untuk ref {$ref}");
            }

            $node = $node[$segment];
        }

        if (! is_array($node)) {
            $this->fail("Node operasi pada ref {$ref} tidak valid.");
        }

        return $node;
    }

    /**
     * Determine if the security requirement means auth is mandatory.
     *
     * In OpenAPI 3.0, `security: [{}, bearerAuth: []]` means auth is optional
     * because the empty object `{}` represents "no auth required".
     * Only when every entry requires a scheme is auth truly mandatory.
     *
     * @param  array<int, mixed>  $security
     */
    private function securityRequiresAuth(array $security): bool
    {
        if (count($security) === 0) {
            return false;
        }

        foreach ($security as $requirement) {
            if (is_array($requirement) && count($requirement) === 0) {
                return false;
            }
        }

        return true;
    }
}
