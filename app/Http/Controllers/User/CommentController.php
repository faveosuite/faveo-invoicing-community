<?php

namespace App\Http\Controllers\User;

use App\Comment;
use App\Http\Controllers\Controller;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Lang;
use Logger;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $user = new User();
        $this->user = $user; // @phpstan-ignore property.notFound

        $comment = new Comment();
        $this->comment = $comment; // @phpstan-ignore property.notFound
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): void
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): mixed
    {
        try {
            $comments = $this->comment->fill($request->input())->save(); // @phpstan-ignore property.notFound

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return back()->with('fails', $exception->getMessage());
        }
    }
}
