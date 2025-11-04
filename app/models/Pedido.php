<?php

namespace Models;

use PDO;
use PDOException;
use Exception;
use Core\Database;

class Pedido
{
    private PDO $db;
    private string $table = 'pedidos';

    public function __construct(?PDO $dbConnection = null)
    {
        $this->db = $dbConnection ?? (new Database())->getConnection();
    }

    /**
     * Busca todos os pedidos com paginação
     */
    public function findAll(int $limit = 10, int $offset = 0, ?int $userId = null): array
    {
        $whereClause = $userId ? "WHERE p.user_id = :user_id" : "";
        $sql = "SELECT p.*, u.name as user_name 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.user_id = u.id 
                {$whereClause}
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            if ($userId) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erro ao buscar pedidos: ' . $e->getMessage());
            throw new Exception('Erro ao listar pedidos');
        }
    }

    /**
     * Busca pedido por ID
     */
    public function findById(int $id, ?int $userId = null): ?array
    {
        $whereClause = "WHERE p.id = :id";
        if ($userId) {
            $whereClause .= " AND p.user_id = :user_id";
        }
        
        $sql = "SELECT p.*, u.name as user_name 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.user_id = u.id 
                {$whereClause} 
                LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            if ($userId) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            return $pedido ?: null;
        } catch (PDOException $e) {
            error_log('Erro ao buscar pedido: ' . $e->getMessage());
            throw new Exception('Erro ao buscar pedido');
        }
    }

    /**
     * Cria um novo pedido
     */
    public function create(array $data): int
    {
        // Validação básica
        if (empty($data['user_id']) || empty($data['descricao'])) {
            throw new Exception('User ID e descrição são obrigatórios');
        }

        $sql = "INSERT INTO {$this->table} (user_id, descricao , status, total) 
                VALUES (:user_id, :descricao, :status, :total)";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':descricao', trim($data['descricao']), PDO::PARAM_STR);
            $stmt->bindValue(':status', $data['status'] ?? 'ativo', PDO::PARAM_STR);
            $stmt->bindValue(':total', $data['valor'] ?? '0', PDO::PARAM_STR);
            
            $stmt->execute();
            
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Erro ao criar pedido: ' . $e->getMessage());
            throw new Exception('Erro ao criar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza um pedido
     */
    public function update(int $id, array $data, ?int $userId = null): bool
    {
        if (empty($data)) {
            throw new Exception('Dados para atualização são obrigatórios');
        }

        $whereClause = "WHERE id = :id";
        if ($userId) {
            $whereClause .= " AND user_id = :user_id";
        }

        $fields = [];
        $bindings = [':id' => $id];

        $allowedFields = ['descricao', 'status','total'];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $fields[] = "{$field} = :{$field}";
                $bindings[":{$field}"] = $value;
            }
        }

        if (empty($fields)) {
            throw new Exception('Nenhum campo válido para atualização');
        }

        $sql = "UPDATE {$this->table} 
                SET " . implode(', ', $fields) . ", 
                    updated_at = CURRENT_TIMESTAMP 
                {$whereClause}";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($bindings as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            
            if ($userId) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar pedido: ' . $e->getMessage());
            throw new Exception('Erro ao atualizar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Deleta um pedido
     */
    public function delete(int $id, ?int $userId = null): bool
    {
        $whereClause = "WHERE id = :id";
        if ($userId) {
            $whereClause .= " AND user_id = :user_id";
        }
        
        $sql = "DELETE FROM {$this->table} {$whereClause}";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            if ($userId) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro ao deletar pedido: ' . $e->getMessage());
            throw new Exception('Erro ao deletar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Conta o total de pedidos
     */
    public function count(?int $userId = null): int
    {
        $whereClause = $userId ? "WHERE user_id = :user_id" : "";
        $sql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            if ($userId) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log('Erro ao contar pedidos: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca pedidos por status
     */
    public function findByStatus(string $status, ?int $userId = null): array
    {
        $whereClause = "WHERE p.status = :status";
        if ($userId) {
            $whereClause .= " AND p.user_id = :user_id";
        }
        
        $sql = "SELECT p.*, u.name as user_name 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.user_id = u.id 
                {$whereClause}
                ORDER BY p.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            
            if ($userId) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erro ao buscar pedidos por status: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Atualiza apenas o status de um pedido
     */
    public function updateStatus(int $id, string $status, ?int $userId = null): bool
    {
        $whereClause = "WHERE id = :id";
        if ($userId) {
            $whereClause .= " AND user_id = :user_id";
        }
        
        $sql = "UPDATE {$this->table} 
                SET status = :status, updated_at = CURRENT_TIMESTAMP 
                {$whereClause}";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            
            if ($userId) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar status do pedido: ' . $e->getMessage());
            throw new Exception('Erro ao atualizar status do pedido: ' . $e->getMessage());
        }
    }

    /**
     * Busca pedidos de um usuário específico
     */
    public function findByUser(int $userId, int $limit = 10, int $offset = 0): array
    {
        return $this->findAll($limit, $offset, $userId);
    }
}