<?php

namespace App\Charts;

use App\Models\Board;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class ProjectTaskChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): array
    {
        $projects = Board::all()->pluck('title')->toArray();
        return $this->chart->horizontalBarChart()
            ->setTitle('No. of task in project')
            ->setSubtitle('Total no. of task in a project')
            ->setColors(['#FFC107', '#D32F2F'])
            ->addData('San Francisco', [6, 9, 3, 4, 10, 8])
            // ->addData('Boston', [7, 3, 8, 2, 6, 4])
            ->setXAxis($projects)
            ->toVue();
        // return $this->chart->barChart()
        //     ->setTitle('No. of task in project')
        //     ->setSubtitle('Total no. of task in a project')
        //     ->setDataset([
        //         [
        //             'name'  => 'Testing',
        //             'data'  => [150, 200, 180],
        //         ],
        //     ])
        //     ->setXAxis($projects)
        //     ->toVue();
    }
}
