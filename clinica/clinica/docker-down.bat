@echo off
setlocal

cd /d "%~dp0"

if not exist ".env.docker" (
    echo .env.docker was not found. Using .env.docker.example for shutdown...
    docker compose --env-file .env.docker.example down
    endlocal
    exit /b %errorlevel%
)

echo Stopping Docker environment...
docker compose --env-file .env.docker down

endlocal
