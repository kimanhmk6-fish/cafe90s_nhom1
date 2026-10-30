<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class Size extends BaseModel
{
    protected string $table = 'sizes';
    protected array $fillable = ['name', 'description', 'sort_order'];

    public function ordered(): array
    {
        return $this->all('sort_order');
    }
}
