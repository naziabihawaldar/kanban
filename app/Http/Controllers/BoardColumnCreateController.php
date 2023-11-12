<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ColumnResource;
use App\Http\Requests\StoreColumnRequest;


class BoardColumnCreateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreColumnRequest $request, Board $board): RedirectResponse
    {
        $board->addColumn(Column::create($request->all()));

        activity('create')
        ->performedOn($board) // Entry add in table. model name(subject_type) & id(subject_id)
        ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
        ->log('A New Column '.$request->title.' is Created on Board '.$board->title.' by ' . Auth::user()->name);
        return back();
    }
}
