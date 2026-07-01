<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $sort = $request->get('sort', 'newest');
        $ip = $request->ip();

        $comments = Comment::whereNull('parent_id')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('comment', 'like', "%$search%");
            })
            ->withCount([
                'likes as likes_count' => fn ($q) => $q->where('type', 'like'),
                'likes as dislikes_count' => fn ($q) => $q->where('type', 'dislike'),
            ])
            ->with([
                'likes' => fn ($q) => $q->where('ip_address', $ip),
                'replies' => fn ($q) => $q->withCount([
                    'likes as likes_count' => fn ($qq) => $qq->where('type', 'like'),
                    'likes as dislikes_count' => fn ($qq) => $qq->where('type', 'dislike'),
                ])->with(['likes' => fn ($qq) => $qq->where('ip_address', $ip)]),
            ])
            ->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('comments.index', compact('comments', 'search', 'sort'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        Comment::create($request->only('name', 'email', 'comment', 'parent_id'));

        return redirect()->back()->with('success', 'Comment posted successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'comment' => 'required|string',
        ]);

        $comment = Comment::findOrFail($id);
        $comment->update($request->only('name', 'email', 'comment'));

        return redirect()->back()->with('success', 'Comment updated successfully!');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully!');
    }

    public function react(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:like,dislike',
        ]);

        $comment = Comment::findOrFail($id);
        $ip = $request->ip();

        $existing = CommentLike::where('comment_id', $comment->id)
            ->where('ip_address', $ip)
            ->first();

        if ($existing && $existing->type === $request->type) {
            $existing->delete();
        } elseif ($existing) {
            $existing->update(['type' => $request->type]);
        } else {
            CommentLike::create([
                'comment_id' => $comment->id,
                'ip_address' => $ip,
                'type' => $request->type,
            ]);
        }

        return response()->json([
            'likes' => CommentLike::where('comment_id', $comment->id)->where('type', 'like')->count(),
            'dislikes' => CommentLike::where('comment_id', $comment->id)->where('type', 'dislike')->count(),
            'user_vote' => CommentLike::where('comment_id', $comment->id)->where('ip_address', $ip)->value('type'),
        ]);
    }
}