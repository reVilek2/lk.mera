<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerChat();
    }

    /**
     * Registers Chat.
     *
     * @return void
     */
    private function registerChat()
    {
        $this->app->bind('chat', function () {
            return $this->app->make(\App\Services\ChatManager::class);
        });
    }
}
