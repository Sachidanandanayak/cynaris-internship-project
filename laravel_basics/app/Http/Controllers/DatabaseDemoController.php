<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DatabaseDemoController extends Controller
{
    /**
     * Display the database integration and Eloquent query demonstration.
     */
    public function index(Request $request): View
    {
        // 1. Eager Loading demonstration with('comments') and query logging
        DB::flushQueryLog();
        DB::enableQueryLog();
        $eagerLoadedPosts = Post::with('comments')->latest()->take(5)->get();
        $eagerQueryLog = DB::getQueryLog();
        DB::disableQueryLog();

        // 2. Demonstration of where() query
        $searchKeyword = $request->query('search', 'Laravel');
        $whereFilteredPosts = Post::where('title', 'like', "%{$searchKeyword}%")
            ->with('comments')
            ->get();

        // 3. Demonstration of orderBy() query
        $sortDirection = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $orderByPosts = Post::orderBy('title', $sortDirection)
            ->take(6)
            ->get();

        // Summary counts
        $totalPostsCount = Post::count();
        $totalCommentsCount = Comment::count();

        return view('database-demo', compact(
            'eagerLoadedPosts',
            'eagerQueryLog',
            'searchKeyword',
            'whereFilteredPosts',
            'sortDirection',
            'orderByPosts',
            'totalPostsCount',
            'totalCommentsCount'
        ));
    }
}
