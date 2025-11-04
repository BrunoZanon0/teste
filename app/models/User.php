<?php

namespace Models;

use PDO;
use PDOException;
use Exception;
use Core\Database;

class User
{
    private $db;
    private $table = 'users';

    public function __construct($dbConnection = null)
    {
        if ($dbConnection === null) {
            $database = new Database();
            $this->db = $database->getConnection();

        } else {
            $this->db = $dbConnection;
        }
    }

    public function emailExists($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log('Erro ao verificar email: ' . $e->getMessage());
            return false;
        }
    }

    public function findByEmail($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, password FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erro ao buscar usuário: ' . $e->getMessage());
            return false;
        }
    }

    public function create($data)
    {
        if($this->emailExists($data['email'])){
            throw new Exception("Email já está em uso");
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$data['name'], $data['email'], $data['password']]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception('Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    public function findById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erro ao buscar usuário por ID: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data)
    {
        try {
            $fields = [];
            $values = [];
            
            foreach ($data as $field => $value) {
                $fields[] = "{$field} = ?";
                $values[] = $value;
            }
            
            $values[] = $id;
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log('Erro ao atualizar usuário: ' . $e->getMessage());
            return false;
        }
    }
}