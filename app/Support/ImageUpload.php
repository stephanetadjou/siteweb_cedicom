<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Images envoyées depuis le back office : redimensionnées (GD) puis rangées dans
 * storage/app/public/uploads, servies par /storage (php artisan storage:link).
 * Les images livrées avec le site (public/images) ne sont jamais supprimées.
 */
class ImageUpload
{
    public const PREFIX = 'storage/uploads/';

    /** @return string chemin public, ex. storage/uploads/marques/coca-cola-ab12cd.png */
    public static function store(UploadedFile $file, string $folder, int $maxWidth): string
    {
        $extension = strtolower($file->extension()) === 'jpeg' ? 'jpg' : strtolower($file->extension());
        $name = (Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image').'-'.Str::lower(Str::random(6)).'.'.$extension;
        $path = "uploads/$folder/$name";

        Storage::disk('public')->put($path, self::resized($file->getRealPath(), $extension, $maxWidth) ?? file_get_contents($file->getRealPath()));

        return 'storage/'.$path;
    }

    public static function delete(?string $publicPath): void
    {
        if ($publicPath && str_starts_with($publicPath, self::PREFIX)) {
            Storage::disk('public')->delete(Str::after($publicPath, 'storage/'));
        }
    }

    /** Image réduite à $maxWidth px de large (contenu du fichier), ou null si inutile ou impossible. */
    private static function resized(string $source, string $extension, int $maxWidth): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! ($info = @getimagesize($source)) || $info[0] <= $maxWidth) {
            return null;
        }

        $image = @imagecreatefromstring(file_get_contents($source));
        if (! $image) {
            return null;
        }

        $height = (int) round($info[1] * $maxWidth / $info[0]);
        $resized = imagecreatetruecolor($maxWidth, $height);
        imagealphablending($resized, false);
        imagesavealpha($resized, true); // garde la transparence des logos PNG / WebP
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $height, $info[0], $info[1]);

        ob_start();
        match ($extension) {
            'png' => imagepng($resized, null, 8),
            'webp' => imagewebp($resized, null, 85),
            default => imagejpeg($resized, null, 85),
        };

        return ob_get_clean();
    }
}
