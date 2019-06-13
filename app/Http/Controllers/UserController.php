<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Page;
use App\Services\UserManager;
use Auth;
use BillingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Response;
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
        Page::setTitle('Пользователи | MeraCapital');
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
           'length' => $request->has('length')  ? (int) $request->input('length') : '10', //default 10
        ];

        $users = $this->userManager->getUsersWithOrderAndPagination($whiteListOrderColumns, $whiteListSearchColumns, $params);

        if ($request->ajax()) {
            return response()->json([
                'users' => $users,
                'draw' => $request->input('draw')
            ], 200);
        }

        return view('users.index', [
            'users' => $users->toJson()
        ]);
    }

    public function show(Request $request, User $user)
    {
        Page::setTitle('Пользователь | MeraCapital');
        Page::setDescription('Страница пользователя');
        $currentManager = $user->getManager();
        $managers = User::role(['admin', 'manager'])->with('clients')->get();

        return view('users.show', [
            'user' => $user,
            'currentManager' => $currentManager,
            'managers' => $managers
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

    public function changeBalance(Request $request, User $user)
    {
        $currUser = Auth::user();
        $user->getManager();
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
            'manual_in' => true,
            'manual_out' => true,
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
            $transaction = BillingService::makeTransaction($user, (int) $amount, $transaction_type, $comment);
            return response()->json([
                'status'=>'success',
                'transaction' => $transaction
            ], 200);
        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }
}
