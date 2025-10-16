<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        "title",
        "description",
        "img",
        "liked_it",
        "count_likes",
        "post_maker_id",
    ];

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "post_maker_id");
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PivotLike::class, "post_id");
    }

}
