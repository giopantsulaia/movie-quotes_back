<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request) :JsonResponse
    {
        $search = substr($request->search, 1);
        if ($request->search[0]=== '@') {
            $search = substr($request->search, 1);
            return response()->json(['movies'=>Movie::where(strtolower('title->en'), 'like', '%'. $search . '%')
            ->orWhere(strtolower('title->ka'), 'like', '%'. $search . '%')
            ->with('author')->with('genres')->with(['quotes'=>['comments.author','author','movie']])->get()], 200);
        } elseif ($request->search[0]==='#') {
            return response()->json(['quotes'=>Quote::where('body->en', 'like', '%' . $search . '%')
            ->orWhere('body->ka', 'like', '%' . $search . '%')
            ->with('movie')->with('author')->with('comments.author')->with('likes')->get()], 200);
        }
    }
}
