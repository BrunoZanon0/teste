    echo "🔄 Atualizando dependências com composer update..."
    composer install
    composer update

    echo "🔄 Iniciando o docker"

    docker compose up -d --build

    echo "🚀 Docker iniciado com sucesso"

    docker exec -it php_api php zanon  migrate

    echo "🗄️ Migrate Executada com sucesso"