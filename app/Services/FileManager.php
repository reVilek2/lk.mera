<?php
namespace App\Services;


use App\Models\File;
use Illuminate\Http\UploadedFile;

class FileManager
{
    private $image_ext = ['jpg', 'jpeg', 'png', 'gif'];
    private $audio_ext = ['mp3', 'ogg', 'mpga'];
    private $video_ext = ['mp4', 'mpeg'];
    private $document_ext = ['doc', 'docx', 'pdf', 'odt'];

    public function getFileData(UploadedFile $file)
    {
        $full_name = $file->getClientOriginalName();
        $pieces = explode(".", $full_name);
        $dir = $this->getDir();

        $fileData['ext'] = $file->getClientOriginalExtension();
        $fileData['type'] = $this->getType($fileData['ext']);
        $fileData['name'] = $pieces[0];
        $fileData['path'] = '/' . $dir. '/';
        $fileData['size'] = $file->getSize();

        return $fileData;
    }

    /**
     * Get type by extension
     * @param string $ext Specific extension
     * @return string   Type
     */
    public function getType($ext)
    {
        if (in_array($ext, $this->image_ext)) {
            return 'image';
        }

        if (in_array($ext, $this->audio_ext)) {
            return 'audio';
        }

        if (in_array($ext, $this->video_ext)) {
            return 'video';
        }

        if (in_array($ext, $this->document_ext)) {
            return 'document';
        }
    }

    public function getDir()
    {
        $AutoIncrementId = File::nextAutoIncrementId();
        return $this->generateFolderName($AutoIncrementId);
    }

    /**
     * @param $id
     * @param int $digit_number
     * @return string
     */
    public function generateFolderName($id, $digit_number=5): string
    {
        return sprintf('%0'.$digit_number.'s', (int)(($id - 1) / 1000) +1);
    }
}