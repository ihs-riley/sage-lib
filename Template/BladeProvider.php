<?php

namespace Roots\Sage\Template;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\ViewServiceProvider;

/**
 * Class BladeProvider
 */
class BladeProvider extends ViewServiceProvider
{
    /**
     * @param ContainerContract|null $container
     * @param array $config
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(ContainerContract $container = null, array $config = [])
    {
        /** @noinspection PhpParamsInspection */
        parent::__construct($container ?: Container::getInstance());

        $this->app->bindIf(
            'config',
            function () use ($config) {
                return $config;
            },
            true
        );
    }

    /**
     * Bind required instances for the service provider.
     */
    public function register(): void
    {
        $this->registerFilesystem();
        $this->registerEvents();
        $this->registerEngineResolver();
        $this->registerViewFinder();
        $this->registerFactory();
        $this->registerBladeCompiler();

        parent::register();
    }

    /**
     * Register Filesystem
     */
    public function registerFilesystem(): void
    {
        $this->app->bindIf('files', Filesystem::class, true);
    }

    /**
     * Register the events dispatcher
     */
    public function registerEvents(): void
    {
        $this->app->bindIf('events', Dispatcher::class, true);
    }

    /**
     * Register the view finder implementation.
     */
    public function registerViewFinder()
    {
        $this->app->bindIf('view.finder', function ($app) {
            $config     = $this->app['config'];
            $paths      = $config['view.paths'];
            $namespaces = $config['view.namespaces'];
            $finder     = new FileViewFinder($app['files'], $paths);

            array_map(
                [$finder, 'addNamespace'],
                array_keys($namespaces),
                $namespaces
            );

            return $finder;
        }, true);

        return $this;
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory(): void
    {
        $this->app->singleton('view', function ($app) {
            $resolver = $app['view.engine.resolver'];
            $finder   = $app['view.finder'];
            $factory  = $this->createFactory($resolver, $finder, $app['events']);

            $factory->setContainer($app);
            $factory->share('app', $app);

            return $factory;
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param EngineResolver $resolver
     *
     * @return void
     */
    public function registerBladeEngine($resolver): void
    {
        $resolver->register('blade', function () {
            return new CompilerEngine($this->app['blade.compiler'], $this->app['files']);
        });
    }
}
