<?php
namespace App\Services;

use App\Models\BillingAccountType;
use App\Models\BillingOperation;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User;
use MoneyAmount;

class TransactionManager
{
    /**
     * @param User $user
     * @param array $orderColumns
     * @param array $searchColumns
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getHistoryWithOrderAndPagination(User $user, array $orderColumns = [], array $searchColumns = [], array $params = [])
    {
        $query = BillingOperation::select([
                'billing_accounts.id as account_id',
                'billing_accounts.user_id as account_user_id',
                'billing_operations.amount',
                'billing_operations.created_at',
                'billing_operation_type.code as operation',
                'billing_operation_type.name as operation_name',
                'billing_transaction_status.code as status',
                'billing_transaction_status.name as status_name',
                'billing_account_type.code as account',
                'billing_account_type.name as account_name',
                'billing_transactions.comment'
              ]
        )
            ->join('billing_operation_type', 'billing_operation_type.id', '=', 'billing_operations.type_id')
            ->join('billing_accounts', 'billing_accounts.id', '=', 'billing_operations.account_id')
            ->join('billing_account_type', 'billing_account_type.id', '=', 'billing_accounts.acc_type_id')
            ->join('billing_transactions', 'billing_transactions.id', '=', 'billing_operations.transaction_id')
            ->join('billing_transaction_status', 'billing_transaction_status.id', '=', 'billing_transactions.status_id')
            ->where('billing_transaction_status.code', TransactionStatus::SUCCESS)
            ->where('billing_account_type.code', BillingAccountType::BALANCE)
        ;
        if (!$user->hasRole('admin')) {
            $query->where('billing_accounts.user_id', $user->id);
        }

        if (array_key_exists('sort', $params) && array_key_exists('dir', $params) && array_key_exists($params['sort'], $orderColumns)) {
            $sort = $params['sort'];
            $dir = $params['dir'];
            $query->orderBy($sort, $dir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if (array_key_exists('search', $params) && $params['search'] && !empty($searchColumns)) {
            $searchValue = $params['search'];
            $query->where(function($q) use ($searchValue, $searchColumns) {
                foreach ($searchColumns as $searchColumn) {
                    if ($searchColumn === 'amount' && is_numeric( $searchValue )) {
                        $q->orWhere('billing_operations.amount', 'like', '%' . MoneyAmount::toExternal($searchValue) . '%');
                    } elseif ($searchColumn === 'operation') {
                        $q->orWhere('billing_operation_type.name', 'like', '%' . $searchValue . '%');
                    } elseif ($searchColumn === 'comment') {
                        $q->orWhere('billing_transactions.comment', 'like', '%' . $searchValue . '%');
                    } elseif ($searchColumn === 'created_at') {
                        $q->orWhereRaw('DATE_FORMAT(billing_operations.created_at, \'%Y-%m-%d %H:%i:%s\') like \'%' . $searchValue . '%\'');
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
}