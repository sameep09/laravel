<?php

namespace App\Http\Controllers;

// use App\Models\FormThree;
use Illuminate\Http\Request;

class FormThreeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('form_three.index');
    }
}
