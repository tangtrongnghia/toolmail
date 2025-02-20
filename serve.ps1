# Lấy địa chỉ IP từ ipconfig
$ipconfig = ipconfig
$ipv4Address = $ipconfig | Select-String -Pattern "IPv4 Address" | ForEach-Object {
    $_ -replace ".*:\s*"
}

# Lấy dòng đầu tiên vì có thể có nhiều kết quả
$ipv4Address = $ipv4Address[0]

# Chạy lệnh PHP Artisan Serve
$port = 80
$command = "php artisan serve --port=$port --host=$ipv4Address"
Invoke-Expression $command
