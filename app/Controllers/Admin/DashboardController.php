<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(): void
    {
        $orders = new Order();
        $this->view('admin/dashboard', [
            'title'        => 'Dashboard',
            'productCount' => (new Product())->stats()['active'],
            'customers'    => (new User())->countByRole('customer'),
            'orderCount'   => $orders->count(),
            'revenue'      => $orders->revenueTotal(),
            'statusCounts' => $orders->statusCounts(),
            'days'         => $orders->revenueLast7Days(),
            'latest'       => $orders->latest(5),
        ], 'admin');
    }
}
