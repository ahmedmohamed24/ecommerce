<?php
namespace App\Http\Traits;

use Illuminate\Foundation\Mix;

trait CustomUpload
{
    /**
     * get the file to upload and the type for example (product|category)
     *
     * @param mixed $img
     * @param string $folder
     * @return string|null
     */
    public function upload($img, $folder): string | null
    {
        if (!$img) {
            return null;
        }
        $ext=$img->getClientOriginalExtension();
        $newImageName="$folder-".now().uniqid().".$ext";
        $img->move(public_path("uploads/$folder"), $newImageName);
        return "/uploads/$folder/$newImageName";
    }
}
