<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recommendation;
use App\Models\RecommendationReceiver;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Log;
use Validator;
use App\Rules\ManagerClientsIds;
use App\Services\RecommedationManager;
use App\Services\Page;
use App\Mail\RecommendationAccepted;
use App\Notifications\RecommendationCreated;

class RecommendationsController extends Controller
{
    private $recommedationManager;

    public function __construct(RecommedationManager $recommedationManager)
    {
        $this->recommedationManager = $recommedationManager;
    }

    public function index(Request $request)
    {
        Page::setTitle('Рекомендации');
        Page::setDescription('Страница Рекомендаций');

        $user = Auth::user();
        if (!$user->hasRole(['manager', 'client', 'introducer'])) {
            abort(403);
        }
        $user->unreadRecommendations->markAsRead();

        $params = [
            'length' => $request->has('length')  ? (int) $request->input('length') : '30', //default 30
        ];

        $recommendations = $this->recommedationManager->getRecommedationsWithPagination($user, $params);
        $managers = User::role(['manager'])->with('clients')->get();

        if ($request->ajax()) {
            return response()->json([
                'recommendations' => $recommendations,
            ], 200);
        }

        return view('recommendations.index', [
            'recommendations' => $recommendations->toJson(),
            'recommendations_count' => $recommendations->count(),
            'managers' => $managers->toJson()
        ]);
    }

    public function create(Request $request)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('manager')) {
            abort(403);
        }

        $validation = Validator::make($request->all(), [
            'clients'=> ['required', 'array', new ManagerClientsIds],
            'title'=> 'required|max:255',
            'text'=> 'required',
            'email'=> 'required|email|max:255',
        ]);

        $errors = $validation->errors();
        $errors = json_decode($errors);

        if ($validation->passes()) {
            $client_ids = $request->input('clients');
            $clients = User::find($client_ids);

            $recommendation = new Recommendation;
            $recommendation->manager_id = $currUser->id;
            $recommendation->title = $request->input('title');
            $recommendation->text = $request->input('text');
            $recommendation->email = $request->input('email');
            $recommendation->save();

            foreach($clients as $client){
                $receiver = new RecommendationReceiver;
                $receiver->client_id = $client->id;

                $recommendation->receivers()->save($receiver);
                $client->notify( new RecommendationCreated($recommendation, $currUser, $client) );
            }

            return response()->json([
                'status'=>'success',
                'recommendation' => $recommendation
            ], 200);

        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    public function clientResolve(Request $request, Recommendation $recommendation){
        $currUser = Auth::user();

        if (!$currUser->hasRole('client')) {
            abort(403);
        }

        $receiver = $recommendation->getReceiverByClientId($currUser->id);
        if(!$receiver){
            abort(403);
        }

        if($receiver->status != RecommendationReceiver::STATUS_PENDING){
            return response()->json([
                'status'=>'error',
                'errors' => ['Bad data provided.']
            ], 200);
        }

        $status = $request->input('status');
        if($status != RecommendationReceiver::STATUS_ACCEPTED && $status != RecommendationReceiver::STATUS_DECLINED){
            return response()->json([
                'status'=>'error',
                'errors' => ['Bad data provided.']
            ], 200);
        }

        $receiver->status = $status;
        $receiver->save();

        if($receiver->status == RecommendationReceiver::STATUS_ACCEPTED){
            $mail = \MultiMail::to($recommendation->email);
            $mail->send(new RecommendationAccepted($recommendation));
        }

        $recommendation = $recommendation::where('id', $recommendation->id)
            ->with(['receivers' => function ($query) use($currUser) {
                $query->select('recommendation_id', 'client_id', 'status');
                $query->where('client_id', '=', $currUser->id);
            }])
            ->first();

        return response()->json([
            'status'=>'success',
            'recommendation' => $recommendation,
        ], 200);
    }
}
