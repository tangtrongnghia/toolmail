# Lấy phiên bản Windows
$osVersion = (Get-CimInstance Win32_OperatingSystem).Version

# Hàm lấy địa chỉ IPv4
function Get-IPv4Address {
    param ($interfaceAlias)
    return (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias $interfaceAlias | Where-Object { $_.AddressState -eq "Preferred" }).IPAddress
}

# Kiểm tra phiên bản Windows
if ($osVersion -ge "10.0") {
    # Windows 10 hoặc cao hơn
    $ipv4Address = Get-IPv4Address -interfaceAlias "Wi-Fi"
    if (-not $ipv4Address) {
        $ipv4Address = Get-IPv4Address -interfaceAlias "Ethernet"
    }
} else {
    # Windows phiên bản thấp hơn
    $ipconfig = ipconfig
    $ipv4Address = $ipconfig | Select-String -Pattern "IPv4 Address. . . . . . . . . . . :" | ForEach-Object {
        $_ -replace ".*:\s*"
    }
    $ipv4Address = $ipv4Address[0]
}

# Kiểm tra xem đã lấy được địa chỉ IP hay chưa
if (-not $ipv4Address) {
    Write-Output "Không tìm thấy địa chỉ IP hợp lệ."
    exit 1
}

# Chạy lệnh PHP Artisan Serve
$port = 80
$command = "php artisan serve --port=$port --host=$ipv4Address"
Invoke-Expression $command
