<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\UsersDataTable;
use App\DataTables\CustomersDataTable;
use App\DataTables\OrdersDataTable;
use DB;
use App\Charts\CustomerChart;

class DashboardController extends Controller
{
    private $bgcolor;
    public function __construct()
    {

        $this->bgcolor = collect([
            '#7158e2',
            '#3ae374',
            '#ff3838',
            "#FF851B",
            "#7FDBFF",
            "#B10DC9",
            "#FFDC00",
            "#001f3f",
            "#39CCCC",
            "#01FF70",
            "#85144b",
            "#F012BE",
            "#3D9970",
            "#111111",
            "#AAAAAA",
        ]);
    }

    public function index()
    {
        // SELECT count(addressline), addressline from customer group by addressline;
        $customers = DB::table('customer')
            ->whereNotNull('addressline')
            // ->select(DB::raw('count(addressline) as total'), 'addressline')
            // ->pluck('addressline')->all();
            ->groupBy('addressline')
            ->orderBy('total', 'DESC')
            ->pluck(DB::raw('count(addressline) as total'), 'addressline')
            ->all();
        // dd(array_values($customers));

        $customerChart = new CustomerChart;
        $dataset = $customerChart->labels(array_keys($customers));
        // dd($dataset);
        $dataset = $customerChart->dataset(
            'Customer Demographics',
            'bar',
            array_values($customers)
        );
        // dd($dataset);
        $dataset = $dataset->backgroundColor($this->bgcolor);
        $customerChart->options([
            'responsive' => true,
            'legend' => ['display' => true],
            'tooltips' => ['enabled' => true],
            'aspectRatio' => 1,
            'scales' => [
                'yAxes' => [
                    [
                        'display' => true,
                    ],
                ],
                'xAxes' => [
                    [
                        'gridLines' => ['display' => false],
                        'display' => true,
                    ],
                ],
            ],
        ]);

        return view('dashboard.index', compact('customerChart'));
    }
    public function getUsers(UsersDataTable $dataTable)
    {
        return $dataTable->render('dashboard.users');
    }

    public function getCustomers(CustomersDataTable $dataTable)
    {
        return $dataTable->render('dashboard.customers');
    }

    public function getOrders(OrdersDataTable $dataTable)
    {
        return $dataTable->render('dashboard.orders');
    }
}
