<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $guarded = [];
    protected $fillable = ['name', 'price', 'category'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image'); // Ensure this is the collection name you are using
    }

    public function carts()
    {
        return $this->hasMany(cart::class, 'product_id');
    }
}
