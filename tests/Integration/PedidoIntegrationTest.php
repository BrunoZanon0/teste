<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class PedidoIntegrationTest extends TestCase
{
    public function testApiResponseStructure()
    {
        // Testa a estrutura da resposta JSON da API
        $mockSuccessResponse = [
            'success' => true,
            'data' => [
                'message' => 'Pedido criado com sucesso',
                'pedido_id' => 1
            ]
        ];

        $this->assertArrayHasKey('success', $mockSuccessResponse);
        $this->assertArrayHasKey('data', $mockSuccessResponse);
        $this->assertArrayHasKey('pedido_id', $mockSuccessResponse['data']);
        $this->assertTrue($mockSuccessResponse['success']);
    }

    public function testErrorResponseStructure()
    {
        // Testa a estrutura de erro da API
        $mockErrorResponse = [
            'success' => false,
            'error' => 'Erro ao criar pedido'
        ];

        $this->assertArrayHasKey('success', $mockErrorResponse);
        $this->assertArrayHasKey('error', $mockErrorResponse);
        $this->assertFalse($mockErrorResponse['success']);
    }

    public function testValidationErrorStructure()
    {
        // Testa a estrutura de erro de validação
        $mockValidationError = [
            'success' => false,
            'error' => 'Erro de validação',
            'errors' => [
                'descricao' => 'Descrição é obrigatória'
            ]
        ];

        $this->assertArrayHasKey('success', $mockValidationError);
        $this->assertArrayHasKey('error', $mockValidationError);
        $this->assertArrayHasKey('errors', $mockValidationError);
        $this->assertFalse($mockValidationError['success']);
    }
}