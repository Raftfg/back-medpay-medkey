@echo off
echo Test de connexion avec curl
echo.
echo Utilisez un email et mot de passe valides
echo.
curl -X POST http://localhost:8000/api/v1/login ^
  -H "Content-Type: application/json" ^
  -H "Origin: http://localhost:8080" ^
  -d "{\"email\":\"admin@medkey.com\",\"password\":\"votre-mot-de-passe\"}" ^
  -v

pause
