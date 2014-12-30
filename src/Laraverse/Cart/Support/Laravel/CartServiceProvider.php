<?php namespace Laraverse\Cart;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['cart'] = $this->app->share(function ($app) {
            return new Cart('main', $app['session'], $app['events']);
        });
    }
}
