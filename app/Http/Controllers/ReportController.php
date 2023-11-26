<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        return inertia("Report");
    }


   public function getReportsData(Request $request)
   {
    try 
    {
        $reports = Card::with('users','board')->latest()->paginate($request->rowsPerPage);
        return ['status' => 'success','message' =>'success','data' => $reports];
    } catch (\Exception $e) {
        logger($e);
        return ['status'=> 'error','message'=> 'Error Occurred'];
    }

   }

   public function export(Request $request) 
    {
        return (new ReportExport($request))->download('reports.csv');
        // return Excel::download(new ReportExport($request), 'report.xlsx');
    }
}
