<?php
// Nếu Apache chưa bật rewrite ở thư mục gốc: chuyển sang thư mục public/
header('Location: public/');
exit;
