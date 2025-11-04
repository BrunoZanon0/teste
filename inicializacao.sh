    echo "🔄 Atualizando dependências com composer update..."
    composer install
    composer update

    echo "🔄 Iniciando o docker"

    docker compose up -d --build

    echo "🔄 Docker iniciado com sucesso"
