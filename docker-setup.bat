@echo off
REM Windows batch script for Docker setup

setlocal enabledelayedexpansion

REM Colors (Windows doesn't support ANSI colors easily, so using simple text)
echo.
echo ========================================
echo FSMS Docker Setup Script
echo ========================================
echo.

REM Check if .env file exists
if not exist .env (
    echo Creating .env file from .env.example...
    copy .env.example .env
    echo .env file created successfully!
    echo Please update the .env file with your configuration.
) else (
    echo .env file already exists.
)

REM Build images
echo.
echo Building Docker images...
docker-compose build

if errorlevel 1 (
    echo Failed to build images!
    exit /b 1
)

echo Images built successfully!

REM Start services
echo.
echo Starting services...
docker-compose up -d

if errorlevel 1 (
    echo Failed to start services!
    exit /b 1
)

echo Services started successfully!

REM Wait for MySQL to be ready
echo.
echo Waiting for MySQL to be ready...
timeout /t 10 /nobreak

REM Check services status
echo.
echo Checking services status...
docker-compose ps

echo.
echo ========================================
echo Docker setup completed successfully!
echo ========================================
echo.
echo Access your application at:
echo   Web:       http://localhost
echo   PhpMyAdmin: http://localhost:8080
echo.
echo Default credentials:
echo   Username: admin
echo   Password: admin123
echo.
echo Useful commands:
echo   docker-compose logs -f          - View logs
echo   docker-compose exec php bash    - Access PHP container
echo   docker-compose exec mysql bash  - Access MySQL container
echo   docker-compose down             - Stop all services
echo.

endlocal
