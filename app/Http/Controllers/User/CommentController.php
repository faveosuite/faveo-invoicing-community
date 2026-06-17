<?php

namespace App\Http\Controllers\User;

use App\Comment;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lang;
use Logger;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $user = new User();
        $this->user = $user;

        $comment = new Comment();
        $this->comment = $comment;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $comments = $this->comment->fill($request->input())->save();

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            Logger::exception($ex);

            return back()->with('fails', $ex->getMessage());
        }
    }
}
