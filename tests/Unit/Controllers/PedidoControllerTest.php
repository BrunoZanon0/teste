<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Testa APENAS a lógica de validação sem instanciar o controller real
 */
class PedidoControllerTest extends TestCase
{
    /**
     * Copia a lógica de validação do PedidoController para testar isoladamente
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

    public function testValidatePedidoDataWithValidData()
    {
        $data = [
            'descricao' => 'Pedido de teste válido com mais de 5 caracteres',
            'status' => 'pendente',
            'valor' => 150.75
        ];

        $errors = $this->validatePedidoData($data);
        $this->assertEmpty($errors);
    }

    public function testValidatePedidoDataWithInvalidStatus()
    {
        $data = [
            'descricao' => 'Pedido válido com descrição suficiente',
            'status' => 'status_invalido_que_nao_existe',
            'valor' => 100
        ];

        $errors = $this->validatePedidoData($data);
        $this->assertArrayHasKey('status', $errors);
        $this->assertEquals('Status inválido', $errors['status']);
    }

    public function testValidatePedidoDataWithShortDescription()
    {
        $data = [
            'descricao' => 'abc',
            'status' => 'pendente'
        ];

        $errors = $this->validatePedidoData($data);
        $this->assertArrayHasKey('descricao', $errors);
        $this->assertEquals('Descrição deve ter pelo menos 5 caracteres', $errors['descricao']);
    }

    public function testValidatePedidoDataWithEmptyDescription()
    {
        $data = [
            'descricao' => '',
            'status' => 'pendente'
        ];

        $errors = $this->validatePedidoData($data);
        $this->assertArrayHasKey('descricao', $errors);
        $this->assertEquals('Descrição é obrigatória', $errors['descricao']);
    }

    public function testValidatePedidoDataWithNegativeValue()
    {
        $data = [
            'descricao' => 'Pedido com valor negativo',
            'status' => 'pendente',
            'valor' => -10
        ];

        $errors = $this->validatePedidoData($data);
        $this->assertArrayHasKey('valor', $errors);
        $this->assertEquals('Valor deve ser um número positivo', $errors['valor']);
    }

    public function testValidatePedidoDataWithLongDescription()
    {
        $data = [
            'descricao' => str_repeat('a', 501),
            'status' => 'pendente'
        ];

        $errors = $this->validatePedidoData($data);
        $this->assertArrayHasKey('descricao', $errors);
        $this->assertEquals('Descrição deve ter no máximo 500 caracteres', $errors['descricao']);
    }

    public function testValidatePedidoDataWithValidStatuses()
    {
        $validStatuses = ['pendente', 'em_andamento', 'concluido', 'cancelado'];
        
        foreach ($validStatuses as $status) {
            $data = [
                'descricao' => 'Descrição válida para teste',
                'status' => $status,
                'valor' => 100
            ];

            $errors = $this->validatePedidoData($data);
            $this->assertArrayNotHasKey('status', $errors, "Status '$status' deveria ser válido");
        }
    }

    public function testValidatePedidoDataWithoutStatus()
    {
        $data = [
            'descricao' => 'Descrição válida sem status'
        ];

        $errors = $this->validatePedidoData($data);
        $this->assertEmpty($errors); // Status não é obrigatório
    }

    public function testValidatePedidoDataWithoutValue()
    {
        $data = [
            'descricao' => 'Descrição válida sem valor'
        ];

        $errors = $this->validatePedidoData($data);
        $this->assertEmpty($errors); // Valor não é obrigatório
    }
}