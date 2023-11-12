<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCardRequest;
use App\Models\Card;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ColumnCardUpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function updateCard(UpdateCardRequest $request, Column $column, Card $card)
    {
        $card->update($request->all());
        return back();
        // logger($request);
    }
}
