<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An image stored in the database (base64), served at /media/{id}.
 */
class Media extends Model
{
    protected $fillable = ['mime', 'size', 'data'];

    protected $hidden = ['data'];

    /** Relative path to use as a product image (asset() turns it into a full URL). */
    public function path(): string
    {
        return 'media/'.$this->id;
    }
}
