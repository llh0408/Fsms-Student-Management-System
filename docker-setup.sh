#!/bin/bash
set -e

echo "🐳 FSMS Docker Setup"
echo "==================="

# Create .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env file"
fi

# Build
echo "Building Docker images..."
docker-compose build

# Start
echo "Starting containers..."
docker-compose up -d

# Wait for MySQL
echo "Waiting for MySQL..."
sleep 10

# Status
echo ""
echo "Setup complete!"
echo ""
echo "Access:"
echo "  Web: http://localhost"
echo "  PhpMyAdmin: http://localhost:8080"
echo ""
echo "Credentials:"
echo "  User: admin"
echo "  Pass: admin123"
echo ""
echo "Commands:"
echo "  docker-compose ps          # Status"
echo "  docker-compose logs -f     # Logs"
echo "  docker-compose down        # Stop"
