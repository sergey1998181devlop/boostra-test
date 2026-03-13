<?php
namespace App\Providers;

use App\Core\Application\Container\Container;
use App\Core\Database\BaseDatabase;

class DatabaseServiceProvider
{
    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        $this->container->bind(\Database::class, function() {
            static $instance = null;
            if ($instance === null) {
                $instance = new \Database();
            }
            return $instance;
        });

        $this->container->bind(BaseDatabase::class, function() {
            return BaseDatabase::getInstance();
        });
    }

    public function boot(): void
    {
        //
    }
}
