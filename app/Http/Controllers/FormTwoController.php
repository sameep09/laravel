<?php

namespace App\Http\Controllers;

// use App\Models\FormTwo;
use Illuminate\Http\Request;

class FormTwoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('form_two.index');
    }
}
