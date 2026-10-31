<?php
declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('client/home', [
            'title'      => 'Trang chủ',
            'featured'   => (new Product())->featured(6),
            'categories' => (new Category())->active(),
        ]);
    }

    public function about(): void
    {
        $this->view('client/about', [
            'title'         => 'Về quán',
            'productCount'  => (new Product())->stats()['active'],
            'categoryCount' => count((new Category())->active()),
        ]);
    }
}
