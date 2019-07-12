<?php
namespace App\Services;


use App\Models\File;
use Illuminate\Http\UploadedFile;
use Storage;

class FileManager
{
    public function getFileData(UploadedFile $file, $baseDir = '/', $name = '')
    {
        $full_name = $file->getClientOriginalName();
        $pieces = explode(".", $full_name);
        $unique_name = $this->getUniqueFileName($full_name, $baseDir);

        $fileData['type'] = $file->getMimeType();
        $fileData['origin_name'] = empty($name) ? $full_name : $name . "." . end($pieces);
        $fileData['name'] = $unique_name . "." . end($pieces);
        $fileData['path'] = $baseDir;
        $fileData['size'] = $file->getSize();

        return $fileData;
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

    /**
     * @param $file_name
     * @param $destination_folder
     * @return string
     */
    public function getUniqueFileName($file_name, $destination_folder)
    {
        $random_string = $this->getRandomString(8);
        $unique_name = md5($file_name. $random_string . time());
        // Check if file with new name already exists
        if(file_exists($destination_folder.'/'.$unique_name)) {
            // If exists try again
            $this->getUniqueFileName($file_name, $destination_folder);
        }

        return $unique_name;
    }


    /**
     * @param $type
     * @return bool|mixed
     */
    public function isFileTypeDisplayable($type) {

        $type_display = array(
            'text/plain' => false,
            'text/html'  => false,
            'text/css' => false,
            'application/javascript' => false,
            'application/json' => false,
            'application/xml' => false,
            'application/x-shockwave-flash' => false,
            'video/x-flv' => false,

            // images
            'image/png' => true,
            'image/jpeg'  => true,
            'image/gif'  => true,
            'image/bmp'  => true,
            'image/vnd.microsoft.icon'  => true,
            'image/tiff'  => true,
            'image/svg+xml'  => true,

            // archives
            'application/zip'  => false,
            'application/x-rar-compressed'  => false,
            'application/x-msdownload'  => false,
            'application/vnd.ms-cab-compressed'  => false,

            // audio/video
            'audio/mpeg'  => false,
            'video/quicktime'  => false,
            'video/mp4'  => false,

            // adobe
            'application/pdf' => true,
            'image/vnd.adobe.photoshop' => false,
            'application/postscript' => false,

            // ms office
            'application/msword' => false,
            'application/rtf' => false,
            'application/vnd.ms-excel' => false,
            'application/vnd.ms-powerpoint' => false,

            // open office
            'application/vnd.oasis.opendocument.text' => false,
            'application/vnd.oasis.opendocument.spreadsheet' => false,
        );

        if (key_exists($type, $type_display)) {

            return $type_display[$type];
        }

        return false;
    }

    /**
     * @param File $file
     * @param string $disk
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function display(File $file, $disk='public')
    {
        if (!$this->isFileTypeDisplayable($file->type)) {
            throw new \Exception('File can not be displayed');
        }

        $content= Storage::disk($disk)->get($this->getFilePath($file));

        return response($content)
            ->header('Content-Transfer-Encoding', 'binary')
            ->header('Content-Type', $file->type);
    }

    /**
     * @param File $file
     * @param string $disk
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function download(File $file, $disk='public')
    {
        $content= Storage::disk($disk)->get($this->getFilePath($file));

        return response($content)
            ->header('Content-Type', $file->type)
            ->header('Content-Disposition', 'attachment; filename="' . $file->origin_name . '"')
            ->header('Content-Length', $file->size);
    }

    public function getFilePath(File $file)
    {
        return $file->path.'/'.$file->name;
    }

    /**
     * @param $length
     * @return string
     */
    private function getRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = strlen($characters);
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $len - 1)];
        }

        return $string;
    }
}