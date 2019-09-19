<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Page;
use App\Services\TransactionManager;
use Auth;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    private $transactionManager;

    /**
     * DocumentController constructor.
     * @param TransactionManager $transactionManager
     */
    public function __construct(TransactionManager $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    public function index(Request $request)
    {
        Page::setTitle('Оплата услуг');
        Page::setDescription('Оплата услуг');
        $user = Auth::user();

        $whiteListOrderColumns = [
            'created_at' => true,
            'comment' => true,
            'amount' => true,
            'operation' => true,
        ];
        $whiteListSearchColumns = [
            'amount',
            'operation',
            'comment',
            'created_at',
        ];
        $params = [
            'sort' => $request->has('column') ? $request->input('column') : null,
            'dir' => $request->has('dir') && $request->input('dir') === 'asc' ? 'asc' : 'desc',
            'search' => $request->has('search') && !empty($request->input('search')) ? $request->input('search') : null,
            'length' => $request->has('length')  ? (int) $request->input('length') : '10', //default 10
        ];

        $transactions = $this->transactionManager->getHistoryWithOrderAndPagination($user, $whiteListOrderColumns, $whiteListSearchColumns, $params);

        if ($request->ajax()) {
            return response()->json([
                'transactions' => $transactions,
                'draw' => $request->input('draw')
            ], 200);
        }

        return view('finances.index', [
            'transactions_count' => $transactions->count(),
            'transactions' => $transactions->toJson()
        ]);
    }
}
