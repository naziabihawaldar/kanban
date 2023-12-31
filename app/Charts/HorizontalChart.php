<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Board;
class HorizontalChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): array
    {
        $projects = Board::select('id','title')->orderBy('created_at','desc')->withCount('cards')->get();
        $titles = $cardCounts = [];
        $i=0;
        foreach ($projects as $project) 
        {
            $titles[$i] = $project->title;
            $cardCounts[$i] = $project->cards_count;
            $i++;
        }
        return $this->chart->horizontalBarChart()
        ->setTitle('No. of task in project')
        ->setSubtitle('Total no. of task in a project')
            ->setColors(['#FFC107', '#D32F2F'])
            ->addData('', $cardCounts)
            // ->setXAxis(['January', 'February', 'March', 'April', 'May', 'June'])
            ->setXAxis($titles)
            ->toVue();
    }
}
