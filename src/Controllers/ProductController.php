<?php

class ProductController
{
	public function index(): void
	{
		$products = db()->select('products')->all();

		response()->json([
			"data" => $products,
			"count" => count($products)
		]);
	}

	public function show(int $id): void
	{
		$product = db()->select('products')->where('id', $id)->first();
		response()->json($product);
	}

	public function enable(int $id): void
	{
		db()
			->update("products")
			->params(["isActive" => 1])
			->where("id", $id)
			->execute();

		response()->json([
			"message" => "Producto Activado Correctamente"
		]);
	}
}

ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}
if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html"); //Validamos el acceso solo a los usuarios logueados al sistema.
} else {
	//Validamos el acceso solo al usuario logueado y autorizado.
	if ($_SESSION['almacen'] == 1) {
		require_once "../modelos/Articulo.php";

		$articulo = new Articulo();

		$idarticulo = isset($_POST["idarticulo"]) ? limpiarCadena($_POST["idarticulo"]) : "";
		$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : "";
		$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
		$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
		$stock = isset($_POST["stock"]) ? limpiarCadena($_POST["stock"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
		$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':

				if (!file_exists($_FILES['imagen']['tmp_name']) || !is_uploaded_file($_FILES['imagen']['tmp_name'])) {
					$imagen = $_POST["imagenactual"];
				} else {
					$ext = explode(".", $_FILES["imagen"]["name"]);
					if ($_FILES['imagen']['type'] == "image/jpg" || $_FILES['imagen']['type'] == "image/jpeg" || $_FILES['imagen']['type'] == "image/png") {
						$imagen = round(microtime(true)) . '.' . end($ext);
						move_uploaded_file($_FILES["imagen"]["tmp_name"], "../files/articulos/" . $imagen);
					}
				}
				if (empty($idarticulo)) {
					$rspta = $articulo->insertar($idcategoria, $codigo, $nombre, $stock, $descripcion, $imagen);
					echo $rspta ? "Artículo registrado" : "Artículo no se pudo registrar";
				} else {
					$rspta = $articulo->editar($idarticulo, $idcategoria, $codigo, $nombre, $stock, $descripcion, $imagen);
					echo $rspta ? "Artículo actualizado" : "Artículo no se pudo actualizar";
				}
				break;

			case 'desactivar':
				$rspta = $articulo->desactivar($idarticulo);
				echo $rspta ? "Artículo Desactivado" : "Artículo no se puede desactivar";
				break;

			case 'activar':
				$rspta = $articulo->activar($idarticulo);
				echo $rspta ? "Artículo activado" : "Artículo no se puede activar";
				break;

			case "selectCategoria":
				require_once "../modelos/Categoria.php";
				$categoria = new Categoria();

				$rspta = $categoria->select();

				while ($reg = $rspta->fetch_object()) {
					echo '<option value=' . $reg->idcategoria . '>' . $reg->nombre . '</option>';
				}
				break;
		}
		//Fin de las validaciones de acceso
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
?>