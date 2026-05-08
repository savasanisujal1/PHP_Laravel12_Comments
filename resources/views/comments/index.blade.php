<!DOCTYPE html>
<html>

<head>
    <title>Comments Wall</title>
    <script src="https://cdn.tailwindcss.com"></script>

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
    </style>
</head>

<body class="text-white min-h-screen">

<div class="max-w-6xl mx-auto p-6">

    <!-- HEADER -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold">🧱 Comments Wall</h1>
        <p class="text-gray-400">Pinterest-style masonry layout</p>
    </div>

    <!-- SUCCESS ALERT -->
    @if(session('success'))
        <div class="bg-green-600 p-3 rounded mb-5">
            {{ session('success') }}
        </div>
    @endif

    <!-- SEARCH -->
    <form method="GET" class="flex gap-2 mb-6">
        <input type="text"
               name="search"
               value="{{ $search ?? '' }}"
               placeholder="Search comments..."
               class="w-full p-3 rounded bg-gray-800 border border-gray-700">

        <button class="bg-blue-600 px-5 rounded hover:bg-blue-700">
            Search
        </button>
    </form>

    <!-- COMMENT FORM -->
    <form action="{{ route('comment.store') }}" method="POST"
          class="bg-gray-900 p-5 rounded-xl mb-8 border border-gray-700">
        @csrf

        <input type="text"
               name="name"
               placeholder="Your Name"
               class="w-full p-3 mb-3 bg-gray-800 rounded text-white">

        <textarea name="comment"
                  placeholder="Write something..."
                  class="w-full p-3 mb-3 bg-gray-800 rounded text-white"></textarea>

        <input type="hidden" name="parent_id">

        <button class="bg-green-500 px-5 py-2 rounded hover:bg-green-600">
            Post
        </button>
    </form>

    <!-- MASONRY GRID -->
    <div class="columns-1 md:columns-2 lg:columns-3 gap-5">

        @foreach($comments as $comment)

            <div class="card bg-gray-900 p-4 rounded-xl shadow-lg border border-gray-700">

                <!-- MAIN COMMENT -->
                <div class="flex justify-between">

                    <div>
                        <h3 class="font-bold text-blue-400">
                            {{ $comment->name }}
                        </h3>

                        <p class="text-gray-300 mt-2">
                            {{ $comment->comment }}
                        </p>
                    </div>

                    <!-- DELETE MAIN COMMENT (FIXED) -->
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

                <!-- REPLIES -->
                <div class="mt-4 space-y-3">

                    @foreach($comment->replies as $reply)

                        <div class="bg-gray-800 p-3 rounded-lg">

                            <div class="flex justify-between">

                                <div>
                                    <p class="text-green-400 font-semibold text-sm">
                                        {{ $reply->name }}
                                    </p>

                                    <p class="text-gray-200 text-sm">
                                        {{ $reply->comment }}
                                    </p>
                                </div>

                                <!-- DELETE REPLY (FIXED) -->
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

                        </div>

                    @endforeach

                </div>

            </div>

        @endforeach

    </div>

    <!-- PAGINATION -->
    <div class="mt-8 flex justify-center">
        <div class="bg-gray-800 p-3 rounded">
            {{ $comments->links() }}
        </div>
    </div>

</div>

</body>
</html>