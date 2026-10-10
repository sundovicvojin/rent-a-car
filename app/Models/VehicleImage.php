<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;

class VehicleImage extends Model
{
    protected $fillable = ['vehicle_id', 'path', 'alt', 'is_primary', 'is_placeholder', 'sort_order'];

    protected static function booted(): void
    {
        static::saved(function (self $image) {
            if ($image->wasRecentlyCreated || $image->wasChanged('path')) {
                app(ImageService::class)->generateVariants($image->path);
            }
            if ($image->is_primary) {
                static::where('vehicle_id', $image->vehicle_id)->where('id', '!=', $image->id)->update(['is_primary' => false]);
            }
        });

        static::deleted(function (self $image) {
            app(ImageService::class)->deleteVariants($image->path);
            Storage::disk('public')->delete($image->path);
        });
    }

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'is_placeholder' => 'boolean'];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Putanja varijante (npr. 'sm', 'md', 'lg'); vidi App\Services\ImageService. */
    public function url(string $size = 'lg'): string
    {
        $variant = app(ImageService::class)->variantPath($this->path, $size);

        return Storage::disk('public')->exists($variant)
            ? Storage::disk('public')->url($variant)
            : Storage::disk('public')->url($this->path);
    }

    /** srcset sa WebP varijantama; prazan string ako varijante ne postoje. */
    public function srcset(): string
    {
        $service = app(ImageService::class);
        $parts = [];
        foreach (ImageService::SIZES as $size => $width) {
            $variant = $service->variantPath($this->path, $size);
            if (Storage::disk('public')->exists($variant)) {
                $parts[] = Storage::disk('public')->url($variant).' '.$width.'w';
            }
        }

        return implode(', ', $parts);
    }
}
