@echo off
setlocal

cd /d "%~dp0"

if not exist ".env.docker" (
    echo .env.docker was not found. Using .env.docker.example for logs...
    docker compose --env-file .env.docker.example logs -f
    endlocal
    exit /b %errorlevel%
)

echo Showing Docker logs...
docker compose --env-file .env.docker logs -f

endlocal
