<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Services\DocumentManager;
use App\Services\FileManager;
use App\Services\Page;
use Illuminate\Http\Request;
use Storage;
use Validator;

class DocumentController extends Controller
{
    private $documentManager;
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * DocumentController constructor.
     * @param DocumentManager $documentManager
     * @param FileManager $fileManager
     */
    public function __construct(DocumentManager $documentManager, FileManager $fileManager)
    {
        $this->documentManager = $documentManager;
        $this->fileManager = $fileManager;
    }

    public function index(Request $request)
    {
        Page::setTitle('Документы | MeraCapital');
        Page::setDescription('Страница документов');

        $whiteListOrderColumns = [
            'id' => true,
            'name' => true,
            'amount' => true,
            'created_at' => true,
        ];
        $whiteListSearchColumns = [
            'name',
            'amount'
        ];
        $params = [
            'sort' => $request->has('column') ? $request->input('column') : null,
            'dir' => $request->has('dir') && $request->input('dir') === 'asc' ? 'asc' : 'desc',
            'search' => $request->has('column') && !empty($request->input('search')) ? $request->input('search') : null,
            'length' => $request->has('length')  ? (int) $request->input('length') : '10', //default 10
        ];

        $documents = $this->documentManager->getDocumentsWithOrderAndPagination($whiteListOrderColumns, $whiteListSearchColumns, $params);
        $managers = User::role(['admin', 'manager'])->with('clients')->get();

        if ($request->ajax()) {
            return response()->json([
                'documents' => $documents,
                'draw' => $request->input('draw')
            ], 200);
        }

        return view('documents.index', [
            'documents' => $documents->toJson(),
            'managers' => $managers->toJson()
        ]);
    }

    public function create(Request $request)
    {
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
            $fileData = $this->fileManager->getFileData($file);
            if (Storage::disk('documents')->exists($fileData['path'].$fileData['name'].'.'.$fileData['ext'])) {
                return response()->json([
                    'status'=>'error',
                    'errors' => ['file' => ['Файл с таким именем уже существует.']]
                ], 200);
            }
            if (Storage::disk('documents')->putFileAs($fileData['path'], $file, $fileData['name'].'.'.$fileData['ext'])) {
                $document = Document::Create([
                    'name'=> $request->input('name'),
                    'amount'=> $request->input('amount'),
                    'client_id' => $request->input('client'),
                    'manager_id' => $request->input('manager'),
                ]);
                $document->addFile($fileData);
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
}
