<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\File;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Services\Page;
use App\Services\UserManager;
use Auth;
use BillingService;
use FileService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Response;
use Storage;
use Validator;


class UserController extends Controller
{
    protected $messages = array(
        'successUpdate' => 'Пользователь успешно изменен.',
        'forbiddenEdit' => 'Запрещено редактировать чужие данные!',
    );

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * UserController constructor.
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Список пользователей
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        Page::setTitle('Пользователи');
        Page::setDescription('Страница пользователей');

        $whiteListOrderColumns = [
            'id' => true,
            'full_name' => true,
            'email' => true,
            'phone' => true,
            'created_at' => true,
        ];
        $whiteListSearchColumns = [
            'email',
            'phone',
            'full_name',
        ];
        $params = [
           'sort' => $request->has('column') ? $request->input('column') : null,
           'dir' => $request->has('dir') && $request->input('dir') === 'asc' ? 'asc' : 'desc',
           'search' => $request->has('column') && !empty($request->input('search')) ? $request->input('search') : null,
           'length' => $request->has('length')  ? (int) $request->input('length') : '30', //default 30
        ];

        $users = $this->userManager->getUsersWithOrderAndPagination($whiteListOrderColumns, $whiteListSearchColumns, $params);

        if ($request->ajax()) {
            return response()->json([
                'users' => $users,
                'draw' => $request->input('draw')
            ], 200);
        }

        return view('users.index', [
            'users_count' => $users->count(),
            'users' => $users->toJson()
        ]);
    }

    public function show(Request $request, User $user)
    {
        $currUser = Auth::user();
        Page::setTitle('Пользователь');
        Page::setDescription('Страница пользователя');
        $currentManager = $user->getManager();
        $managers = User::role(['admin', 'manager'])->with('clients')->get();
        $clients = $currUser->hasRole('admin') ? User::role(['client', 'user'])->get() : [];
        return view('users.show', [
            'user' => $user,
            'currentManager' => $currentManager,
            'managers' => $managers,
            'clients' => $clients,
            'intruduceList' => $currUser->hasRole('admin') ? $user->introducers : []
        ]);
    }

    public function attachManager(Request $request, User $user)
    {
        if (!Auth::user()) {
            return response()->json(['error' => 'User not authorized.'], 200);
        }
        $manager_id = (int) $request->{'manager_id'};
        $currentManager = $user->getManager();

        // если отключают менеджера
        if ($manager_id === 0) {
            $user = $this->userManager->detachManager($user, $currentManager);
        } else {
            $newManager = User::whereId($manager_id)->get()->first();
            if (!$newManager) {
                return response()->json(['error' => 'Bad data provided.'], 200);
            }

            $user = $this->userManager->changeManager($user, $currentManager, $newManager);
        }

        $currentManager = $user->fresh()->getManager();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'currentManager' => $currentManager,
        ], 200);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function changeBalance(Request $request, User $user)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin')) {
            abort(403);
        }

        if (!$request->has('transaction_type') || !$request->has('amount')) {
            return response()->json([
                'status'=>'error',
                'errors' => ['Bad data provided.']
            ], 200);
        }
        $whiteListTransactionTypes = [
            TransactionType::MANUAL_IN => true,
            TransactionType::MANUAL_OUT => true,
        ];
        $transaction_type = $request->input('transaction_type');
        $amount = $request->input('amount');
        $comment = $request->input('comment');

        if (!array_key_exists($transaction_type, $whiteListTransactionTypes)) {
            abort(403);
        }
        $validation = Validator::make(['amount' => $amount], [
            'amount'=> 'required|regex:/^\d*(\.\d+)?$/'
        ]);
        $errors = $validation->errors();
        $errors = json_decode($errors);
        if ($validation->passes()) {
            if ($transaction_type === TransactionType::MANUAL_OUT && !BillingService::checkAmountOnBalance($user, (int) $amount)){
                return response()->json([
                    'status'=>'exception',
                    'message' => 'Недостаточно средств для выполнения операции!'
                ], 200);
            }
            if (!$comment) {
                $comment = $transaction_type === TransactionType::MANUAL_OUT ? 'Ручное списание с баланса':'Ручное пополнение баланса';
            }
            // транзакция на оплату
            $transaction = BillingService::makeTransaction($user, (int) $amount, $transaction_type, $comment);
            $transaction->setStatus(TransactionStatus::PENDING);// переключаем статус для исполнения
            $transaction->save();
            // исполнение транзакции
            $transaction = BillingService::runTransaction($transaction);

            return response()->json([
                'status'=>'success',
                'transaction' => $transaction,
                'user' => $user->fresh()
            ], 200);
        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function togleRole(Request $request, User $user)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin')) {
            abort(403);
        }
        $inputRole = $request->input('role');
        if (!$user->hasRole($inputRole)) {
            if ($user->hasRole('client'))
                $user->removeRole('client');
            if ($user->hasRole('user'))
                $user->removeRole('user');
            switch ($inputRole) {
                case 'admin':
                    if ($user->hasRole('manager'))
                        break;
                    if ($user->hasRole('introducer'))
                        $user->removeRole('introducer');
                    break;
                case 'manager':
                    if ($user->hasRole('admin'))
                        break;
                    if ($user->hasRole('introducer'))
                        $user->removeRole('introducer');
                    break;
            }
            $user->assignRole($inputRole);
            $user->save();
        } 
        return response()->json([
            'status'=>'success',
            'user' => $user
        ], 200);
    }

    public function syncIntroducer(Request $request, User $user)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin')) {
            abort(403);
        }
        $newAtached = [];
        foreach($request->input('selected') as $client)
            $newAtached[] = $client['code'];
        $user->introducers()->sync($newAtached);
        return response()->json([
            'status'=>'success',
            'user' => $user
        ], 200);
    }

    public function documentIndex(Request $request)
    {
        Page::setTitle('Документы пользователя');
        Page::setDescription('Страница документов пользователя');

        $whiteListOrderColumns = [
            'owner' => true,
            'origin_name' => true,
            'created_at' => true,
        ];
        $whiteListSearchColumns = [
            'owner',
            'origin_name',
            'created_at',
        ];
        $params = [
            'sort' => $request->has('column') ? $request->input('column') : null,
            'dir' => $request->has('dir') && $request->input('dir') === 'asc' ? 'asc' : 'desc',
            'search' => $request->has('column') && !empty($request->input('search')) ? $request->input('search') : null,
            'length' => $request->has('length')  ? (int) $request->input('length') : '30', //default 30
        ];

        $files = $this->userManager->getUserFilesWithOrderAndPagination($whiteListOrderColumns, $whiteListSearchColumns, $params);

        if ($request->ajax()) {
            return response()->json([
                'files' => $files,
                'draw' => $request->input('draw')
            ], 200);
        }

        return view('users.documents', [
            'files_count' => $files->count(),
            'files' => $files->toJson()
        ]);
    }

    public function documentCreate(Request $request)
    {
        $currUser = Auth::user();
        if ($currUser->hasRole('manager|admin')) {
            abort(403);
        }
        $validation = Validator::make($request->all(), [
            'file'=> 'required|mimes:jpeg,png,jpg,pdf|max:10000',
            'name'=> 'required|string',
        ]);
        $errors = $validation->errors();
        $errors = json_decode($errors);
        if ($validation->passes()) {
            $file = $request->file('file');
            $fileData = FileService::getFileData($file, '/'.$currUser->id, $request->input('name'));
            if (Storage::disk('files')->exists($fileData['path'].'/'.$fileData['name'])) {
                return response()->json([
                    'status'=>'error',
                    'errors' => ['file' => ['Файл с таким именем уже существует.']]
                ], 200);
            }
            if (Storage::disk('files')->putFileAs($fileData['path'], $file, $fileData['name'])) {

                $file = $currUser->addFile($fileData);
                $file = File::select([
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
                    ->where('files.id', $file->id)
                    ->with('model')->first();

                return response()->json([
                    'status'=>'success',
                    'file' => $file
                ], 200);

            } else {
                return response()->json([
                    'status'=>'error',
                    'errors' => ['file' => ['Ошибка при сохранении файла']]
                ], 200);
            }
        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    /**
     * @param Request $request
     * @param File $file
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function fileAction(Request $request, File $file)
    {
        $currUser = Auth::user();
        $file = File::whereId($file->id)->with('model')->first();
        if (!$currUser || $currUser->id !== $file->model->id || !Storage::disk('files')->exists(FileService::getFilePath($file))) {
            abort(404);
        }
        if (!$currUser->hasRole('admin|manager') && $currUser->id !== $file->model->id) {
            abort(403);
        }

        if (!FileService::isFileTypeDisplayable($file->type) || $request->has('download')) {

            return FileService::download($file, 'files');

        }else {
            return FileService::display($file, 'files');
        }
    }
}
