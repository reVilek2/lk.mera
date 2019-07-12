<?php
namespace App\Services;

use App\Models\File;
use App\Models\User;
use App\Notifications\EmailConfirmNotification;
use Auth;
use ChatService;
use Illuminate\Support\Facades\Date;
use Mail;
use SmsService;
use stdClass;

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
            $code = implode('!', [$user->id, $user->makeEmailActivationCode()]);

            $url = route('email.confirm', $code);

            Mail::to($user->email)->send(new EmailConfirmNotification($url));

        } catch (\Exception $ex) {
            \Log::error('Ошибка при отправке Email для подтверждения почты: '.$ex->getMessage());
        }
    }

    /**
     * Sends the activation phone to a user
     * @param  User $user
     * @return bool
     * @throws \Exception
     */
    public function sendActivationPhone(User $user)
    {
        $phone = $user->phone;
        if (!$phone || empty($phone)) {
            throw new \Exception('Ошибка: телефонный номер на найден.');
        }

        try {
            $code = $user->makePhoneActivationCode();

            SmsService::send($phone, 'Код активации: '.$code);

            return true;
        } catch (\Exception $ex) {
            \Log::error('Ошибка при отправке Sms для подтверждения телефона: '.$ex->getMessage());
        }
    }

    public function checkActivationCode(User $user, $code)
    {
        $result = new stdClass();
        $result->status = 'success';

        if ($user->phone_confirmation_code !== $code) {
            $result->status = 'error';
            $result->code = 'invalid_code';
            $result->message = 'Введен неверный код.';
            return $result;
        }

        $codeCreatedDate = clone $user->phone_confirmation_code_created_at;
        $currentDate = Date::now();
        if ($codeCreatedDate->modify('+'.User::PHONE_CODE_EXPIRY.' seconds') < $currentDate) {
            $result->status = 'error';
            $result->code = 'code_expired';
            $result->message = 'Действие кода истекло.';
            return $result;
        }

        return $result;
    }

    /**
     * @param User $user
     * @return int
     */
    public function getResendPhoneCodeTime(User $user)
    {
        if (!$user->phone_confirmation_code || !$user->phone_confirmation_code_created_at) {
            return 0;
        }
        $codeCreatedDate = clone $user->phone_confirmation_code_created_at;
        $currentDate = Date::now();
        if ($codeCreatedDate->modify('+'.User::PHONE_CODE_EXPIRY.' seconds') < $currentDate) {
            return 0;
        }

        return $codeCreatedDate->getTimestamp() - $currentDate->getTimestamp();
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
     * @param array $orderColumns
     * @param array $searchColumns
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getUserFilesWithOrderAndPagination(array $orderColumns = [], array $searchColumns = [], array $params = [])
    {
        $currUser = Auth::user();
        $is_admin = $currUser->hasRole('admin|manager');

        $query = File::select([
            'files.id',
            'files.model_type',
            'files.model_id',
            'files.name',
            'files.origin_name',
            'files.type',
            'files.path',
            'files.size',
            'files.created_at',

            'users.last_name',
            'users.second_name',
            'users.first_name',
            'users.email',
            'users.phone',
        ])->join('users', 'users.id', '=', 'files.model_id')
            ->whereModelType(User::class)
            ->with('model');

        if (!$is_admin) {
            $query->whereModelId($currUser->id);
        }

        if (array_key_exists('sort', $params) && array_key_exists('dir', $params) && array_key_exists($params['sort'], $orderColumns)) {
            $sort = $params['sort'];
            $dir = $params['dir'];
            if ($sort !== 'owner') {
                $query->orderBy($sort, $dir);
            } elseif ($is_admin) { // если админ то разрешаем сортировать
                $query->orderByRaw('
                    CASE
                        WHEN (`users`.`first_name` IS NOT NULL OR `users`.`first_name` <> "")  THEN `users`.`first_name`
                        WHEN (`users`.`second_name` IS NOT NULL OR `users`.`second_name` <> "") THEN `users`.`second_name`
                        WHEN (`users`.`last_name` IS NOT NULL and `users`.`last_name` <> "") THEN `users`.`last_name`                        
                        WHEN (`users`.`email` IS NOT NULL and `users`.`email` <> "") THEN `users`.`email`                        
                        WHEN (`users`.`phone` IS NOT NULL and `users`.`phone` <> "") THEN `users`.`phone`                        
                       ELSE 0            
                    END
                    '. $dir.',
                    `users`.`first_name` '. $dir .', `users`.`second_name` '. $dir.' , `users`.`last_name` '. $dir.', `users`.`email` '. $dir.', `users`.`phone` '. $dir
                );
            } else {
                $query->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }
        if (array_key_exists('search', $params) && $params['search'] && !empty($searchColumns)) {
            $searchValue = $params['search'];
            $query->where(function($q) use ($searchValue, $searchColumns, $is_admin) {
                foreach ($searchColumns as $searchColumn) {
                    if ($searchColumn === 'created_at') {
                        $q->orWhereRaw('DATE_FORMAT(files.created_at, \'%Y-%m-%d %H:%i:%s\') like \'%' . $searchValue . '%\'');
                    } elseif ($searchColumn === 'owner' && $is_admin) {
                        $q->orWhereRaw('
                            CONCAT(COALESCE(`users`.`last_name`,\'\'),\' \', COALESCE(`users`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`users`.`last_name`,\'\'),\' \', COALESCE(`users`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`users`.`last_name`,\'\'),\' \', COALESCE(`users`.`first_name`,\'\'),\' \', COALESCE(`users`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`users`.`last_name`,\'\'),\' \', COALESCE(`users`.`second_name`,\'\'),\' \', COALESCE(`users`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR

                            CONCAT(COALESCE(`users`.`first_name`,\'\'),\' \', COALESCE(`users`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`users`.`first_name`,\'\'),\' \', COALESCE(`users`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`users`.`first_name`,\'\'),\' \', COALESCE(`users`.`last_name`,\'\'),\' \', COALESCE(`users`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`users`.`first_name`,\'\'),\' \', COALESCE(`users`.`second_name`,\'\'),\' \', COALESCE(`users`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR

                            CONCAT(COALESCE(`users`.`second_name`,\'\'),\' \', COALESCE(`users`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`users`.`second_name`,\'\'),\' \', COALESCE(`users`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`users`.`second_name`,\'\'),\' \', COALESCE(`users`.`last_name`,\'\'),\' \', COALESCE(`users`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`users`.`second_name`,\'\'),\' \', COALESCE(`users`.`first_name`,\'\'),\' \', COALESCE(`users`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            `users`.`phone` like \'%'. $searchValue .'%\' OR
                            `users`.`email` like \'%'. $searchValue .'%\'
                        ');
                    } elseif ($searchColumn !== 'owner') {
                        $q->orWhere('files.'.$searchColumn, 'like', '%' . $searchValue . '%');
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