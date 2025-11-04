<?php

namespace Controllers;

use Enum\JsonResponse;
use Enum\HttpStatusCode;
use Interfaces\PedidoControllerInterface;
use Models\Pedido;
use Exception;

class PedidoController extends Controller implements PedidoControllerInterface
{
    private Pedido $pedidoModel;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
    }

    /**
     * Lista todos os pedidos (com paginação)
     * GET /pedidos
     */
    public function getAll(): void
    {
        try {
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $limit = max(1, min(50, (int) ($_GET['limit'] ?? 10)));
            $offset = ($page - 1) * $limit;

            // Se tiver usuário autenticado, filtra por user_id
            $userId = $this->getAuthenticatedUserId();
            
            $pedidos = $this->pedidoModel->findAll($limit, $offset, $userId);
            $total = $this->pedidoModel->count($userId);
            $totalPages = ceil($total / $limit);

            JsonResponse::success([
                'pedidos' => $pedidos,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ]);

        } catch (Exception $e) {
            error_log('Erro ao listar pedidos: ' . $e->getMessage());
            JsonResponse::error('Erro ao listar pedidos', HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostra detalhes de um pedido específico
     * GET /pedidos/{id}
     */
    public function getOneOrder(int $id): void
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $pedido = $this->pedidoModel->findById($id, $userId);

            if (!$pedido) {
                JsonResponse::error('Pedido não encontrado', HttpStatusCode::NOT_FOUND);
                return;
            }

            JsonResponse::success([
                'pedido' => $pedido
            ]);

        } catch (Exception $e) {
            error_log('Erro ao buscar pedido: ' . $e->getMessage());
            JsonResponse::error('Erro ao buscar pedido', HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cria um novo pedido
     * POST /pedidos
     */
    public function createNewOrder(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                JsonResponse::error('Método não permitido', HttpStatusCode::METHOD_NOT_ALLOWED);
                return;
            }

            $jsonInput = file_get_contents('php://input');
            $data = json_decode($jsonInput, true);
            
            if (!$data) {
                JsonResponse::error('Dados JSON inválidos ou vazios', HttpStatusCode::BAD_REQUEST);
                return;
            }

            // Validações básicas
            $errors = $this->validatePedidoData($data);
            if (!empty($errors)) {
                JsonResponse::validationError($errors);
                return;
            }

            // Obtém o ID do usuário autenticado
            $userId = $this->getAuthenticatedUserId();
            $data['user_id'] = $userId;

            $pedidoId = $this->pedidoModel->create($data);

            JsonResponse::success([
                'message' => 'Pedido criado com sucesso',
                'pedido_id' => $pedidoId
            ], HttpStatusCode::CREATED);

        } catch (Exception $e) {
            error_log('Erro ao criar pedido: ' . $e->getMessage());
            JsonResponse::error($e->getMessage(), HttpStatusCode::BAD_REQUEST);
        }
    }

    /**
     * Atualiza um pedido
     * PUT /pedidos/{id}
     */
    public function updateOrder(int $id): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                JsonResponse::error('Método não permitido', HttpStatusCode::METHOD_NOT_ALLOWED);
                return;
            }

            $jsonInput = file_get_contents('php://input');
            $data = json_decode($jsonInput, true);
            
            if (!$data) {
                JsonResponse::error('Dados JSON inválidos ou vazios', HttpStatusCode::BAD_REQUEST);
                return;
            }

            $userId = $this->getAuthenticatedUserId();
            
            // Verifica se o pedido existe e pertence ao usuário
            $pedido = $this->pedidoModel->findById($id, $userId);
            if (!$pedido) {
                JsonResponse::error('Pedido não encontrado', HttpStatusCode::NOT_FOUND);
                return;
            }

            $updated = $this->pedidoModel->update($id, $data, $userId);

            if ($updated) {
                JsonResponse::success([
                    'message' => 'Pedido atualizado com sucesso',
                    'pedido_id' => $id
                ]);
            } else {
                JsonResponse::error('Erro ao atualizar pedido', HttpStatusCode::INTERNAL_SERVER_ERROR);
            }

        } catch (Exception $e) {
            error_log('Erro ao atualizar pedido: ' . $e->getMessage());
            JsonResponse::error($e->getMessage(), HttpStatusCode::BAD_REQUEST);
        }
    }

    /**
     * Valida dados do pedido
     */
    private function validatePedidoData(array $data): array
    {
        $errors = [];

        if (empty($data['descricao'])) {
            $errors['descricao'] = 'Descrição é obrigatória';
        } elseif (strlen($data['descricao']) < 5) {
            $errors['descricao'] = 'Descrição deve ter pelo menos 5 caracteres';
        } elseif (strlen($data['descricao']) > 500) {
            $errors['descricao'] = 'Descrição deve ter no máximo 500 caracteres';
        }

        if (isset($data['status']) && !in_array($data['status'], ['pendente', 'em_andamento', 'concluido', 'cancelado'])) {
            $errors['status'] = 'Status inválido';
        }

        if (isset($data['valor']) && (!is_numeric($data['valor']) || $data['valor'] < 0)) {
            $errors['valor'] = 'Valor deve ser um número positivo';
        }

        return $errors;
    }

    /**
     * Obtém o ID do usuário autenticado
     */
    private function getAuthenticatedUserId(): int
    {
        // Implementação depende do seu middleware de autenticação
        // Exemplo: pegar do JWT decodificado
        if (defined('CURRENT_USER')) {
            return CURRENT_USER['user_id'];
        }
        
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']['user_id'];
        }

        throw new Exception('Usuário não autenticado');
    }

    /**
     * Deleta um pedido
     * DELETE /pedidos/{id}
     */
    public function delete(int $id): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                JsonResponse::error('Método não permitido', HttpStatusCode::METHOD_NOT_ALLOWED);
                return;
            }

            $userId = $this->getAuthenticatedUserId();
            
            // Verifica se o pedido existe e pertence ao usuário
            $pedido = $this->pedidoModel->findById($id, $userId);
            if (!$pedido) {
                JsonResponse::error('Pedido não encontrado', HttpStatusCode::NOT_FOUND);
                return;
            }

            $deleted = $this->pedidoModel->delete($id, $userId);

            if ($deleted) {
                JsonResponse::success([
                    'message' => 'Pedido deletado com sucesso',
                    'pedido_id' => $id
                ]);
            } else {
                JsonResponse::error('Erro ao deletar pedido', HttpStatusCode::INTERNAL_SERVER_ERROR);
            }

        } catch (Exception $e) {
            error_log('Erro ao deletar pedido: ' . $e->getMessage());
            JsonResponse::error($e->getMessage(), HttpStatusCode::BAD_REQUEST);
        }
    }
}