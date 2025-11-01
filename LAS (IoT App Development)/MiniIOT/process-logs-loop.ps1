Write-Host "Starting IoT Log Processor..." -ForegroundColor Green
Set-Location "d:\Work\MiniIOT"

while ($true) {
    $timestamp = Get-Date -Format "HH:mm:ss"
    Write-Host "Processing logs at $timestamp..." -ForegroundColor Yellow
    
    try {
        php artisan iot:process-logs
        Write-Host "Log processing completed" -ForegroundColor Green
    }
    catch {
        Write-Host "Error processing logs" -ForegroundColor Red
    }
    
    Start-Sleep -Seconds 1
}
