<?php

namespace App\Charts;

use App\Models\Board;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class OverdueTaskChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): array
    {
        $overdue_projects = Board::whereHas('cards', function ($query) {
                                $query->where('task_status', 'overdue');
                            })->get();
        $projects = $counts = [];
        $i=0; 
        foreach ($overdue_projects as $overdue_project) 
        {
            $projects[$i] = $overdue_project->title;
            $counts[$i] = $overdue_project->cards()->where('task_status', 'overdue')->count();
            $i++;
        }
        return $this->chart->pieChart()
            ->setTitle('OverDue Task In Project')
            ->setSubtitle('No of overdue task in a project')
            ->addData($counts)
            ->setLabels($projects)
            ->toVue();
    }
}
