<?php
namespace App\Services;

use App\Models\User;
use App\Models\Recommendation;
use App\Models\RecommendationReceiver;

class RecommedationManager
{
    public function getRecommedationsWithPagination(User $user, array $params = [])
    {
        $query = Recommendation::query()
            ->orderBy('id', 'desc');

        if ($user->hasRole('admin')) {
          $query
            ->with(['receivers' => function ($query) use($user) {
                $query->select('recommendation_id', 'client_id', 'status');
            }]);
        } else if ($user->hasRole('manager')) {
            $query
            ->where('manager_id', '=', $user->id)
            ->with(['receivers' => function ($query) use($user) {
                $query->select('recommendation_id', 'client_id', 'status');
            }]);
        } else if($user->hasRole('client')) {
            $query
            ->whereHas('receivers', function ($query) use($user) {
                $query->where('client_id', '=', $user->id);
            })
            ->with(['receivers' => function ($query) use($user) {
                $query->select('recommendation_id', 'client_id', 'status');
                $query->where('client_id', '=', $user->id);
            }]);
        } else if ($user->hasRole('introducer')) {
            foreach ($user->introducers as $key=>$client) {
                if ($key === 0)
                    $query
                    ->whereHas('receivers', function ($query) use($client) {
                        $query->where('client_id', '=', $client->id);
                    })
                    ->with(['receivers' => function ($query) use($client) {
                        $query->select('recommendation_id', 'client_id', 'status');
                    }]);
                else
                    $query
                    ->orWhereHas('receivers', function ($query) use($client) {
                        $query->where('client_id', '=', $client->id);
                    })
                    ->with(['receivers' => function ($query) use($client) {
                        $query->select('recommendation_id', 'client_id', 'status');
                    }]);
            }
        }

        if (array_key_exists('length', $params) && $params['length']) {
            return $query->paginate($params['length']);
        } else {
            return $query->get();
        }
    }
}
