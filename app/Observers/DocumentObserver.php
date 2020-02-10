<?php

namespace App\Observers;

use App\Models\Document;

class DocumentObserver
{
    /**
     * Handle the document "created" event.
     *
     * @param  \App\Document  $document
     * @return void
     */
    public function created(Document $document)
    {
        //
    }

    /**
     * Handle the document "updated" event.
     *
     * @param  \App\Document  $document
     * @return void
     */
    public function updated(Document $document)
    {
        //
    }

    /**
     * Handle the document "deleted" event.
     *
     * @param  \App\Document  $document
     * @return void
     */
    public function deleted(Document $document)
    {
        $files = $document->files;
        foreach($files as $file){
            $file->delete();
        }
    }

    /**
     * Handle the document "restored" event.
     *
     * @param  \App\Document  $document
     * @return void
     */
    public function restored(Document $document)
    {
        //
    }

    /**
     * Handle the document "force deleted" event.
     *
     * @param  \App\Document  $document
     * @return void
     */
    public function forceDeleted(Document $document)
    {
        //
    }
}
