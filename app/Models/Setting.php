<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /** Get a setting value (JSON-decoded), or $default if missing. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::find($key);
        if (! $row) {
            return $default;
        }
        $decoded = json_decode((string) $row->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;
    }

    /** Store a setting value (any JSON-encodable type). */
    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => json_encode($value)]);
    }

    /** Safe to call during boot: returns [] if the table isn't migrated yet. */
    public static function allSafely(): array
    {
        if (! Schema::hasTable('settings')) {
            return [];
        }

        return static::all()->mapWithKeys(fn ($r) => [
            $r->key => json_decode((string) $r->value, true),
        ])->all();
    }
}
