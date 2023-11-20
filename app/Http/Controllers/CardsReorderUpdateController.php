<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardsReorderUpdateRequest;
use App\Models\Card;
use App\Models\Column;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Http\Request;

class CardsReorderUpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(CardsReorderUpdateRequest $request): RedirectResponse
    {
        logger("***********************************");
        logger($request);
        logger("***********************************");
    }
    public function __invokeOLD(CardsReorderUpdateRequest $request): RedirectResponse
    {
        $data = collect($request->columns)
            ->recursive() // make all nested arrays a collection
            ->map(function ($column) {
                return $column['cards']->map(function ($card) use ($column) {
                    $card_obj = Card::find($card['id']);                    
                    $col = Column::find($column["id"]);                   
                    activity('change_status')
                    ->performedOn($card_obj) // Entry add in table. model name(subject_type) & id(subject_id)
                    ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                    ->log(Auth::user()->name.' changed the status to '.$col->title);
                    return ['id' => $card['id'], 'position' => $card['position'], 'column_id' => $column['id']];
                });
            })
            ->flatten(1)
            ->toArray();
        logger($data);
        // Batch
        Card::query()->upsert($data, ['id'], ['position', 'column_id']);
        
        return back();
    }

    public function cardReorder(Request $request)
    {
        $datas = $request->columns;
        foreach ($datas as $data) 
        {
            $card = Card::find($data['card_id']);
            if($card)
            {
                $card->column_id = $data['column_id'];
                $card->position = $data['position'];
                $card->save();
            } 
        }
        return back();
    }
}
