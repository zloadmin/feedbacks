<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    const COUNT_OF_REVIEWS = 10;
    use SoftDeletes;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = ['text'];
    public function checked()
    {
        $this->checked_at = now();
        $this->save();
    }
    public function scopeCheck($query)
    {
        return $query->whereNotNull('checked_at');
    }
    static function randomReview()
    {
        return self::select('text')->check()->inRandomOrder()->first();
    }
    static function randomReviews()
    {
        return self::select('id', 'text')->check()->inRandomOrder()->take(self::COUNT_OF_REVIEWS)->pluck('text', 'id');
    }
}
