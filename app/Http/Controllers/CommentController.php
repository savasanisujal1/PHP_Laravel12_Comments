<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // LIST + SEARCH + PAGINATION
    public function index(Request $request)
    {
        $search = $request->search;

        $comments = Comment::whereNull('parent_id')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('comment', 'like', "%$search%");
            })
            ->with('replies')
            ->latest()
            ->paginate(5);

        return view('comments.index', compact('comments', 'search'));
    }

    // STORE COMMENT / REPLY
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        Comment::create($request->all());

        return redirect()->back()->with('success', 'Comment posted successfully!');
    }

    // DELETE COMMENT
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // delete replies first
        Comment::where('parent_id', $id)->delete();

        // delete main comment
        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully!');
    }
}