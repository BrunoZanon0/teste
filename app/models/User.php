<?php

namespace Models;

use PDO;
use PDOException;
use Exception;
use Core\Database;

class User
{
    private PDO $db;
    private string $table = 'users';

    public function __construct(?PDO $dbConnection = null)
    {
        $this->db = $dbConnection ?? (new Database())->getConnection();
    }

    /**
     * Verifica se um email já existe no banco
     */
    public function emailExists(string $email): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE email = :email LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            error_log('Erro ao verificar email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca usuário pelo email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, name, email, password, created_at, updated_at 
                FROM {$this->table} 
                WHERE email = :email 
                LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e) {
            error_log('Erro ao buscar usuário por email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca usuário pelo ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT id, name, email, created_at, updated_at 
                FROM {$this->table} 
                WHERE id = :id 
                LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e) {
            error_log('Erro ao buscar usuário por ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cria um novo usuário
     */
    public function create(array $data): int
    {
        // Validação básica
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new Exception('Nome, email e senha são obrigatórios');
        }

        if ($this->emailExists($data['email'])) {
            throw new Exception('Email já está em uso');
        }

        $sql = "INSERT INTO {$this->table} (name, email, password) 
                VALUES (:name, :email, :password)";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindValue(':name', trim($data['name']), PDO::PARAM_STR);
            $stmt->bindValue(':email', trim($data['email']), PDO::PARAM_STR);
            $stmt->bindValue(':password', $data['password'], PDO::PARAM_STR);
            
            $stmt->execute();
            
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception('Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza um usuário existente
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            throw new Exception('Dados para atualização são obrigatórios');
        }

        // Remove campos que não devem ser atualizados
        unset($data['id'], $data['created_at']);

        $fields = [];
        $bindings = [':id' => $id];

        foreach ($data as $field => $value) {
            if (in_array($field, ['name', 'email', 'password'])) {
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
                WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($bindings as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar usuário: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deleta um usuário
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro ao deletar usuário: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista todos os usuários (com paginação opcional)
     */
    public function findAll(int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT id, name, email, created_at, updated_at 
                FROM {$this->table} 
                ORDER BY created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            
            if ($limit > 0) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erro ao listar usuários: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o total de usuários
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log('Erro ao contar usuários: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Atualiza a senha do usuário
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $sql = "UPDATE {$this->table} 
                SET password = :password, updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':password', $newPassword, PDO::PARAM_STR);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar senha: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica credenciais de login
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove a senha do resultado
            unset($user['password']);
            return $user;
        }
        
        return null;
    }
}