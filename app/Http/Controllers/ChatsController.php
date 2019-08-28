<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageSentNotification;
use App\Services\ChatManager;
use App\Services\Page;
use Auth;
use ChatService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Notification;
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

        $user = Auth::user();
        $user->unreadNotificationMessages->markAsRead();
        $chats = $this->chatManager->getChatList($user);

        return view('chat.index', compact('chats'));
    }

    /**
     * @param Request $request
     * @param Chat $chat
     * @return array|\Illuminate\Http\JsonResponse|string
     * @throws \Throwable
     */
    public function chatHistory(Request $request, Chat $chat)
    {
        if (!Auth::user()) {
            return response()->json(['error' => 'User not authorized.'], 200);
        }

        return response()->json([
            'status' => 'success',
            'chat' => $chat
        ], 200);
    }

    /**
     * Persist message to database
     *
     * @param  Request $request
     * @param Chat $chat
     * @return array|Response
     * @throws \Throwable
     */
    public function sendMessage(Request $request, Chat $chat)
    {
        if (!Auth::user()) {
            return response()->json(['error' => 'User not authorized.'], 200);
        }
        $rules = [
            'message-data'=>'required'
        ];
        try {
            $validation = Validator::make(['message-data' => $request->{'message-data'}], $rules);
            if (!$validation->fails()) {

                $body = $request->input('message-data');
                $sender = Auth::user();
                $message = $this->chatManager->makeMessage($body, $chat, $sender);

                foreach ($chat->users()->get() as $receiver) {
                    if ((int)$receiver->id !== (int)$sender->id) {
                        // событие для чата
                        broadcast(new MessageSent($chat, $message, $sender, $receiver));
                        // уведомляем участников чата
                        /** @var User $receiver */
                        $receiver->notify(new MessageSentNotification($chat, $message, $sender, $receiver));
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => $message
                ], 200);

            } else {
                return response()->json([
                    'status' => 'exception',
                    'message' => 'Сообщение не проходит валидацию'
                ], 200);
            }
        } catch (\Exception $ex) {
            $message = 'На сервере произошла ошибка. Пожалуйста, обратитесь к администратору системы.';
            if (config('app.debug')) {
                $message = $ex->getMessage();
            }
            return response()->json([
                'status'=>'exception',
                'message' => $message
            ], 200);
        }
    }
}
