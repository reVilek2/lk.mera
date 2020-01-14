<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\File;
use App\Models\User;
use App\Services\DocumentManager;
use App\Services\Page;
use App\Notifications\DocumentCreated;
use Auth;
use BillingService;
use FileService;
use Illuminate\Http\Request;
use SmsService;
use Storage;
use Validator;

class DocumentController extends Controller
{
    private $documentManager;

    /**
     * DocumentController constructor.
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * Список документов
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        Page::setTitle('Отчеты');
        Page::setDescription('Страница отчетов');
        $user = Auth::user();

        $user->unreadDocuments->markAsRead();

        $whiteListOrderColumns = [
            'id' => true,
            'name' => true,
            'amount' => true,
            'created_at' => true,
            'manager_full_name' => true,
            'client_full_name' => true,
        ];
        $whiteListSearchColumns = [
            'name',
            'amount',
            'full_name'
        ];
        $params = [
            'sort' => $request->has('column') ? $request->input('column') : null,
            'dir' => $request->has('dir') && $request->input('dir') === 'asc' ? 'asc' : 'desc',
            'search' => $request->has('search') && !empty($request->input('search')) ? $request->input('search') : null,
            'length' => $request->has('length')  ? (int) $request->input('length') : '30', //default 30
        ];

        $documents = $this->documentManager->getDocumentsWithOrderAndPagination($user, $whiteListOrderColumns, $whiteListSearchColumns, $params);
        $managers = User::role(['admin', 'manager'])->with('clients')->get();
        if ($request->ajax()) {
            return response()->json([
                'documents' => $documents,
                'draw' => $request->input('draw')
            ], 200);
        }

        return view('documents.index', [
            'documents_count' => $documents->count(),
            'documents' => $documents->toJson(),
            'managers' => $managers->toJson()
        ]);
    }

    /**
     * Создание документа.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('manager')) {
            abort(403);
        }

        $validation = Validator::make($request->all(), [
            'file'=> 'required|mimes:jpeg,png,jpg,pdf|max:10000',
            'name'=> 'required|string',
            'client'=> 'required|integer',
            'manager'=> 'required|integer',
            'amount'=> 'required|regex:/^\d*(\.\d+)?$/',
        ]);
        $errors = $validation->errors();
        $errors = json_decode($errors);
        if ($validation->passes()) {
            $file = $request->file('file');
            $fileData = FileService::getFileData($file, Document::getSaveFileDir());
            if (Storage::disk('documents')->exists($fileData['path'].'/'.$fileData['name'])) {
                return response()->json([
                    'status'=>'error',
                    'errors' => ['file' => ['Файл с таким именем уже существует.']]
                ], 200);
            }
            if (Storage::disk('documents')->putFileAs($fileData['path'], $file, $fileData['name'])) {
                $document = Document::Create([
                    'name'=> $request->input('name'),
                    'amount'=> $request->input('amount'),
                    'client_id' => $request->input('client'),
                    'manager_id' => $request->input('manager'),
                ]);
                $document->addFile($fileData);

                $client = $document->client;
                if($client){
                    $client->notify( new DocumentCreated($document, $currUser, $client) );
                }

                $document = Document::whereId($document->id)->with('client')->with('manager')->with('files')->first();
                return response()->json([
                    'status'=>'success',
                    'document' => $document
                ], 200);

            } else {
                return response()->json([
                    'status'=>'error',
                    'errors' => ['file' => ['Ошибка при сохранении файла']]
                ], 200);
            }
        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    /**
     * Удаление документа.
     *
     * @param Request $request
     * @param Document $document
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Document $document)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin')) {
            abort(403);
        }

        if($document->signed || $document->paid){
            abort(403);
        }

        $document->delete();
        return response()->json([
            'status'=>'success',
            'action'=>'delete',
        ], 200);

    }

    /**
     * Отображение/Скачивание файла прикрепленного к документу
     *
     * @param Request $request
     * @param Document $document
     * @param File $file
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function documentFile(Request $request, Document $document, File $file)
    {
        $currUser = Auth::user();
        $file = File::whereId($file->id)->with('model')->first();
        $document = Document::whereId($document->id)->with('client')->with('manager')->first();
        if ($document->id !== $file->model->id || !$currUser || !Storage::disk('documents')->exists(FileService::getFilePath($file))) {
            abort(404);
        }
        if (!$currUser->hasRole('admin') && $currUser->id !== $document->client->id && $currUser->id !== $document->manager->id) {
            $introduced = false;
            if ($currUser->hasRole('introducer')) {
                foreach ($currUser->introducers as $client) {
                    if ($client->id === $document->client->id)
                        $introduced = true;
                }
            }
            if (!$introduced)
                abort(403);
        }

        if (!FileService::isFileTypeDisplayable($file->type) || $request->has('download')) {

            return FileService::download($file, 'documents');

        } else {
            return FileService::display($file, 'documents');
        }
    }

    /**
     * Смена статуса документа (Только для администратора)
     *
     * @param Request $request
     * @param Document $document
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request, Document $document)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin')) {
            abort(403);
        }

        if (!$request->has('signed') || !$request->has('paid')) {
            return response()->json([
                'status'=>'error',
                'errors' => ['Bad data provided.']
            ], 200);
        }

        $signed = (int) $request->input('signed');
        $paid = (int) $request->input('paid');

        $document->signed = $signed;
        $document->paid = $paid;
        $document->save();
        $document->history()->create([
            'user_id'=>$currUser->id,
            'signed'=>$signed,
            'paid'=>$paid,
        ]);

        return response()->json([
            'status'=>'success',
            'document' => $document
        ], 200);
    }

    /**
     * Оплата документа
     *
     * @param Request $request
     * @param Document $document
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPaid(Request $request, Document $document)
    {
        $currUser = Auth::user();
        $document = Document::whereId($document->id)->with('manager')->first();

        if (!$currUser->hasRole('admin') && $currUser->id !== $document->manager->id && $currUser->id !== $document->client->id) {
            abort(403);
        }

        if (!$request->has('paid')) {
            return response()->json([
                'status'=>'error',
                'errors' => ['Bad data provided.']
            ], 200);
        }
        // если документ уже оплачен то возврашаем просто документ
        if ($document->paid) {
            return response()->json([
                'status'=>'success',
                'document' => $document,
            ], 200);
        }

        $is_client = $currUser->id === $document->client->id;
        $signed = $request->has('signed') && $currUser->hasRole('admin') ? true : false;

        try {
            if ($is_client) {
                /** @var User $client */
                $client = $document->client;
                if ($missingAmount = BillingService::calculateMissingAmount($client, (int) $document->amount)) {
                    $paymentCardDefault = $client->getPaymentCardDefault(config('payment.default_driver'));
                    return response()->json([
                        'status'=>'missingAmount',
                        'missingAmount' => $missingAmount,
                        'paymentCardDefault' => $paymentCardDefault
                    ], 200);
                }

                BillingService::payDocumentFromUserBalance($document);
            }
            else {

                BillingService::manualPaymentDocument($document, $signed);
            }
        }
        catch(\Exception $e) {
            return response()->json([
                'status'=>'error',
                'errors' => [$e->getMessage()]
            ], 200);
        }
        $updatedDocument = $document->fresh();
        return response()->json([
            'status'=>'success',
            'document' => $updatedDocument,
            'client' => $updatedDocument->client,
        ], 200);
    }

    /**
     * Клиент ставит подпись документа
     *
     * @param Request $request
     * @param Document $document
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSigned(Request $request, Document $document)
    {
        $currUser = Auth::user();
        $document = Document::whereId($document->id)->with('client')->with('manager')->first();
        if ($currUser->id !== $document->client->id) {
            abort(403);
        }

        if (!$request->has('signed')) {
            return response()->json([
                'status'=>'error',
                'errors' => ['Bad data provided.']
            ], 200);
        }
        // усли уже подписан то просто возврашаем документ
        if ($document->signed) {
            return response()->json([
                'status'=>'success',
                'document' => $document,
                'client' => $document->client
            ], 200);
        }

        $signed = $request->has('signed') ? 1 : 0;
        $paid = $document->paid;

        $document->signed = $signed;
        $document->save();
        $document->history()->create([
            'user_id'=>$currUser->id,
            'signed'=>$signed,
            'paid'=>$paid,
        ]);

        return response()->json([
            'status'=>'success',
            'document' => $document,
            'client' => $document->client
        ], 200);
    }
}
