#!/bin/bash
# Script de prueba de API - Virtual Balance
# Ejecutar: chmod +x test-api.sh && ./test-api.sh

API_KEY="3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe"
BASE_URL="http://localhost:8000/api"

echo -e "\n========================================"
echo "VIRTUAL BALANCE API - TESTS"
echo -e "========================================\n"

# 1. Health Check
echo "1. Health Check:"
curl -X GET "$BASE_URL/health" -H "X-API-Key: $API_KEY"
echo -e "\n"

# 2. Registrar Usuario
echo "2. Registrar Usuario:"
curl -X POST "$BASE_URL/users/register" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: $API_KEY" \
  -d '{
    "document": "1234567890",
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "phone": "3001234567"
  }'
echo -e "\n"

# 3. Consultar Saldo
echo "3. Consultar Saldo:"
curl -X GET "$BASE_URL/users/1234567890/balance" -H "X-API-Key: $API_KEY"
echo -e "\n"

# 4. Recargar Billetera
echo "4. Recargar Billetera (crear transacción pendiente):"
curl -X POST "$BASE_URL/transactions/recharge" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: $API_KEY" \
  -d '{
    "document": "1234567890",
    "phone": "3001234567",
    "amount": 10000
  }'
echo -e "\n"

echo "========================================"
echo "NOTA:"
echo "- Copia el 'token' de la recarga"
echo "- Usa ese token para confirmar el pago:"
echo ""
echo '  curl -X POST http://localhost:8000/api/transactions/confirm \'
echo '    -H "Content-Type: application/json" \'
echo '    -H "X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe" \'
echo '    -d '"'"'{"token": "tok_XXXXXXXX", "sessionId": "sess_XXXXXXXX"}'"'"''
echo -e "========================================\n"
