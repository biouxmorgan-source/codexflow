<?php

namespace App\Livewire\Concerns;

use App\Models\GameSystem;
use App\Models\World;
use App\Support\Plans\Plans;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/** Image d'un jeu ou d'un monde : enregistrée dès qu'elle est choisie, comptée dans le stockage. */
trait EditsLibraryImage
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $image = null;

    abstract protected function libraryItem(): GameSystem|World;

    public function updatedImage(): void
    {
        $item = $this->libraryItem();
        abort_unless($item->user_id === auth()->id(), 403);

        $this->validate(
            ['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240']],
            attributes: ['image' => __('image')],
        );
        Plans::ensureRoom($item->owner, (int) $this->image->getSize(), 'image');

        $item->deleteImage();
        $item->image_path = $this->image->store('images', $item::IMAGE_DISK);
        $item->save();
        $this->image = null;
    }

    public function removeImage(): void
    {
        $item = $this->libraryItem();
        abort_unless($item->user_id === auth()->id(), 403);

        $item->deleteImage();
        $item->save();
    }
}
