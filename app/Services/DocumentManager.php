<?php
namespace App\Services;

use App\Models\Document;
use App\Models\User;


class DocumentManager
{

    /**
     * @param User $user
     * @param array $orderColumns
     * @param array $searchColumns
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getDocumentsWithOrderAndPagination(User $user, array $orderColumns = [], array $searchColumns = [], array $params = [])
    {
        $query = Document::select([
            'documents.id',
            'documents.name',
            'documents.amount',
            'documents.created_at',
            'documents.client_id',
            'documents.manager_id',
            'documents.signed',
            'documents.paid',
            ])
            ->with('client')
            ->with('manager')
            ->with('files')
            ;

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
                    $q->orWhere($searchColumn, 'like', '%' . $searchValue . '%');
                }
            });
        }

        if (!$user->hasRole('admin')) {
            if ($user->hasRole('manager')) {
                $query->where('manager_id', '=', $user->id);
            } else {
                $query->where('client_id', '=', $user->id);
            }
        }

        if (array_key_exists('length', $params) && $params['length']) {
            return $query->paginate($params['length']);
        } else {
            return $query->get();
        }
    }
}