<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/** Image d'illustration d'un jeu ou d'un monde, sur le disque des fichiers (dossier « images »). */
trait HasLibraryImage
{
    public const IMAGE_DISK = 'local';

    protected static function bootHasLibraryImage(): void
    {
        static::deleting(fn (self $model) => $model->deleteImage());
    }

    public function hasImage(): bool
    {
        return $this->image_path !== null;
    }

    public function deleteImage(): void
    {
        if ($this->image_path !== null) {
            Storage::disk(self::IMAGE_DISK)->delete($this->image_path);
            $this->image_path = null;
        }
    }
}
