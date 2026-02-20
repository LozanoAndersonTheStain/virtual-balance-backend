# Script de prueba de API - Virtual Balance
# Ejecutar: .\test-api.ps1

$API_KEY = "3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe"
$BASE_URL = "http://localhost:8000/api"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "VIRTUAL BALANCE API - TESTS" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# 1. Health Check
Write-Host "1. Health Check:" -ForegroundColor Yellow
curl -X GET "$BASE_URL/health" -H "X-API-Key: $API_KEY"
Write-Host "`n"

# 2. Registrar Usuario
Write-Host "2. Registrar Usuario:" -ForegroundColor Yellow
$registerData = @{
    document = "1234567890"
    name     = "Juan Pérez"
    email    = "juan@example.com"
    phone    = "3001234567"
} | ConvertTo-Json

curl -X POST "$BASE_URL/users/register" `
    -H "Content-Type: application/json" `
    -H "X-API-Key: $API_KEY" `
    -d $registerData
Write-Host "`n"

# 3. Consultar Saldo
Write-Host "3. Consultar Saldo:" -ForegroundColor Yellow
curl -X GET "$BASE_URL/users/1234567890/balance" -H "X-API-Key: $API_KEY"
Write-Host "`n"

# 4. Recargar Billetera
Write-Host "4. Recargar Billetera (crear transacción pendiente):" -ForegroundColor Yellow
$rechargeData = @{
    document = "1234567890"
    phone    = "3001234567"
    amount   = 10000
} | ConvertTo-Json

curl -X POST "$BASE_URL/transactions/recharge" `
    -H "Content-Type: application/json" `
    -H "X-API-Key: $API_KEY" `
    -d $rechargeData
Write-Host "`n"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "NOTA:" -ForegroundColor Yellow
Write-Host "- Copia el 'token' y 'sessionId' de la recarga" -ForegroundColor White
Write-Host "- Confirmar como CLIENTE:" -ForegroundColor White
Write-Host '  curl -X POST http://localhost:8000/api/transactions/confirm \' -ForegroundColor Gray
Write-Host '    -H "Content-Type: application/json" \' -ForegroundColor Gray
Write-Host '    -H "X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe" \' -ForegroundColor Gray
Write-Host '    -d ''{"token": "tok_XXXXXXXX", "sessionId": "sess_XXXXXXXX"}''' -ForegroundColor Gray
Write-Host "`n- Confirmar como WEBHOOK (pasarela externa):" -ForegroundColor White
Write-Host '  curl -X POST http://localhost:8000/api/notifications/payment \' -ForegroundColor Gray
Write-Host '    -H "Content-Type: application/json" \' -ForegroundColor Gray
Write-Host '    -H "X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe" \' -ForegroundColor Gray
Write-Host '    -d ''{"token": "tok_XXXXXXXX", "sessionId": "sess_XXXXXXXX"}''' -ForegroundColor Gray
Write-Host "========================================`n" -ForegroundColor Cyan
