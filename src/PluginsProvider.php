<?php namespace Tehcodedninja\Plugins;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Tehcodedninja\Plugins\Models\Plugin;

class PluginsProvider extends ServiceProvider
{
	/**
	 * This namespace is applied to your controller routes.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = "Tehcodedninja\Plugins";

	protected $plugins = [];

	 /**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishMigration();
		$this->publishHelloWorld();

		if(Schema::hasTable('plugins') && $this->app->runningInConsole() === false)
		{
			$this->plugins = Plugin::active()->get();
			
			foreach ($this->plugins as $plugin) {
				// Load Plugin Class
				require $plugin->path.'/'.$plugin->file;

				// Load Controllers
				$controllers = glob($plugin->path.'/Controllers/*');

				foreach ($controllers as $controller) {
					if (strtolower(substr($controller, strrpos($controller, '.') + 1)) == 'php')
					{
						require $controller;
					}
				}

				// Load Models
				$models = glob($plugin->path.'/Models/*');

				foreach ($models as $model) {
					if (strtolower(substr($model, strrpos($model, '.') + 1)) == 'php')
					{
						require $model;
					}
				}

				// Load Views
				$this->loadViewsFrom($plugin->path.'/views', $plugin->pluginClass());
				
				// Load Routes
				Route::group([
						'middleware' => 'web',
						'namespace' => $plugin->namespace.'\Controllers',
					], function ($router) {
						$plugin_folder_name = Plugin::getFolderName($router->getGroupStack()[0]['namespace']);
						require $_SERVER['DOCUMENT_ROOT'].'/content/plugins/'.$plugin_folder_name.'/routes.php';
				});
			}
		}
	}

	protected function publishMigration()
  {
    if (! class_exists('CreatePluginsTable')) {
      // Publish the migration
      $timestamp = date('Y_m_d_His', time());
      $this->publishes([
        __DIR__.'/database/migrations/create_plugins_table.php' => database_path('migrations/'.$timestamp.'_create_plugins_table.php'),
        ], 'migrations');
    }
  }

  public function publishHelloWorld()
  {
  	$timestamp = date('Y_m_d_His', time());
      $this->publishes([
        __DIR__.'/public/' => public_path(''),
        ], 'plugins');
  }

	public function register()
	{
	}
}