<?php
namespace App\Services;

use App\Models\User;
use App\Notifications\EmailConfirmNotification;
use ChatService;
use Illuminate\Http\Request;
use Mail;

class UserManager
{
    /**
     * Sends the activation email to a user
     * @param  User $user
     * @return void
     */
    public function sendActivationEmail(User $user)
    {
        try {
            $code = implode('!', [$user->id, $user->getEmailActivationCode()]);

            $url = route('email.confirm', $code);

            Mail::to($user->email)->send(new EmailConfirmNotification($url));

        } catch (\Exception $ex) {
            \Log::error('Ошибка при отправке Email для подтверждения почты: '.$ex->getMessage());
        }
    }

    /**
     * @param array $orderColumns
     * @param array $searchColumns
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getUsersWithOrderAndPagination(array $orderColumns = [], array $searchColumns = [], array $params = [])
    {
        $query = User::select(['id', 'email', 'phone', 'first_name', 'second_name', 'last_name', 'created_at']);

        if (array_key_exists('sort', $params) && array_key_exists('dir', $params) && array_key_exists($params['sort'], $orderColumns)) {
            $sort = $params['sort'];
            $dir = $params['dir'];
            if ($sort !== 'full_name') {
                $query->orderBy($sort, $dir);
            } else {
                $query->orderByRaw('
                    CASE
                       WHEN (last_name IS NOT NULL and last_name <> "") THEN last_name
                       WHEN (first_name IS NOT NULL OR first_name <> "")  THEN first_name
                       WHEN (second_name IS NOT NULL OR second_name <> "") THEN second_name     
                       ELSE 0            
                    END
                    '. $dir.',
                    last_name '. $dir .', first_name '. $dir.' , second_name '. $dir
                );
            }
        }
        if (array_key_exists('search', $params) && $params['search'] && !empty($searchColumns)) {
            $searchValue = $params['search'];
            $query->where(function($q) use ($searchValue, $searchColumns) {
                foreach ($searchColumns as $searchColumn) {
                    if ($searchColumn !== 'full_name') {
                        $q->orWhere($searchColumn, 'like', '%' . $searchValue . '%');
                    } else {
                        $q->orWhere('first_name', 'like', '%' . $searchValue . '%')
                            ->orWhere('second_name', 'like', '%' . $searchValue . '%')
                            ->orWhere('last_name', 'like', '%' . $searchValue . '%')
                            ->orWhereRaw('
                                CONCAT(COALESCE(`last_name`,\'\'),\' \', COALESCE(`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                CONCAT(COALESCE(`last_name`,\'\'),\' \', COALESCE(`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                CONCAT(COALESCE(`last_name`,\'\'),\' \', COALESCE(`first_name`,\'\'),\' \', COALESCE(`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                CONCAT(COALESCE(`last_name`,\'\'),\' \', COALESCE(`second_name`,\'\'),\' \', COALESCE(`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
        
                                CONCAT(COALESCE(`first_name`,\'\'),\' \', COALESCE(`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                CONCAT(COALESCE(`first_name`,\'\'),\' \', COALESCE(`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                CONCAT(COALESCE(`first_name`,\'\'),\' \', COALESCE(`last_name`,\'\'),\' \', COALESCE(`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                CONCAT(COALESCE(`first_name`,\'\'),\' \', COALESCE(`second_name`,\'\'),\' \', COALESCE(`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                
                                CONCAT(COALESCE(`second_name`,\'\'),\' \', COALESCE(`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                CONCAT(COALESCE(`second_name`,\'\'),\' \', COALESCE(`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                CONCAT(COALESCE(`second_name`,\'\'),\' \', COALESCE(`last_name`,\'\'),\' \', COALESCE(`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                                CONCAT(COALESCE(`second_name`,\'\'),\' \', COALESCE(`first_name`,\'\'),\' \', COALESCE(`last_name`,\'\')) like \'%'. $searchValue .'%\'
                            ');
                    }
                }
            });
        }

        if (array_key_exists('length', $params) && $params['length']) {
            return $query->paginate($params['length']);
        } else {
            return $query->get();
        }
    }

    /**
     * @param User $user
     * @param User|null $oldManager
     * @param User|null $newManager
     * @return User
     */
    public function changeManager(User $user, User $oldManager = null, User $newManager = null)
    {
        if (!$newManager || ($oldManager && $newManager && $oldManager->id === $newManager->id)) {
            return $user;
        }
        if (!$oldManager) {
            $user->manager()->attach($newManager->id);
        } else {
            $user->manager()->detach($oldManager->id);
            $user->manager()->attach($newManager->id);
        }

        // даем роль "клиент"
        $user = $this->assignClientRole($user);
        // синхронизируем чат client/manager
        $this->syncClientChat($user, $newManager);

        return $user;
    }

    public function detachManager(User $user, User $currManager = null)
    {
        if ($currManager) {
            $user->manager()->detach($currManager->id);
            // синхронизируем чат client/manager
            $this->syncClientChat($user, null);
        }

        return $user;
    }

    public function assignClientRole(User $user)
    {
        if (! $user->hasRole(User::ROLE_CLIENT) ) { // если нет роли "client"
            if ( $user->hasRole(User::ROLE_USER) ) { // удаляем старую роль "user" если есть
                $user->removeRole(User::ROLE_USER);
            }
            $user->assignRole(User::ROLE_CLIENT);
        }

        return $user;
    }

    public function syncClientChat(User $user, User $manager = null)
    {
        $participants = [];
        $participants[] = $user->id;
        if ($manager) {
            $participants[] = $manager->id;
        }

        $chat = ChatService::getPrivateClientChat($user);
        if ($chat) {
            // chat sync users
            ChatService::chatSyncUsers($chat, $participants);
        } else {
            // new chat
            ChatService::createChat($participants, 'Приватный чат c менеджером', true, true);
        }
    }
}