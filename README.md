# PHP_Laravel12_Comments

## Introduction

PHP_Laravel12_Comments is a modern Laravel 12 project that demonstrates how to implement a fully functional comment system. Users can:

- Post new comments

- Reply to existing comments (nested replies)

- View all comments in a threaded, organized structure

This project is designed to showcase clean Laravel project structure, proper Eloquent relationships, and Blade template usage, while also demonstrating basic validation and session-based feedback.

The system is fully responsive and styled using Tailwind CSS, with dark mode support for a modern user experience.

---

## Project Overview

The PHP_Laravel12_Comments project demonstrates the following core concepts:

1) Laravel 12 Setup – A clean and organized Laravel project structure.

2) Database Migrations – Creating the comments table with support for nested replies.

3) Eloquent Models & Relationships –

- Comment model with replies() and parent() relationships

- One-to-many relationships to handle replies

4) Database Operations – Creating and displaying top-level comments and their nested replies.

5) Blade Templates – Rendering threaded comments and reply forms dynamically.

6) Form Validation & Session Feedback – Ensuring proper user input with informative messages.

7) Responsive & Modern UI – Using Tailwind CSS for a professional, mobile-friendly layout.

8) Dark Mode Support – Compatible with both light and dark themes.

9) Nested Comment Functionality – Supports unlimited levels of nested replies.

This project is ideal for beginners and intermediate Laravel developers who want to implement real-world comment/reply functionality in their applications.

---

## Requirements

- PHP >= 8.1

- Laravel 12

- Composer

- MySQL

- Node.js & NPM (for compiling frontend assets if needed)

---

## Step 1: Create the Laravel 12 Project

Open your terminal and run:

```bash
composer create-project laravel/laravel PHP_Laravel12_Comments "12.*"
cd PHP_Laravel12_Comments
```

This creates a new Laravel 12 project named PHP_Laravel12_Comments.

---

## Step 2: Set up the Database

Update the .env file:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_comments
DB_USERNAME=root
DB_PASSWORD=
```

Then Run Migration Command:

```bash
php artisan migrate
```

---

## Step 3: Create the Comment Model & Migration

Run the artisan command:

```bash
php artisan make:model Comment -m
```

This will create:

- app/Models/Comment.php

- database/migrations/xxxx_xx_xx_create_comments_table.php

---

## Step 4: Update Migration

Open the migration file and update it as follows:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the commenter
            $table->text('comment'); // Comment content
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete(); // For replies
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
```
Explanation:

- parent_id allows nested comments (replies).

- nullOnDelete() ensures that if the parent comment is deleted, replies are not automatically deleted.

---

## Step 5: Run Migrations

```bash
php artisan migrate
```

---

## Step 6: Define Relationships in Comment Model

Open app/Models/Comment.php:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'comment', 'parent_id'];

    // Relationship for replies
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // Relationship for parent comment
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
}
```
Explanation:

- replies() fetches all nested comments.

- parent() fetches the parent comment if it exists.

---

## Step 7: Create Comment Controller

```bash
php artisan make:controller CommentController
```

Open app/Http/Controllers/CommentController.php:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::whereNull('parent_id')->with('replies')->latest()->get();
        return view('comments.index', compact('comments'));
    }

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
}
```
Explanation:

- index() fetches top-level comments with their replies.

- store() saves new comments or replies and validates the input.

---

## Step 8: Create Routes

Open routes/web.php:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;

Route::get('/', [CommentController::class, 'index']);
Route::post('/comment', [CommentController::class, 'store'])->name('comment.store');
```

---

## Step 9: Create Blade View

Create resources/views/comments/index.blade.php:

```blade
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Comments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar for comment section */
        .comments-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .comments-scroll::-webkit-scrollbar-thumb {
            background-color: #9ca3af;
            border-radius: 3px;
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 font-sans">

    <div class="max-w-4xl mx-auto p-6">

        <!-- Page Header -->
        <header class="mb-8 text-center">
            <h1 class="text-3xl md:text-4xl font-extrabold text-blue-700 dark:text-blue-500">Comments & Discussions</h1>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Share your thoughts and reply to others.</p>
        </header>

        <!-- Success Message -->
        @if(session('success'))
        <div class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 p-4 rounded-md mb-6 shadow-md">
            {{ session('success') }}
        </div>
        @endif

        <!-- Comment Form -->
        <section class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Post a Comment</h2>
            <form action="{{ route('comment.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="text" name="name" placeholder="Your Name" class="w-full p-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <textarea name="comment" placeholder="Write your comment..." rows="4" class="w-full p-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                <input type="hidden" name="parent_id" value="">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition duration-200">Post Comment</button>
            </form>
        </section>

        <!-- Comments List -->
        <section class="comments-scroll space-y-6 max-h-[70vh] overflow-y-auto">
            @foreach($comments as $comment)
            <div class="bg-gray-50 dark:bg-gray-800 shadow-sm rounded-lg p-5 space-y-3 border border-gray-200 dark:border-gray-700">
                <!-- Comment Content -->
                <div>
                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $comment->name }}</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ $comment->comment }}</p>
                </div>

                <!-- Reply Form -->
                <form action="{{ route('comment.store') }}" method="POST" class="mt-3 space-y-2 bg-gray-100 dark:bg-gray-900 p-3 rounded-lg">
                    @csrf
                    <input type="text" name="name" placeholder="Your Name" class="w-full p-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    <input type="text" name="comment" placeholder="Reply..." class="w-full p-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <button type="submit" class="bg-gray-700 hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 text-white px-4 py-2 rounded-md text-sm transition duration-200">Reply</button>
                </form>

                <!-- Nested Replies -->
                @foreach($comment->replies as $reply)
                <div class="ml-8 mt-4 bg-gray-100 dark:bg-gray-900 rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $reply->name }}</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ $reply->comment }}</p>
                </div>
                @endforeach
            </div>
            @endforeach
        </section>

    </div>

</body>

</html>
```

Explanation:

Top-level comments are displayed with nested replies indented.

Each comment has its own reply form.

---

## Step 10: Test the Project

Start the development server:

```bash
php artisan serve
```

Open: 

```bash
http://127.0.0.1:8000
```

- Add a comment and reply to see the system working.

---

## Output

<img src="screenshots/Screenshot 2026-03-16 170901.png" width="1000">

<img src="screenshots/Screenshot 2026-03-16 170916.png" width="1000">

<img src="screenshots/Screenshot 2026-03-16 171016.png" width="1000">

<img src="screenshots/Screenshot 2026-03-16 171033.png" width="1000">

---

## Project Structure

```
PHP_Laravel12_Comments/
├─ app/
│  ├─ Models/
│  │  └─ Comment.php
│  └─ Http/
│     └─ Controllers/
│        └─ CommentController.php
├─ database/
│  └─ migrations/
│     └─ xxxx_create_comments_table.php
├─ resources/
│  └─ views/
│     └─ comments/
│        └─ index.blade.php
├─ routes/
│  └─ web.php
└─ .env
```

---

Your PHP_Laravel12_Comments Project is now ready!




<<<<<<< HEAD


=======
>>>>>>> development
