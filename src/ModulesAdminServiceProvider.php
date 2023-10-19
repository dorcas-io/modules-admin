<?php

namespace Dorcas\ModulesAdmin;
use Illuminate\Support\ServiceProvider;

class ModulesAdminServiceProvider extends ServiceProvider {

	public function boot()
	{
		$this->loadRoutesFrom(__DIR__.'/routes/web.php');
		$this->loadViewsFrom(__DIR__.'/resources/views', 'modules-admin');
		$this->publishes([
			__DIR__.'/config/modules-admin.php' => config_path('modules-admin.php'),
		], 'dorcas-modules');
		/*$this->publishes([
			__DIR__.'/assets' => public_path('vendor/modules-settings')
		], 'dorcas-modules');*/
	}

	public function register()
	{
		//add menu config
		$this->mergeConfigFrom(
	        __DIR__.'/config/navigation-menu.php', 'navigation-menu.modules-admin.sub-menu'
	     );
	}

}


?>