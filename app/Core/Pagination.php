<?php
declare(strict_types=1);

namespace App\Core;

/** Phân trang dùng chung cho Client và Admin */
final class Pagination
{
    public int $page;
    public int $totalPages;

    public function __construct(public int $total, public int $perPage, int $page)
    {
        $this->totalPages = max(1, (int) ceil($total / max(1, $perPage)));
        $this->page = min(max(1, $page), $this->totalPages);
    }

    public static function fromRequest(int $total, int $perPage): self
    {
        return new self($total, $perPage, (int) ($_GET['page'] ?? 1));
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /** Link giữ nguyên các tham số lọc/tìm kiếm hiện tại */
    public function link(int $page): string
    {
        $q = $_GET;
        $q['page'] = $page;
        return '?' . http_build_query($q);
    }

    /** Danh sách số trang rút gọn: 1 … 4 5 6 … 20 */
    public function window(): array
    {
        $pages = [];
        for ($i = 1; $i <= $this->totalPages; $i++) {
            if ($i === 1 || $i === $this->totalPages || abs($i - $this->page) <= 1) {
                $pages[] = $i;
            }
        }
        return $pages;
    }
}
