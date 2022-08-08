<?php

namespace App\Http\Controllers\Admin;

use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class HomeController
{
    public function index()
    {
        $settings1 = [
            'chart_title'           => 'Total Pendaftar',
            'chart_type'            => 'line',
            'report_type'           => 'group_by_date',
            'model'                 => 'App\Models\Pendaftar',
            'group_by_field'        => 'created_at',
            'group_by_period'       => 'day',
            'aggregate_function'    => 'count',
            'filter_field'          => 'created_at',
            'filter_days'           => '7',
            'group_by_field_format' => 'd-m-Y H:i:s',
            'column_class'          => 'col-md-6',
            'entries_number'        => '5',
            'translation_key'       => 'pendaftar',
        ];

        $chart1 = new LaravelChart($settings1);

        $settings2 = [
            'chart_title'           => 'Total Pendaftar',
            'chart_type'            => 'bar',
            'report_type'           => 'group_by_date',
            'model'                 => 'App\Models\Pendaftar',
            'group_by_field'        => 'created_at',
            'group_by_period'       => 'day',
            'aggregate_function'    => 'count',
            'filter_field'          => 'created_at',
            'filter_days'           => '7',
            'group_by_field_format' => 'd-m-Y H:i:s',
            'column_class'          => 'col-md-6',
            'entries_number'        => '5',
            'translation_key'       => 'pendaftar',
        ];

        $chart2 = new LaravelChart($settings2);

        $settings3 = [
            'chart_title'        => 'Profit',
            'chart_type'         => 'line',
            'report_type'        => 'group_by_string',
            'model'              => 'App\Models\Event',
            'group_by_field'     => 'harga',
            'aggregate_function' => 'count',
            'filter_field'       => 'created_at',
            'filter_days'        => '7',
            'column_class'       => 'col-md-6',
            'entries_number'     => '5',
            'translation_key'    => 'event',
        ];

        $chart3 = new LaravelChart($settings3);

        $settings4 = [
            'chart_title'           => 'Pendaftar Baru',
            'chart_type'            => 'latest_entries',
            'report_type'           => 'group_by_date',
            'model'                 => 'App\Models\Pendaftar',
            'group_by_field'        => 'created_at',
            'group_by_period'       => 'day',
            'aggregate_function'    => 'count',
            'filter_field'          => 'created_at',
            'filter_days'           => '7',
            'group_by_field_format' => 'd-m-Y H:i:s',
            'column_class'          => 'col-md-6',
            'entries_number'        => '10',
            'fields'                => [
                'nama'           => '',
                'no_tiket'       => '',
                'checkin'        => '',
                'status_payment' => '',
                'payment_type'   => '',
            ],
            'translation_key' => 'pendaftar',
        ];

        $settings4['data'] = [];
        if (class_exists($settings4['model'])) {
            $settings4['data'] = $settings4['model']::latest()
                ->take($settings4['entries_number'])
                ->get();
        }

        if (!array_key_exists('fields', $settings4)) {
            $settings4['fields'] = [];
        }

        return view('home', compact('chart1', 'chart2', 'chart3', 'settings4'));
    }
}
