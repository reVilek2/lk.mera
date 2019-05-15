<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

class AuthManager
{
    /**
     * @var string User Model Class
     */
    protected $userModel = User::class;
    /**
     * @var string The IP address of this request
     */
    public $ipAddress = '0.0.0.0';

    /**
     * Initializes the singleton
     */
    protected function init()
    {
        $this->ipAddress = Request::ip();
    }
    //
    // User
    //

    /**
     * Creates a new instance of the user model
     *
     * @return User
     */
    public function createUserModel()
    {
        $class = '\\'.ltrim($this->userModel, '\\');
        return new $class();
    }

    /**
     * Prepares a query derived from the user model.
     *
     * @return Builder $query
     */
    protected function createUserModelQuery()
    {
        $model = $this->createUserModel();
        $query = $model->newQuery();

        return $query;
    }

    /**
     * Finds a user by the login value.
     *
     * @param string $id
     * @return mixed (Models\User || null)
     */
    public function findUserById($id)
    {
        $query = $this->createUserModelQuery();
        $user = $query->find($id);

        return $this->validateUserModel($user) ? $user : null;
    }

    /**
     * Perform additional checks on the user model.
     *
     * @param $user
     * @return boolean
     */
    protected function validateUserModel($user)
    {
        return $user instanceof $this->userModel;
    }
}