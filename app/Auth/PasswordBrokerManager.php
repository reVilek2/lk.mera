<?php
namespace App\Auth;

use App\Auth\PasswordBroker as CustomPasswordBroker;
use Closure;
use Illuminate\Contracts\Auth\PasswordBrokerFactory as FactoryContract;
use InvalidArgumentException;
use \Illuminate\Auth\Passwords\PasswordBrokerManager as BasePasswordBrokerManager;

class PasswordBrokerManager extends BasePasswordBrokerManager implements FactoryContract
{
    /**
     * Resolve the given broker.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Password resetter [{$name}] is not defined.");
        }

        // The password broker uses a token repository to validate tokens and send user
        // password e-mails, as well as validating that password reset process as an
        // aggregate service of sorts providing a convenient interface for resets.
        return new CustomPasswordBroker(
            $this->createTokenRepository($config),
            $this->app['auth']->createUserProvider($config['provider'] ?? null)
        );
    }

    /**
     * Attempt to get the broker from the local cache.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker($name = null)
    {
        return parent::broker($name);
    }

    /**
     * Send a password reset link to a user.
     *
     * @param  array $credentials
     * @return string
     */
    public function sendResetLink(array $credentials)
    {
        return parent::sendResetLink($credentials);
    }

    /**
     * Reset the password for the given token.
     *
     * @param  array $credentials
     * @param  \Closure $callback
     * @return mixed
     */
    public function reset(array $credentials, Closure $callback)
    {
        return parent::reset($credentials, $callback);
    }

    /**
     * Set a custom password validator.
     *
     * @param  \Closure $callback
     * @return void
     */
    public function validator(Closure $callback)
    {
        parent::validator($callback);
    }

    /**
     * Determine if the passwords match for the request.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validateNewPassword(array $credentials)
    {
        return parent::validateNewPassword($credentials);
    }
}