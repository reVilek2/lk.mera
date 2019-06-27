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
            'client.last_name as client_last_name',
            'client.second_name as client_second_name',
            'client.first_name as client_first_name',
            'manager.last_name as manager_last_name',
            'manager.second_name as manager_second_name',
            'manager.first_name as manager_first_name',
            ])
            ->join('users as client', 'client.id', '=', 'documents.client_id')
            ->join('users as manager', 'manager.id', '=', 'documents.manager_id')
            ->with('client')
            ->with('manager')
            ->with('files')
            ;

        if (array_key_exists('sort', $params) && array_key_exists('dir', $params) && array_key_exists($params['sort'], $orderColumns)) {
            $sort = $params['sort'];
            $dir = $params['dir'];
            if ($sort !== 'client_full_name' && $sort !== 'manager_full_name') {
                $query->orderBy($sort, $dir);
            } elseif ($sort !== 'client_full_name') {
                $query->orderByRaw('
                    CASE
                       WHEN (`manager`.`last_name` IS NOT NULL and `manager`.`last_name` <> "") THEN `manager`.`last_name`
                       WHEN (`manager`.`first_name` IS NOT NULL OR `manager`.`first_name` <> "")  THEN `manager`.`first_name`
                       WHEN (`manager`.`second_name` IS NOT NULL OR `manager`.`second_name` <> "") THEN `manager`.`second_name`     
                       ELSE 0            
                    END
                    '. $dir.',
                    `manager`.`last_name` '. $dir .', `manager`.`first_name` '. $dir.' , `manager`.`second_name` '. $dir
                );
            } else {
                $query->orderByRaw('
                    CASE
                       WHEN (`client`.`last_name` IS NOT NULL and `client`.`last_name` <> "") THEN `client`.`last_name`
                       WHEN (`client`.`first_name` IS NOT NULL OR `client`.`first_name` <> "")  THEN `client`.`first_name`
                       WHEN (`client`.`second_name` IS NOT NULL OR `client`.`second_name` <> "") THEN `client`.`second_name`     
                       ELSE 0            
                    END
                    '. $dir.',
                    `client`.`last_name` '. $dir .', `client`.`first_name` '. $dir.' , `client`.`second_name` '. $dir
                );
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        if (array_key_exists('search', $params) && $params['search'] && !empty($searchColumns)) {
            $searchValue = $params['search'];
            $query->where(function($q) use ($searchValue, $searchColumns) {
                foreach ($searchColumns as $searchColumn) {
                    if ($searchColumn !== 'full_name') {
                        $q->orWhere($searchColumn, 'like', '%' . $searchValue . '%');
                    } else {
                        $q->orWhereRaw('
                            CONCAT(COALESCE(`client`.`last_name`,\'\'),\' \', COALESCE(`client`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`client`.`last_name`,\'\'),\' \', COALESCE(`client`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`client`.`last_name`,\'\'),\' \', COALESCE(`client`.`first_name`,\'\'),\' \', COALESCE(`client`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`client`.`last_name`,\'\'),\' \', COALESCE(`client`.`second_name`,\'\'),\' \', COALESCE(`client`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR

                            CONCAT(COALESCE(`client`.`first_name`,\'\'),\' \', COALESCE(`client`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`client`.`first_name`,\'\'),\' \', COALESCE(`client`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`client`.`first_name`,\'\'),\' \', COALESCE(`client`.`last_name`,\'\'),\' \', COALESCE(`client`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`client`.`first_name`,\'\'),\' \', COALESCE(`client`.`second_name`,\'\'),\' \', COALESCE(`client`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR

                            CONCAT(COALESCE(`client`.`second_name`,\'\'),\' \', COALESCE(`client`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`client`.`second_name`,\'\'),\' \', COALESCE(`client`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`client`.`second_name`,\'\'),\' \', COALESCE(`client`.`last_name`,\'\'),\' \', COALESCE(`client`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`client`.`second_name`,\'\'),\' \', COALESCE(`client`.`first_name`,\'\'),\' \', COALESCE(`client`.`last_name`,\'\')) like \'%'. $searchValue .'%\'
                        ')->orWhereRaw('
                            CONCAT(COALESCE(`manager`.`last_name`,\'\'),\' \', COALESCE(`manager`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`manager`.`last_name`,\'\'),\' \', COALESCE(`manager`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`manager`.`last_name`,\'\'),\' \', COALESCE(`manager`.`first_name`,\'\'),\' \', COALESCE(`manager`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`manager`.`last_name`,\'\'),\' \', COALESCE(`manager`.`second_name`,\'\'),\' \', COALESCE(`manager`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR

                            CONCAT(COALESCE(`manager`.`first_name`,\'\'),\' \', COALESCE(`manager`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`manager`.`first_name`,\'\'),\' \', COALESCE(`manager`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`manager`.`first_name`,\'\'),\' \', COALESCE(`manager`.`last_name`,\'\'),\' \', COALESCE(`manager`.`second_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`manager`.`first_name`,\'\'),\' \', COALESCE(`manager`.`second_name`,\'\'),\' \', COALESCE(`manager`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR

                            CONCAT(COALESCE(`manager`.`second_name`,\'\'),\' \', COALESCE(`manager`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`manager`.`second_name`,\'\'),\' \', COALESCE(`manager`.`last_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`manager`.`second_name`,\'\'),\' \', COALESCE(`manager`.`last_name`,\'\'),\' \', COALESCE(`manager`.`first_name`,\'\')) like \'%'. $searchValue .'%\' OR
                            CONCAT(COALESCE(`manager`.`second_name`,\'\'),\' \', COALESCE(`manager`.`first_name`,\'\'),\' \', COALESCE(`manager`.`last_name`,\'\')) like \'%'. $searchValue .'%\'
                        ');
                    }
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