<?php
declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Pagination;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    private const PER_PAGE = 8;

    public function menu(): void
    {
        $filters = [
            'q'           => $this->input('q'),
            'category'    => $this->input('category'),
            'sort'        => $this->input('sort'),
            'only_public' => true,
        ];
        $products = new Product();
        [, $total] = $products->search($filters, 1, 0);
        $pager = Pagination::fromRequest($total, self::PER_PAGE);
        [$items] = $products->search($filters, self::PER_PAGE, $pager->offset());

        $this->view('client/menu', [
            'title'      => 'Thực đơn',
            'items'      => $items,
            'total'      => $total,
            'pager'      => $pager,
            'filters'    => $filters,
            'categories' => (new Category())->active(),
        ]);
    }
}
