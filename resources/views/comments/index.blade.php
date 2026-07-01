<!DOCTYPE html>
<html>

<head>
    <title>Comments Wall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>

    <style>
        body {
            background: radial-gradient(circle at top, #0f172a, #020617);
            font-family: ui-sans-serif, system-ui;
        }

        .card {
            break-inside: avoid;
            margin-bottom: 18px;
            transition: all 0.25s ease;
            position: relative;
        }

        .card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        h1 {
            background: linear-gradient(90deg, #60a5fa, #34d399, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        html {
            scroll-behavior: smooth;
        }

        .CodeMirror, .editor-toolbar {
            background: #1f2937 !important;
            color: #f3f4f6 !important;
            border-color: #374151 !important;
        }

        .editor-toolbar a {
            color: #f3f4f6 !important;
        }

        .vote-btn.active-like {
            color: #34d399 !important;
        }

        .vote-btn.active-dislike {
            color: #f87171 !important;
        }
    </style>
</head>

<body class="text-white min-h-screen">

<div class="max-w-6xl mx-auto p-6">

    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold">🧱 Comments Wall</h1>
        <p class="text-gray-400">Pinterest-style masonry layout</p>
    </div>

    @if(session('success'))
        <div class="bg-green-600 p-3 rounded mb-5">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="flex flex-col md:flex-row gap-2 mb-6">
        <input type="text"
               name="search"
               value="{{ $search ?? '' }}"
               placeholder="Search comments..."
               class="w-full p-3 rounded bg-gray-800 border border-gray-700">

        <select name="sort" class="p-3 rounded bg-gray-800 border border-gray-700" onchange="this.form.submit()">
            <option value="newest" {{ ($sort ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
            <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
        </select>

        <button class="bg-blue-600 px-5 rounded hover:bg-blue-700">
            Search
        </button>
    </form>

    <form action="{{ route('comment.store') }}" method="POST"
          class="bg-gray-900 p-5 rounded-xl mb-8 border border-gray-700">
        @csrf

        <div class="flex flex-col md:flex-row gap-3 mb-3">
            <input type="text"
                   name="name"
                   placeholder="Your Name"
                   required
                   class="w-full p-3 bg-gray-800 rounded text-white">

            <input type="email"
                   name="email"
                   placeholder="Your Email (for avatar)"
                   class="w-full p-3 bg-gray-800 rounded text-white">
        </div>

        <textarea name="comment"
                  id="main-editor"
                  placeholder="Write something..."
                  class="w-full p-3 mb-3 bg-gray-800 rounded text-white"></textarea>

        <input type="hidden" name="parent_id">

        <button class="bg-green-500 px-5 py-2 rounded hover:bg-green-600">
            Post
        </button>
    </form>

    <div class="columns-1 md:columns-2 lg:columns-3 gap-5">

        @foreach($comments as $comment)

            <div class="card bg-gray-900 p-4 rounded-xl shadow-lg border border-gray-700">

                <div class="flex justify-between gap-3">

                    <div class="flex gap-3">
                        <img src="{{ $comment->gravatar }}" class="w-10 h-10 rounded-full" alt="{{ $comment->name }}">

                        <div>
                            <h3 class="font-bold text-blue-400">
                                {{ $comment->name }}
                            </h3>

                            <p class="text-gray-500 text-xs">
                                {{ $comment->created_at->diffForHumans() }}
                            </p>

                            <div class="text-gray-300 mt-2 comment-body">
                                {!! $comment->rendered_comment !!}
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('comment.delete', $comment->id) }}"
                          method="POST"
                          onsubmit="return confirm('⚠️ Delete this comment?')">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="text-red-400 hover:text-red-600">
                            ✕
                        </button>
                    </form>

                </div>

                <div class="flex items-center gap-4 mt-3 text-sm">
                    <button type="button"
                            class="vote-btn flex items-center gap-1 text-gray-400 hover:text-green-400 {{ ($comment->likes->first()->type ?? null) === 'like' ? 'active-like' : '' }}"
                            onclick="react({{ $comment->id }}, 'like', this)">
                        👍 <span class="likes-count">{{ $comment->likes_count }}</span>
                    </button>

                    <button type="button"
                            class="vote-btn flex items-center gap-1 text-gray-400 hover:text-red-400 {{ ($comment->likes->first()->type ?? null) === 'dislike' ? 'active-dislike' : '' }}"
                            onclick="react({{ $comment->id }}, 'dislike', this)">
                        👎 <span class="dislikes-count">{{ $comment->dislikes_count }}</span>
                    </button>

                    <button type="button" class="text-gray-400 hover:text-blue-400"
                            onclick="toggleReplyForm({{ $comment->id }})">
                        Reply
                    </button>

                    <button type="button" class="text-gray-400 hover:text-yellow-400"
                            onclick="toggleEditForm({{ $comment->id }})">
                        Edit
                    </button>
                </div>

                <form id="edit-form-{{ $comment->id }}" action="{{ route('comment.update', $comment->id) }}"
                      method="POST" class="hidden mt-3 bg-gray-800 p-3 rounded-lg">
                    @csrf
                    @method('PUT')

                    <input type="text" name="name" value="{{ $comment->name }}" required
                           class="w-full p-2 mb-2 bg-gray-700 rounded text-white text-sm">

                    <input type="email" name="email" value="{{ $comment->email }}" placeholder="Email"
                           class="w-full p-2 mb-2 bg-gray-700 rounded text-white text-sm">

                    <textarea name="comment" required
                              class="w-full p-2 mb-2 bg-gray-700 rounded text-white text-sm">{{ $comment->comment }}</textarea>

                    <button class="bg-yellow-500 px-4 py-1 rounded text-sm hover:bg-yellow-600">
                        Save
                    </button>
                </form>

                <form id="reply-form-{{ $comment->id }}" action="{{ route('comment.store') }}"
                      method="POST" class="hidden mt-3 bg-gray-800 p-3 rounded-lg">
                    @csrf

                    <input type="text" name="name" placeholder="Your Name" required
                           class="w-full p-2 mb-2 bg-gray-700 rounded text-white text-sm">

                    <input type="email" name="email" placeholder="Your Email (for avatar)"
                           class="w-full p-2 mb-2 bg-gray-700 rounded text-white text-sm">

                    <textarea name="comment" placeholder="Write a reply..." required
                              class="w-full p-2 mb-2 bg-gray-700 rounded text-white text-sm"></textarea>

                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">

                    <button class="bg-green-500 px-4 py-1 rounded text-sm hover:bg-green-600">
                        Reply
                    </button>
                </form>

                <div class="mt-4 space-y-3">

                    @foreach($comment->replies as $reply)

                        <div class="bg-gray-800 p-3 rounded-lg">

                            <div class="flex justify-between gap-3">

                                <div class="flex gap-2">
                                    <img src="{{ $reply->gravatar }}" class="w-8 h-8 rounded-full" alt="{{ $reply->name }}">

                                    <div>
                                        <p class="text-green-400 font-semibold text-sm">
                                            {{ $reply->name }}
                                        </p>

                                        <p class="text-gray-500 text-xs">
                                            {{ $reply->created_at->diffForHumans() }}
                                        </p>

                                        <div class="text-gray-200 text-sm mt-1">
                                            {!! $reply->rendered_comment !!}
                                        </div>
                                    </div>
                                </div>

                                <form action="{{ route('comment.delete', $reply->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('⚠️ Delete this reply?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="text-red-300 text-xs">
                                        ✕
                                    </button>
                                </form>

                            </div>

                            <div class="flex items-center gap-3 mt-2 text-xs">
                                <button type="button"
                                        class="vote-btn flex items-center gap-1 text-gray-400 hover:text-green-400 {{ ($reply->likes->first()->type ?? null) === 'like' ? 'active-like' : '' }}"
                                        onclick="react({{ $reply->id }}, 'like', this)">
                                    👍 <span class="likes-count">{{ $reply->likes_count }}</span>
                                </button>

                                <button type="button"
                                        class="vote-btn flex items-center gap-1 text-gray-400 hover:text-red-400 {{ ($reply->likes->first()->type ?? null) === 'dislike' ? 'active-dislike' : '' }}"
                                        onclick="react({{ $reply->id }}, 'dislike', this)">
                                    👎 <span class="dislikes-count">{{ $reply->dislikes_count }}</span>
                                </button>

                                <button type="button" class="text-gray-400 hover:text-yellow-400"
                                        onclick="toggleEditForm({{ $reply->id }})">
                                    Edit
                                </button>
                            </div>

                            <form id="edit-form-{{ $reply->id }}" action="{{ route('comment.update', $reply->id) }}"
                                  method="POST" class="hidden mt-2 bg-gray-700 p-2 rounded-lg">
                                @csrf
                                @method('PUT')

                                <input type="text" name="name" value="{{ $reply->name }}" required
                                       class="w-full p-2 mb-2 bg-gray-600 rounded text-white text-xs">

                                <input type="email" name="email" value="{{ $reply->email }}" placeholder="Email"
                                       class="w-full p-2 mb-2 bg-gray-600 rounded text-white text-xs">

                                <textarea name="comment" required
                                          class="w-full p-2 mb-2 bg-gray-600 rounded text-white text-xs">{{ $reply->comment }}</textarea>

                                <button class="bg-yellow-500 px-3 py-1 rounded text-xs hover:bg-yellow-600">
                                    Save
                                </button>
                            </form>

                        </div>

                    @endforeach

                </div>

            </div>

        @endforeach

    </div>

    <div class="mt-8 flex justify-center">
        <div class="bg-gray-800 p-3 rounded">
            {{ $comments->links() }}
        </div>
    </div>

</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || '{{ csrf_token() }}';

    new SimpleMDE({ element: document.getElementById('main-editor') });

    function toggleReplyForm(id) {
        const form = document.getElementById('reply-form-' + id);
        form.classList.toggle('hidden');
    }

    function toggleEditForm(id) {
        const form = document.getElementById('edit-form-' + id);
        form.classList.toggle('hidden');
    }

    async function react(id, type, btn) {
        try {
            const res = await fetch(`/comment/${id}/react`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ type })
            });

            const data = await res.json();

            const wrapper = btn.closest('.flex.items-center.gap-4, .flex.items-center.gap-3');
            wrapper.querySelector('.likes-count').textContent = data.likes;
            wrapper.querySelector('.dislikes-count').textContent = data.dislikes;

            wrapper.querySelectorAll('.vote-btn').forEach(b => {
                b.classList.remove('active-like', 'active-dislike');
            });

            if (data.user_vote === 'like') {
                wrapper.querySelector('.vote-btn:nth-child(1)').classList.add('active-like');
            } else if (data.user_vote === 'dislike') {
                wrapper.querySelector('.vote-btn:nth-child(2)').classList.add('active-dislike');
            }
        } catch (error) {
            alert('Something went wrong, please try again.');
        }
    }
</script>

</body>
</html>