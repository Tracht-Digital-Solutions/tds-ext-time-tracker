<?php
declare(strict_types=1);

namespace Tds\Ext\TimeTracker\Tests;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tds\Ext\TimeTracker\TimeTrackerModule;
use Tds\Frontend\Contract\UserContext;

/** Configurable UserContext double. */
final class FakeUser implements UserContext
{
    /** @param string[] $perms */
    public function __construct(private bool $auth = true, private bool $admin = false, private array $perms = [])
    {
    }

    public function isAuthenticated(): bool
    {
        return $this->auth;
    }

    public function userId(): ?int
    {
        return $this->auth ? 1 : null;
    }

    public function email(): ?string
    {
        return null;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    /** @return string[] */
    public function permissions(): array
    {
        return $this->perms;
    }

    public function has(string $permission): bool
    {
        return $this->admin || in_array($permission, $this->perms, true);
    }

    public function activeCompanyId(): ?int
    {
        return null;
    }
}

/**
 * Route + RBAC coverage without a DB: the auth checks (and payload validation)
 * short-circuit before any repository access.
 */
final class TimeTrackerModuleTest extends TestCase
{
    private function appWith(UserContext $user): \Slim\App
    {
        $container = new Container();
        $container->set(UserContext::class, $user);
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
        (new TimeTrackerModule())->register($app);
        return $app;
    }

    private function get(\Slim\App $app, string $path): \Psr\Http\Message\ResponseInterface
    {
        return $app->handle((new ServerRequestFactory())->createServerRequest('GET', $path));
    }

    /** @param array<string,mixed> $body */
    private function post(\Slim\App $app, string $path, array $body): \Psr\Http\Message\ResponseInterface
    {
        return $app->handle(
            (new ServerRequestFactory())->createServerRequest('POST', $path)->withParsedBody($body)
        );
    }

    public function testDeclaresPermissionsAndMigration(): void
    {
        $module = new TimeTrackerModule();
        $ids = array_map(static fn ($p): string => $p->id, $module->permissions());
        self::assertSame(['time:read', 'time:write'], $ids);
        self::assertCount(1, $module->migrations());
    }

    public function testSummaryRequiresAuth(): void
    {
        self::assertSame(401, $this->get($this->appWith(new FakeUser(auth: false)), '/time/summary')->getStatusCode());
        self::assertSame(403, $this->get($this->appWith(new FakeUser(perms: [])), '/time/summary')->getStatusCode());
    }

    public function testStartRequiresWrite(): void
    {
        self::assertSame(403, $this->post($this->appWith(new FakeUser(perms: ['time:read'])), '/time/start', [])->getStatusCode());
    }

    public function testManualEntryValidatesRange(): void
    {
        // writer with a non-positive range → 422 (validation before the repo).
        $res = $this->post($this->appWith(new FakeUser(perms: ['time:write'])), '/time/entries', [
            'started_at' => '2026-07-18 10:00:00',
            'ended_at' => '2026-07-18 09:00:00',
        ]);
        self::assertSame(422, $res->getStatusCode());
    }
}
