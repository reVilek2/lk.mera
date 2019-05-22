<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageSentNotification;
use App\Services\ChatManager;
use App\Services\Page;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Events\MessageSent;

class ChatsController extends Controller
{
    /**
     * @var ChatManager
     */
    private $chatManager;

    public function __construct(ChatManager $chatManager)
    {
        $this->chatManager = $chatManager;
    }

    /**
     * Show chats
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Page::setTitle('Чат | MeraCapital');
        Page::setDescription('Страница чата');

        $users = User::all();

        return view('chat.index', compact('users'));
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array|\Illuminate\Http\JsonResponse|string
     * @throws \Throwable
     */
    public function chatHistory(Request $request, User $user)
    {
        if (!Auth::user()) {
            return response()->json(['error' => 'User not authorized.'], 200);
        }

        $conversations = $this->chatManager->getMessagesByUserId($user->id, Auth::user()->id);
        $messages = [];
        if($conversations) {
            $user = $conversations->withUser;
            $messages = $conversations->messages;
        }
        if (count($messages) > 0) {
            $messages = $messages->sortBy('id');
        }
        $html = view('chat.ajax.chatHistory', compact('messages', 'user'))->render();
        return response()->json(['status'=>'success', 'html' => $html], 200);
    }

    /**
     * Fetch all messages
     *
     * @return Message
     */
    public function fetchMessages()
    {
        return Message::with('user')->get();
    }

    /**
     * Persist message to database
     *
     * @param  Request $request
     * @return array|Response
     * @throws \Throwable
     */
    public function sendMessage(Request $request)
    {
        if (!Auth::user()) {
            return response()->json(['error' => 'User not authorized.'], 200);
        }
        $rules = [
            'message-data'=>'required',
            '_id'=>'required'
        ];
        $validation = Validator::make(['message-data' => $request->{'message-data'}, '_id' => $request->{'_id'}], $rules);
        if (!$validation->fails()) {
            $body = $request->input('message-data');
            $receiverId = $request->input('_id');
            $senderId = Auth::user()->id;
            if ($message = $this->chatManager->sendMessageByUserId($receiverId, $senderId, $body)) {
                /** @var User $receiver */
                $receiver = User::whereId($receiverId)->get()->first();
                $sender = User::whereId($senderId)->get()->first();
                $receiverHtml = view('chat.ajax.receiverMessageHtml', compact('message'))->render();
                $senderHtml = view('chat.ajax.senderMessageHtml', compact('message'))->render();

                $receiver->notify(new MessageSentNotification($receiver, $sender, $message, $receiverHtml));
//                broadcast(new MessageSent($receiver, $sender, $message, $receiverHtml))->toOthers();

                return response()->json(['status'=>'success', 'html' => $senderHtml], 200);
            }
        }
    }
}
