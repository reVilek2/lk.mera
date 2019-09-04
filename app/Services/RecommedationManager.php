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

        if ($user->hasRole('manager')) {
            $query
            ->where('manager_id', '=', $user->id)
            ->with(['receivers' => function ($query) use($user) {
                $query->select('recommendation_id', 'client_id', 'status');
            }]);
        } elseif($user->hasRole('client')) {
            $query
            ->whereHas('receivers', function ($query) use($user) {
                $query->where('client_id', '=', $user->id);
            })
            ->with(['receivers' => function ($query) use($user) {
                $query->select('recommendation_id', 'client_id', 'status');
                $query->where('client_id', '=', $user->id);
            }]);
        }

        if (array_key_exists('length', $params) && $params['length']) {
            return $query->paginate($params['length']);
        } else {
            return $query->get();
        }
    }
}
