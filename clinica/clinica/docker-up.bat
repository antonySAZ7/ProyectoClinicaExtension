@echo off
setlocal

cd /d "%~dp0"

if not exist ".env.docker" (
    echo Creating .env.docker from .env.docker.example...
    copy /Y ".env.docker.example" ".env.docker" >nul
)

echo Starting Docker environment...
docker compose --env-file .env.docker up --build

endlocal
