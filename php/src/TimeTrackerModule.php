<?php
declare(strict_types=1);

namespace Tds\Ext\TimeTracker;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Tds\Panel\Contract\AbstractModule;
use Tds\Panel\Contract\PermissionDef;

/**
 * Backend half of the time-tracker extension. Mounts the time-tracking routes
 * (incl. the widget's `/time/summary` dataEndpoint), declares its permission,
 * and ships its own migrations (class names prefixed `TimeTracker*` to stay
 * globally unique in the in-process migrator).
 */
final class TimeTrackerModule extends AbstractModule
{
    public function id(): string
    {
        return 'time-tracker';
    }

    public function register(App $app): void
    {
        // Widget data endpoint (see the manifest's WidgetManifest.dataEndpoint).
        $app->get('/time/summary', function (Request $request, Response $response): Response {
            // Placeholder payload — the real impl reads the time_entry table
            // scoped to the authenticated user.
            $response->getBody()->write(json_encode(['weekHours' => 12.5], JSON_THROW_ON_ERROR));
            return $response->withHeader('Content-Type', 'application/json');
        });
    }

    /** @return string[] */
    public function migrations(): array
    {
        return [__DIR__ . '/../db/migrations'];
    }

    /** @return PermissionDef[] */
    public function permissions(): array
    {
        return [new PermissionDef('time:read', 'Zeiten ansehen', 'time-tracker')];
    }
}
