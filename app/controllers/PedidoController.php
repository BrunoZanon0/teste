<?php 

namespace Controllers;

class PedidoController extends Controller{
     public function index()
    {
        echo json_encode(['msg' => 'Listando pedidos']);
    }

    public function show($id)
    {
        echo json_encode(['msg' => "Detalhes do pedido $id"]);
    }

    public function store()
    {
        echo json_encode(['msg' => 'Pedido criado']);
    }

    public function update($id)
    {
        echo json_encode(['msg' => "Pedido $id atualizado"]);
    }
}