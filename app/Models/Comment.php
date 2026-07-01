<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'email', 'comment', 'parent_id'];

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function likes()
    {
        return $this->hasMany(CommentLike::class);
    }

    public function getGravatarAttribute()
    {
        $source = $this->email ?: $this->name;
        $hash = md5(strtolower(trim($source)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=80";
    }

    public function getRenderedCommentAttribute()
    {
        $text = e($this->comment);

        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer" class="underline text-blue-400">$1</a>', $text);
        $text = nl2br($text);

        return $text;
    }
}