<?php
declare(strict_types=1);

namespace Tds\Ext\TimeTracker\Tests;

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tds\Ext\TimeTracker\TimeTrackerModule;
use Tds\Panel\Contract\ModuleRegistry;

/**
 * Composes the module through a real ModuleRegistry + Slim app and dispatches
 * its widget dataEndpoint.
 */
final class TimeTrackerModuleTest extends TestCase
{
    public function testSummaryRouteIsMounted(): void
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        (new ModuleRegistry([new TimeTrackerModule()]))->registerAll($app);

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/time/summary');
        $response = $app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('weekHours', (string) $response->getBody());
    }

    public function testDeclaresPermissionAndMigration(): void
    {
        $module = new TimeTrackerModule();
        $ids = array_map(static fn ($p): string => $p->id, $module->permissions());
        self::assertContains('time:read', $ids);
        self::assertCount(1, $module->migrations());
    }
}
