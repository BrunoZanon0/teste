<?php 

namespace Interfaces;

interface PedidoControllerInterface
{
    public function getAll(): void;
    public function getOneOrder(int $id): void;
    public function createNewOrder(): void;
    public function updateOrder(int $id): void;
}